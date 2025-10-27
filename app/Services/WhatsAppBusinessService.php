<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\WhatsAppLog;

/**
 * Service d'envoi de messages via WhatsApp Business API
 *
 * Utilise l'API officielle Meta (Facebook) pour envoyer des messages WhatsApp
 * Compatible avec WhatsApp Business Platform et Cloud API
 *
 * @link https://developers.facebook.com/docs/whatsapp/cloud-api
 */
class WhatsAppBusinessService
{
    /**
     * URL de base de l'API WhatsApp
     */
    private string $apiUrl;

    /**
     * Token d'accès pour l'API
     */
    private string $apiToken;

    /**
     * ID du numéro de téléphone WhatsApp Business
     */
    private string $phoneNumberId;

    /**
     * Activer/Désactiver l'envoi réel
     */
    private bool $enabled;

    /**
     * Mode démo (log uniquement, pas d'envoi)
     */
    private bool $demoMode;

    /**
     * Timeout pour les requêtes HTTP
     */
    private int $timeout;

    /**
     * Initialisation du service avec les configurations
     */
    public function __construct()
    {
        $this->apiUrl = Config::get('whatsapp.api_url');
        $this->apiToken = Config::get('whatsapp.api_token');
        $this->phoneNumberId = Config::get('whatsapp.phone_number_id');
        $this->enabled = Config::get('whatsapp.enabled', true);
        $this->demoMode = Config::get('whatsapp.demo_mode', false);
        $this->timeout = Config::get('whatsapp.timeout', 30);
    }

    /**
     * Envoyer un message texte simple
     *
     * @param string $to Numéro de téléphone du destinataire (format international sans +)
     * @param string $message Contenu du message (max 4096 caractères)
     * @param array $context Contexte additionnel pour le logging
     * @return array Résultat de l'envoi
     */
    public function sendTextMessage(string $to, string $message, array $context = []): array
    {
        try {
            // Vérifier si le service est activé
            if (!$this->enabled) {
                return $this->logAndReturn(false, 'WhatsApp service disabled', $to, $message, $context);
            }

            // Vérifier la configuration
            if (empty($this->apiToken) || empty($this->phoneNumberId)) {
                return $this->logAndReturn(false, 'WhatsApp API not configured', $to, $message, $context);
            }

            // Formater le numéro de téléphone
            $to = $this->formatPhoneNumber($to);

            // Valider le numéro
            if (!$this->isValidPhoneNumber($to)) {
                return $this->logAndReturn(false, 'Invalid phone number', $to, $message, $context);
            }

            // Mode démo : simuler l'envoi
            if ($this->demoMode) {
                return $this->logAndReturn(true, 'Demo mode - Message not sent', $to, $message, $context);
            }

            // Construire le payload pour l'API WhatsApp
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'preview_url' => true,
                    'body' => $this->sanitizeMessage($message)
                ]
            ];

