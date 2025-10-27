<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class FirebaseService
{
    protected $serverKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->serverKey = config('firebase.server_key', env('FIREBASE_SERVER_KEY'));
        $this->apiUrl = 'https://fcm.googleapis.com/fcm/send';
    }

    /**
     * Envoyer une notification
     */
    public function sendNotification($notification)
    {
        try {
            if (!$this->serverKey) {
                throw new Exception('Firebase server key not configured');
            }

            // Récupérer les tokens des destinataires
            $tokens = $this->getTargetTokens($notification);

            if ($tokens->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Aucun token d\'appareil trouvé pour les destinataires'
                ];
            }

            $sentCount = 0;
            $failedTokens = [];

            // Envoyer par batch pour éviter les limites
            $batches = $tokens->chunk(1000);

            foreach ($batches as $batch) {
                $result = $this->sendToTokens($batch->pluck('device_token')->toArray(), $notification);

                $sentCount += $result['success_count'];
                $failedTokens = array_merge($failedTokens, $result['failed_tokens']);
            }

            // Désactiver les tokens invalides
            if (!empty($failedTokens)) {
                DeviceToken::whereIn('device_token', $failedTokens)
                    ->update(['is_active' => false]);
            }

            // Mettre à jour la notification
            $notification->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_count' => $sentCount,
                'failed_count' => count($failedTokens)
            ]);

            return [
                'success' => true,
                'sent_count' => $sentCount,
                'failed_count' => count($failedTokens)
            ];

        } catch (Exception $e) {
            Log::error('Firebase notification error: ' . $e->getMessage());

            // Marquer la notification comme échouée
            $notification->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer à une liste de tokens
     */
    private function sendToTokens($tokens, $notification)
    {
        try {
            $payload = [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $notification->title,
                    'body' => $notification->message,
                    'icon' => $notification->icon ?? 'default',
                    'sound' => 'default',
                ],
                'data' => array_merge([
                    'notification_id' => $notification->id,
                    'type' => $notification->type,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ], $notification->data ?? [])
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $result = $response->json();

                $successCount = $result['success'] ?? 0;
                $failedTokens = [];

                // Analyser les résultats pour identifier les tokens invalides
                if (isset($result['results'])) {
                    foreach ($result['results'] as $index => $res) {
                        if (isset($res['error'])) {
                            $failedTokens[] = $tokens[$index];
                        }
                    }
                }

                return [
                    'success_count' => $successCount,
                    'failed_tokens' => $failedTokens
                ];
            }

            throw new Exception('Firebase API error: ' . $response->body());

        } catch (Exception $e) {
            Log::error('Firebase send error: ' . $e->getMessage());
            return [
                'success_count' => 0,
                'failed_tokens' => $tokens
            ];
        }
    }

    /**
     * Obtenir les tokens des destinataires
     */
    private function getTargetTokens($notification)
    {
        $query = DeviceToken::active();

        // Filtrer selon le type de destinataire
        switch ($notification->target_type) {
            case 'user':
                if ($notification->target_id) {
                    $query->where('user_id', $notification->target_id);
                }
                break;
            case 'restaurant':
                if ($notification->target_id) {
                    $query->whereHas('user', function($q) use ($notification) {
                        $q->where('restaurant_id', $notification->target_id);
                    });
                }
                break;
            case 'all':
            default:
                // Tous les tokens actifs
                break;
        }

        return $query->get();
    }

    /**
     * Enregistrer un token d'appareil
     */
    public function registerToken($userId, $token, $deviceInfo = [])
    {
        try {
            $deviceToken = DeviceToken::updateOrCreate([
                'user_id' => $userId,
                'device_token' => $token
            ], [
                'device_type' => $deviceInfo['platform'] ?? 'unknown',
                'device_info' => $deviceInfo,
                'app_version' => $deviceInfo['app_version'] ?? null,
                'is_active' => true,
                'last_used_at' => now()
            ]);

            return $deviceToken;

        } catch (Exception $e) {
            Log::error('Token registration error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Désinscrire un token
     */
    public function unregisterToken($token)
    {
        return DeviceToken::where('device_token', $token)
            ->update(['is_active' => false]);
    }

    /**
     * Tester l'envoi de notification
     */
    public function testNotification($token, $title = 'Test', $message = 'Test message')
    {
        try {
            $payload = [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                    'sound' => 'default'
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl, $payload);

            return [
                'success' => $response->successful(),
                'response' => $response->json()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
