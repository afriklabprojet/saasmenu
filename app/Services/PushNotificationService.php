<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Exception;

class PushNotificationService
{
    /**
     * Envoyer une notification à un utilisateur spécifique
     */
    public function sendToUser(int $userId, array $payload): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur introuvable'];
        }

        $subscriptions = PushSubscription::forUser($userId)->active()->get();

        if ($subscriptions->isEmpty()) {
            return ['success' => false, 'message' => 'Aucune souscription active'];
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Envoyer une notification à tous les utilisateurs
     */
    public function sendToAll(array $payload): array
    {
        $subscriptions = PushSubscription::active()->get();

        if ($subscriptions->isEmpty()) {
            return ['success' => false, 'message' => 'Aucune souscription active'];
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Envoyer une notification à plusieurs utilisateurs
     */
    public function sendToUsers(array $userIds, array $payload): array
    {
        $subscriptions = PushSubscription::whereIn('user_id', $userIds)
            ->active()
            ->get();

        if ($subscriptions->isEmpty()) {
            return ['success' => false, 'message' => 'Aucune souscription active pour ces utilisateurs'];
        }

        return $this->sendToSubscriptions($subscriptions, $payload);
    }

    /**
     * Envoyer des notifications à une collection de souscriptions
     */
    protected function sendToSubscriptions(Collection $subscriptions, array $payload): array
    {
        $results = [
            'success' => true,
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($subscriptions as $subscription) {
            try {
                $result = $this->sendNotification($subscription, $payload);

                if ($result['success']) {
                    $results['sent']++;
                    $subscription->markAsUsed();
                } else {
                    $results['failed']++;
                    $results['errors'][] = $result['error'];

                    // Désactiver les souscriptions invalides
                    if (in_array($result['error_code'] ?? null, [410, 400])) {
                        $subscription->deactivate();
                    }
                }
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = $e->getMessage();
                Log::error('Erreur envoi notification push', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $results;
    }

    /**
     * Envoyer une notification à une souscription spécifique
     */
    protected function sendNotification(PushSubscription $subscription, array $payload): array
    {
        // Préparer les données de la notification
        $notificationData = $this->prepareNotificationData($payload);

        // Simuler l'envoi (remplacer par votre implémentation réelle)
        // Vous devrez utiliser une librairie comme web-push-php pour l'envoi réel

        try {
            // Exemple avec web-push (vous devez installer minishlink/web-push)
            // $webPush = new WebPush([
            //     'VAPID' => [
            //         'subject' => config('app.url'),
            //         'publicKey' => config('webpush.vapid.public_key'),
            //         'privateKey' => config('webpush.vapid.private_key'),
            //     ]
            // ]);

            // $report = $webPush->sendOneNotification(
            //     Subscription::create($subscription->toSubscriptionArray()),
            //     json_encode($notificationData)
            // );

            // Pour la démonstration, nous simulons un succès
            $success = true;

            if ($success) {
                Log::info('Notification push envoyée', [
                    'user_id' => $subscription->user_id,
                    'title' => $notificationData['title'] ?? 'Sans titre'
                ]);

                return ['success' => true];
            } else {
                return [
                    'success' => false,
                    'error' => 'Échec envoi notification',
                    'error_code' => 500
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * Préparer les données de la notification
     */
    protected function prepareNotificationData(array $payload): array
    {
        $defaults = [
            'title' => 'E-menu',
            'body' => 'Nouvelle notification',
            'icon' => '/images/logo.png',
            'badge' => '/images/badge.png',
            'tag' => 'e-menu-notification',
            'requireInteraction' => false,
            'actions' => [
                [
                    'action' => 'view',
                    'title' => 'Voir',
                    'icon' => '/images/view-icon.png'
                ],
                [
                    'action' => 'dismiss',
                    'title' => 'Ignorer'
                ]
            ],
            'data' => [
                'url' => '/',
                'timestamp' => time()
            ]
        ];

        return array_merge($defaults, $payload);
    }

    /**
     * Créer une notification pour une nouvelle commande
     */
    public function notifyNewOrder(int $userId, array $orderData): array
    {
        $payload = [
            'title' => 'Nouvelle commande reçue !',
            'body' => 'Commande #' . ($orderData['order_number'] ?? 'N/A') . ' - ' . ($orderData['total'] ?? '0') . '€',
            'icon' => '/images/order-icon.png',
            'tag' => 'new-order-' . ($orderData['id'] ?? time()),
            'requireInteraction' => true,
            'data' => [
                'url' => '/orders/' . ($orderData['id'] ?? ''),
                'order_id' => $orderData['id'] ?? null,
                'type' => 'new_order'
            ]
        ];

        return $this->sendToUser($userId, $payload);
    }

    /**
     * Créer une notification pour le changement de statut d'une commande
     */
    public function notifyOrderStatusChange(int $userId, array $orderData): array
    {
        $status = $orderData['status'] ?? 'inconnue';
        $statusMessages = [
            'confirmed' => 'Votre commande a été confirmée',
            'preparing' => 'Votre commande est en préparation',
            'ready' => 'Votre commande est prête !',
            'delivered' => 'Votre commande a été livrée',
            'cancelled' => 'Votre commande a été annulée'
        ];

        $payload = [
            'title' => 'Mise à jour de commande',
            'body' => $statusMessages[$status] ?? 'Statut de commande mis à jour',
            'icon' => '/images/status-icon.png',
            'tag' => 'order-status-' . ($orderData['id'] ?? time()),
            'data' => [
                'url' => '/orders/' . ($orderData['id'] ?? ''),
                'order_id' => $orderData['id'] ?? null,
                'type' => 'status_change',
                'status' => $status
            ]
        ];

        return $this->sendToUser($userId, $payload);
    }

    /**
     * Créer une notification promotionnelle
     */
    public function notifyPromotion(array $userIds, array $promoData): array
    {
        $payload = [
            'title' => $promoData['title'] ?? 'Offre spéciale !',
            'body' => $promoData['message'] ?? 'Ne manquez pas cette offre exceptionnelle',
            'icon' => '/images/promo-icon.png',
            'tag' => 'promo-' . ($promoData['id'] ?? time()),
            'requireInteraction' => true,
            'data' => [
                'url' => $promoData['url'] ?? '/',
                'type' => 'promotion',
                'promo_id' => $promoData['id'] ?? null
            ]
        ];

        return $this->sendToUsers($userIds, $payload);
    }

    /**
     * Nettoyer les anciennes souscriptions
     */
    public function cleanupOldSubscriptions(int $days = 30): int
    {
        $cutoff = now()->subDays($days);

        $deleted = PushSubscription::where('last_used_at', '<', $cutoff)
            ->orWhere(function ($query) use ($cutoff) {
                $query->whereNull('last_used_at')
                      ->where('created_at', '<', $cutoff);
            })
            ->delete();

        Log::info("Nettoyage des souscriptions push: {$deleted} supprimées");

        return $deleted;
    }

    /**
     * Obtenir les statistiques des notifications
     */
    public function getStats(): array
    {
        return [
            'total_subscriptions' => PushSubscription::count(),
            'active_subscriptions' => PushSubscription::active()->count(),
            'users_with_notifications' => PushSubscription::active()->distinct('user_id')->count(),
            'recent_subscriptions' => PushSubscription::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }
}
