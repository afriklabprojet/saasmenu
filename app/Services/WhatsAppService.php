<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class WhatsAppService
{
    protected $apiUrl;
    protected $apiToken;
    protected $phoneNumberId;
    protected $businessAccountId;

    public function __construct()
    {
        $this->apiUrl = config('whatsapp.api_url', 'https://graph.facebook.com/v18.0');
        $this->apiToken = config('whatsapp.api_token');
        $this->phoneNumberId = config('whatsapp.phone_number_id');
        $this->businessAccountId = config('whatsapp.business_account_id');
    }

    /**
     * Envoyer une notification de nouvelle commande au restaurant
     */
    public function sendOrderNotification($order, $restaurant)
    {
        try {
            $phone = $this->formatPhoneNumber($restaurant->whatsapp_phone ?? $restaurant->phone);

            if (!$phone) {
                throw new Exception("Numéro WhatsApp du restaurant invalide");
            }

            $message = $this->formatOrderMessage($order, $restaurant);

            $result = $this->sendMessage($phone, $message);

            // Log l'envoi
            $this->logMessage([
                'order_id' => $order->id,
                'restaurant_id' => $restaurant->id,
                'phone' => $phone,
                'message_type' => 'order_notification',
                'status' => $result['success'] ? 'sent' : 'failed',
                'message_id' => $result['message_id'] ?? null,
                'error' => $result['error'] ?? null,
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('WhatsApp Order Notification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer une confirmation de paiement au client
     */
    public function sendPaymentConfirmation($order, $customer)
    {
        try {
            $phone = $this->formatPhoneNumber($customer->phone);

            if (!$phone) {
                throw new Exception("Numéro WhatsApp du client invalide");
            }

            $message = $this->formatPaymentConfirmationMessage($order, $customer);

            $result = $this->sendMessage($phone, $message);

            $this->logMessage([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'phone' => $phone,
                'message_type' => 'payment_confirmation',
                'status' => $result['success'] ? 'sent' : 'failed',
                'message_id' => $result['message_id'] ?? null,
                'error' => $result['error'] ?? null,
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('WhatsApp Payment Confirmation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer une mise à jour de livraison au client
     */
    public function sendDeliveryUpdate($order, $customer, $status)
    {
        try {
            $phone = $this->formatPhoneNumber($customer->phone);

            if (!$phone) {
                throw new Exception("Numéro WhatsApp du client invalide");
            }

            $message = $this->formatDeliveryUpdateMessage($order, $customer, $status);

            $result = $this->sendMessage($phone, $message);

            $this->logMessage([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'phone' => $phone,
                'message_type' => 'delivery_update',
                'status' => $result['success'] ? 'sent' : 'failed',
                'message_id' => $result['message_id'] ?? null,
                'error' => $result['error'] ?? null,
            ]);

            return $result;
        } catch (Exception $e) {
            Log::error('WhatsApp Delivery Update Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Envoyer un message WhatsApp via l'API
     */
    public function sendMessage($phone, $message, $useTemplate = false)
    {
        try {
            $url = "{$this->apiUrl}/{$this->phoneNumberId}/messages";

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $phone,
            ];

            if ($useTemplate && isset($message['template'])) {
                $payload['type'] = 'template';
                $payload['template'] = $message['template'];
            } else {
                $payload['type'] = 'text';
                $payload['text'] = [
                    'preview_url' => false,
                    'body' => $message
                ];
            }

            $response = Http::withToken($this->apiToken)
                ->timeout(config('whatsapp.timeout', 30))
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message_id' => $data['messages'][0]['id'] ?? null,
                    'phone' => $phone
                ];
            } else {
                $error = $response->json();
                Log::error('WhatsApp API Error', [
                    'status' => $response->status(),
                    'error' => $error
                ]);

                return [
                    'success' => false,
                    'error' => $error['error']['message'] ?? 'Unknown error',
                    'error_code' => $error['error']['code'] ?? null
                ];
            }
        } catch (Exception $e) {
            Log::error('WhatsApp Send Message Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Formater le message de nouvelle commande
     */
    protected function formatOrderMessage($order, $restaurant)
    {
        $items = $order->orderitems->map(function($item) {
            return "• {$item->name} x{$item->quantity} - " . config('settings.currency_symbol', 'XOF') . " " . number_format($item->price, 0, ',', ' ');
        })->implode("\n");

        $total = config('settings.currency_symbol', 'XOF') . " " . number_format($order->order_price, 0, ',', ' ');

        $message = "🔔 *NOUVELLE COMMANDE #{$order->id}*\n\n";
        $message .= "👤 Client: {$order->client->name}\n";
        $message .= "📱 Tél: {$order->client->phone}\n";
        $message .= "📍 Adresse: {$order->address}\n\n";
        $message .= "📦 *Articles:*\n{$items}\n\n";
        $message .= "💰 *Total: {$total}*\n";
        $message .= "💳 Paiement: {$order->payment_method}\n";
        $message .= "⏰ Commande passée le: " . $order->created_at->format('d/m/Y à H:i');

        if ($order->comment) {
            $message .= "\n\n📝 Note: {$order->comment}";
        }

        return $message;
    }

    /**
     * Formater le message de confirmation de paiement
     */
    protected function formatPaymentConfirmationMessage($order, $customer)
    {
        $total = config('settings.currency_symbol', 'XOF') . " " . number_format($order->order_price, 0, ',', ' ');

        $message = "✅ *PAIEMENT CONFIRMÉ*\n\n";
        $message .= "Bonjour {$customer->name},\n\n";
        $message .= "Votre paiement de *{$total}* pour la commande #{$order->id} a été confirmé avec succès.\n\n";
        $message .= "🍽️ Votre commande est en préparation !\n";
        $message .= "⏰ Livraison estimée: " . ($order->estimated_delivery_time ?? '30-45 minutes') . "\n\n";
        $message .= "Merci pour votre confiance ! 🙏";

        return $message;
    }

    /**
     * Formater le message de mise à jour de livraison
     */
    protected function formatDeliveryUpdateMessage($order, $customer, $status)
    {
        $statusMessages = [
            'accepted' => "✅ Votre commande #{$order->id} a été acceptée et est en préparation.",
            'prepared' => "👨‍🍳 Votre commande #{$order->id} est prête !",
            'on_the_way' => "🚗 Votre commande #{$order->id} est en route vers vous !",
            'delivered' => "✅ Votre commande #{$order->id} a été livrée. Bon appétit ! 🍽️",
            'cancelled' => "❌ Désolé, votre commande #{$order->id} a été annulée.",
        ];

        $message = "*MISE À JOUR DE LIVRAISON*\n\n";
        $message .= "Bonjour {$customer->name},\n\n";
        $message .= $statusMessages[$status] ?? "Statut de votre commande: {$status}";

        if ($status === 'on_the_way' && isset($order->driver)) {
            $message .= "\n\n🚗 Livreur: {$order->driver->name}";
            $message .= "\n📱 Contact: {$order->driver->phone}";
        }

        return $message;
    }

    /**
     * Formater un numéro de téléphone au format international WhatsApp
     */
    protected function formatPhoneNumber($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Supprimer tous les caractères non numériques
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Si le numéro commence par 0, le remplacer par le code pays
        if (substr($phone, 0, 1) === '0') {
            $countryCode = config('whatsapp.default_country_code', '225'); // Côte d'Ivoire par défaut
            $phone = $countryCode . substr($phone, 1);
        }

        // Valider la longueur (minimum 10 chiffres)
        if (strlen($phone) < 10) {
            return null;
        }

        return $phone;
    }

    /**
     * Valider un numéro de téléphone WhatsApp
     */
    public function validatePhoneNumber($phone)
    {
        $formattedPhone = $this->formatPhoneNumber($phone);
        return !is_null($formattedPhone);
    }

    /**
     * Logger un message envoyé
     */
    protected function logMessage($data)
    {
        try {
            DB::table('whatsapp_messages_log')->insert([
                'order_id' => $data['order_id'] ?? null,
                'restaurant_id' => $data['restaurant_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'phone' => $data['phone'],
                'message_type' => $data['message_type'],
                'status' => $data['status'],
                'message_id' => $data['message_id'] ?? null,
                'error' => $data['error'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('WhatsApp Log Error: ' . $e->getMessage());
        }
    }

    /**
     * Récupérer les statistiques d'envoi
     */
    public function getStatistics($restaurantId = null, $days = 30)
    {
        $query = DB::table('whatsapp_messages_log')
            ->where('created_at', '>=', now()->subDays($days));

        if ($restaurantId) {
            $query->where('restaurant_id', $restaurantId);
        }

        return [
            'total_sent' => $query->clone()->where('status', 'sent')->count(),
            'total_failed' => $query->clone()->where('status', 'failed')->count(),
            'by_type' => $query->clone()->select('message_type', DB::raw('count(*) as total'))
                ->groupBy('message_type')
                ->get(),
        ];
    }

    /**
     * Tester la connexion WhatsApp
     */
    public function testConnection($testPhone = null)
    {
        try {
            $phone = $testPhone ?? config('whatsapp.test_phone');

            if (!$phone) {
                return [
                    'success' => false,
                    'error' => 'Numéro de test non configuré'
                ];
            }

            $message = "🧪 Test de connexion WhatsApp - " . now()->format('d/m/Y H:i:s');

            return $this->sendMessage($this->formatPhoneNumber($phone), $message);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