            // Envoyer la requête à l'API WhatsApp
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            // Vérifier la réponse
            if ($response->successful()) {
                $data = $response->json();
                return $this->logAndReturn(
                    true,
                    'Message sent successfully',
                    $to,
                    $message,
                    array_merge($context, [
                        'message_id' => $data['messages'][0]['id'] ?? null,
                        'api_response' => $data
                    ])
                );
            } else {
                return $this->logAndReturn(
                    false,
                    'API request failed',
                    $to,
                    $message,
                    array_merge($context, [
                        'status_code' => $response->status(),
                        'error_response' => $response->json()
                    ])
                );
            }

        } catch (\Exception $e) {
            return $this->logAndReturn(
                false,
                'Exception occurred',
                $to,
                $message,
                array_merge($context, [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ])
            );
        }
    }

    /**
     * Envoyer un message template approuvé
     *
     * @param string $to Numéro de téléphone du destinataire
     * @param string $templateName Nom du template approuvé
     * @param array $parameters Paramètres du template
     * @param string $language Code langue (fr, en, etc.)
     * @param array $context Contexte additionnel
     * @return array Résultat de l'envoi
     */
    public function sendTemplateMessage(
        string $to,
        string $templateName,
        array $parameters = [],
        string $language = 'fr',
        array $context = []
    ): array {
        try {
            // Vérifications de base
            if (!$this->enabled) {
                return $this->logAndReturn(false, 'WhatsApp service disabled', $to, "Template: {$templateName}", $context);
            }

            if (empty($this->apiToken) || empty($this->phoneNumberId)) {
                return $this->logAndReturn(false, 'WhatsApp API not configured', $to, "Template: {$templateName}", $context);
            }

            // Formater le numéro
            $to = $this->formatPhoneNumber($to);

            if (!$this->isValidPhoneNumber($to)) {
                return $this->logAndReturn(false, 'Invalid phone number', $to, "Template: {$templateName}", $context);
            }

            // Mode démo
            if ($this->demoMode) {
                return $this->logAndReturn(true, 'Demo mode - Template not sent', $to, "Template: {$templateName}", $context);
            }

            // Construire les composants du template
            $components = [];
            if (!empty($parameters)) {
                $templateParameters = [];
                foreach ($parameters as $param) {
                    $templateParameters[] = [
                        'type' => 'text',
                        'text' => (string) $param
                    ];
                }

                $components[] = [
                    'type' => 'body',
                    'parameters' => $templateParameters
                ];
            }

            // Payload pour l'API
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => $language
                    ],
                    'components' => $components
                ]
            ];

            // Envoyer la requête
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiToken)
                ->post("{$this->apiUrl}/{$this->phoneNumberId}/messages", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $this->logAndReturn(
                    true,
                    'Template message sent successfully',
                    $to,
                    "Template: {$templateName}",
                    array_merge($context, [
                        'template' => $templateName,
                        'message_id' => $data['messages'][0]['id'] ?? null,
                        'api_response' => $data
                    ])
                );
            } else {
                return $this->logAndReturn(
                    false,
                    'Template API request failed',
                    $to,
                    "Template: {$templateName}",
                    array_merge($context, [
                        'template' => $templateName,
                        'status_code' => $response->status(),
                        'error_response' => $response->json()
                    ])
                );
            }

        } catch (\Exception $e) {
            return $this->logAndReturn(
                false,
                'Template exception occurred',
                $to,
                "Template: {$templateName}",
                array_merge($context, [
                    'template' => $templateName,
                    'exception' => $e->getMessage()
                ])
            );
        }
    }

    /**
     * Ouvrir un chat WhatsApp avec un message pré-rempli (URL)
     * Utile pour générer des liens cliquables
     *
     * @param string $to Numéro de téléphone
     * @param string $message Message pré-rempli
     * @return string URL WhatsApp
     */
    public function generateWhatsAppUrl(string $to, string $message): string
    {
        $to = $this->formatPhoneNumber($to);
        $encodedMessage = urlencode($message);
        return "https://wa.me/{$to}?text={$encodedMessage}";
    }

    /**
     * Formater un numéro de téléphone au format international
     *
     * @param string $phone Numéro brut
     * @return string Numéro formaté
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Retirer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Si le numéro commence par 0, remplacer par le code pays par défaut
        if (substr($phone, 0, 1) === '0') {
            $countryCode = Config::get('whatsapp.default_country_code', '225');
            $phone = $countryCode . substr($phone, 1);
        }

        // Si le numéro ne commence pas par le code pays, l'ajouter
        $countryCode = Config::get('whatsapp.default_country_code', '225');
        if (substr($phone, 0, strlen($countryCode)) !== $countryCode) {
            // Seulement si ce n'est pas déjà un numéro international
            if (strlen($phone) < 10) {
                $phone = $countryCode . $phone;
            }
        }

        return $phone;
    }

    /**
     * Valider un numéro de téléphone
     *
     * @param string $phone Numéro à valider
     * @return bool Valide ou non
     */
    private function isValidPhoneNumber(string $phone): bool
    {
        // Un numéro WhatsApp valide doit avoir entre 10 et 15 chiffres
        return preg_match('/^[0-9]{10,15}$/', $phone);
    }

    /**
     * Nettoyer et valider le contenu d'un message
     *
     * @param string $message Message brut
     * @return string Message nettoyé
     */
    private function sanitizeMessage(string $message): string
    {
        // Décoder les entités HTML
        $message = html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Limiter à 4096 caractères (limite WhatsApp)
        if (strlen($message) > 4096) {
            $message = substr($message, 0, 4093) . '...';
        }

        return trim($message);
    }

    /**
     * Logger et retourner le résultat
     *
     * @param bool $success Succès ou échec
     * @param string $status Message de statut
     * @param string $to Destinataire
     * @param string $message Message envoyé
     * @param array $context Contexte additionnel
     * @return array Résultat formaté
     */
    private function logAndReturn(
        bool $success,
        string $status,
        string $to,
        string $message,
        array $context = []
    ): array {
        $result = [
            'success' => $success,
            'status' => $status,
            'to' => $to,
            'timestamp' => now()->toIso8601String(),
            'context' => $context
        ];

        // Logger dans les fichiers Laravel
        if ($success) {
            Log::info('WhatsApp message sent', array_merge($result, ['message_preview' => substr($message, 0, 100)]));
        } else {
            Log::error('WhatsApp message failed', array_merge($result, ['message_preview' => substr($message, 0, 100)]));
        }

        // Logger dans la base de données si la table existe
        try {
            if (class_exists(WhatsAppLog::class)) {
                WhatsAppLog::create([
                    'to' => $to,
                    'message' => $message,
                    'status' => $status,
                    'success' => $success,
                    'response' => json_encode($context),
                    'sent_at' => now()
                ]);
            }
        } catch (\Exception $e) {
            // Ignorer si la table n'existe pas encore
            Log::debug('WhatsApp log table not available: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Tester la connexion à l'API WhatsApp
     *
     * @return array Résultat du test
     */
    public function testConnection(): array
    {
        try {
            if (empty($this->apiToken) || empty($this->phoneNumberId)) {
                return [
                    'success' => false,
                    'message' => 'API credentials not configured',
                    'details' => [
                        'api_token_set' => !empty($this->apiToken),
                        'phone_number_id_set' => !empty($this->phoneNumberId)
                    ]
                ];
            }

            // Tester avec une requête GET sur le phone number
            $response = Http::timeout($this->timeout)
                ->withToken($this->apiToken)
                ->get("{$this->apiUrl}/{$this->phoneNumberId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'WhatsApp API connection successful',
                    'details' => $response->json()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'WhatsApp API connection failed',
                    'status_code' => $response->status(),
                    'error' => $response->json()
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test exception',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir les statistiques d'envoi
     *
     * @param int $days Nombre de jours
     * @return array Statistiques
     */
    public function getStats(int $days = 7): array
    {
        try {
            if (!class_exists(WhatsAppLog::class)) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp log table not available'
                ];
            }

            $startDate = now()->subDays($days);

            $stats = [
                'period' => $days . ' days',
                'start_date' => $startDate->toDateString(),
                'end_date' => now()->toDateString(),
                'total_sent' => WhatsAppLog::where('created_at', '>=', $startDate)->count(),
                'total_success' => WhatsAppLog::where('created_at', '>=', $startDate)
                    ->where('success', true)
                    ->count(),
                'total_failed' => WhatsAppLog::where('created_at', '>=', $startDate)
                    ->where('success', false)
                    ->count(),
            ];

            $stats['success_rate'] = $stats['total_sent'] > 0
                ? round(($stats['total_success'] / $stats['total_sent']) * 100, 2) . '%'
                : '0%';

            return [
                'success' => true,
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to get stats',
                'error' => $e->getMessage()
            ];
        }
    }
}
