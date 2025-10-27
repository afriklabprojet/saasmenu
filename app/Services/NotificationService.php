<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Types de notifications supportées
     */
    const TYPES = [
        'system_alert' => 'Alerte Système',
        'performance_warning' => 'Performance Dégradée',
        'security_incident' => 'Incident Sécurité',
        'backup_status' => 'Statut Backup',
        'order_critical' => 'Commande Critique',
        'restaurant_offline' => 'Restaurant Hors Ligne',
        'payment_failure' => 'Échec Paiement',
        'training_reminder' => 'Rappel Formation',
        'maintenance_scheduled' => 'Maintenance Programmée'
    ];

    /**
     * Canaux de notification disponibles
     */
    const CHANNELS = [
        'email' => 'Email',
        'sms' => 'SMS',
        'push' => 'Push Notification',
        'slack' => 'Slack',
        'webhook' => 'Webhook',
        'database' => 'Base de données'
    ];

    /**
     * Niveaux de priorité
     */
    const PRIORITIES = [
        'low' => 1,
        'normal' => 2,
        'high' => 3,
        'critical' => 4,
        'emergency' => 5
    ];

    /**
     * Envoyer notification
     */
    public function send($type, $message, $data = [], $priority = 'normal', $channels = ['database'])
    {
        try {
            $notification = $this->createNotification($type, $message, $data, $priority);

            // Log notification
            Log::channel('notifications')->info("Notification créée", [
                'id' => $notification['id'],
                'type' => $type,
                'priority' => $priority,
                'channels' => $channels
            ]);

            $results = [];
            foreach ($channels as $channel) {
                $results[$channel] = $this->sendToChannel($notification, $channel);
            }

            // Mettre à jour statut notification
            $this->updateNotificationStatus($notification['id'], $results);

            return [
                'success' => true,
                'notification_id' => $notification['id'],
                'channels_results' => $results
            ];

        } catch (\Exception $e) {
            Log::error("Erreur envoi notification: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Créer notification
     */
    private function createNotification($type, $message, $data, $priority)
    {
        $notification = [
            'id' => 'NOTIF_' . uniqid(),
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'priority' => $priority,
            'priority_level' => self::PRIORITIES[$priority] ?? 2,
            'status' => 'pending',
            'created_at' => now(),
            'attempts' => 0,
            'channels_status' => []
        ];

        // Sauvegarder en cache pour traitement
        Cache::put("notification_{$notification['id']}", $notification, now()->addDays(7));

        return $notification;
    }

    /**
     * Envoyer vers canal spécifique
     */
    private function sendToChannel($notification, $channel)
    {
        try {
            switch ($channel) {
                case 'email':
                    return $this->sendEmail($notification);
                case 'sms':
                    return $this->sendSMS($notification);
                case 'push':
                    return $this->sendPushNotification($notification);
                case 'slack':
                    return $this->sendSlack($notification);
                case 'webhook':
                    return $this->sendWebhook($notification);
                case 'database':
                    return $this->saveToDatabase($notification);
                default:
                    return ['success' => false, 'error' => 'Canal non supporté'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Envoi par email
     */
    private function sendEmail($notification)
    {
        $recipients = $this->getEmailRecipients($notification['type'], $notification['priority']);

        foreach ($recipients as $email) {
            Mail::raw($this->formatEmailMessage($notification), function ($message) use ($email, $notification) {
                $message->to($email)
                        ->subject($this->getEmailSubject($notification));
            });
        }

        return [
            'success' => true,
            'recipients_count' => count($recipients),
            'sent_at' => now()
        ];
    }

    /**
     * Envoi SMS
     */
    private function sendSMS($notification)
    {
        $recipients = $this->getSMSRecipients($notification['type'], $notification['priority']);
        $message = $this->formatSMSMessage($notification);

        // Vérifier configuration SMS
        $apiUrl = env('SMS_API_URL');
        if (!$apiUrl) {
            return [
                'success' => false,
                'error' => 'Configuration SMS manquante (SMS_API_URL)',
                'recipients_count' => count($recipients)
            ];
        }

        $successCount = 0;
        foreach ($recipients as $phone) {
            try {
                // Intégration avec service SMS
                $response = Http::post($apiUrl, [
                    'to' => $phone,
                    'message' => $message,
                    'api_key' => env('SMS_API_KEY')
                ]);

                if ($response->successful()) {
                    $successCount++;
                }
            } catch (\Exception $e) {
                Log::error("Erreur envoi SMS: " . $e->getMessage());
            }
        }

        return [
            'success' => $successCount > 0,
            'recipients_count' => count($recipients),
            'success_count' => $successCount,
            'sent_at' => now(),
            'note' => $apiUrl ? null : 'Mode simulation (pas de configuration SMS)'
        ];
    }

    /**
     * Envoi Push Notification
     */
    private function sendPushNotification($notification)
    {
        // Intégration Firebase Cloud Messaging ou service similaire
        $tokens = $this->getPushTokens($notification['type']);

        $payload = [
            'title' => $this->getPushTitle($notification),
            'body' => $this->formatPushMessage($notification),
            'data' => $notification['data']
        ];

        // Simulation envoi FCM
        $response = Http::post('https://fcm.googleapis.com/fcm/send', [
            'registration_ids' => $tokens,
            'notification' => $payload,
            'data' => $notification['data']
        ], [
            'Authorization' => 'key=' . env('FCM_SERVER_KEY'),
            'Content-Type' => 'application/json'
        ]);

        return [
            'success' => $response->successful(),
            'tokens_count' => count($tokens),
            'response' => $response->json(),
            'sent_at' => now()
        ];
    }

    /**
     * Envoi vers Slack
     */
    private function sendSlack($notification)
    {
        $webhookUrl = env('SLACK_WEBHOOK_URL');
        if (!$webhookUrl) {
            return ['success' => false, 'error' => 'Slack webhook non configuré'];
        }

        $payload = [
            'text' => $this->formatSlackMessage($notification),
            'username' => 'RestroSaaS Monitor',
            'icon_emoji' => $this->getSlackEmoji($notification['type']),
            'attachments' => [[
                'color' => $this->getSlackColor($notification['priority']),
                'fields' => [
                    [
                        'title' => 'Type',
                        'value' => self::TYPES[$notification['type']] ?? $notification['type'],
                        'short' => true
                    ],
                    [
                        'title' => 'Priorité',
                        'value' => strtoupper($notification['priority']),
                        'short' => true
                    ],
                    [
                        'title' => 'Timestamp',
                        'value' => $notification['created_at']->format('d/m/Y H:i:s'),
                        'short' => true
                    ]
                ]
            ]]
        ];

        $response = Http::post($webhookUrl, $payload);

        return [
            'success' => $response->successful(),
            'status_code' => $response->status(),
            'sent_at' => now()
        ];
    }

    /**
     * Envoi webhook personnalisé
     */
    private function sendWebhook($notification)
    {
        $webhooks = $this->getWebhooks($notification['type']);
        $successCount = 0;

        if (empty($webhooks)) {
            return [
                'success' => false,
                'error' => 'Aucun webhook configuré',
                'webhooks_count' => 0
            ];
        }

        foreach ($webhooks as $webhook) {
            if (!isset($webhook['url']) || !$webhook['url']) {
                continue;
            }

            try {
                $response = Http::post($webhook['url'], [
                    'notification' => $notification,
                    'signature' => $this->generateWebhookSignature($notification, $webhook['secret'] ?? '')
                ]);

                if ($response->successful()) {
                    $successCount++;
                }
            } catch (\Exception $e) {
                Log::error("Erreur webhook: " . $e->getMessage());
            }
        }

        return [
            'success' => $successCount > 0,
            'webhooks_count' => count($webhooks),
            'success_count' => $successCount,
            'sent_at' => now()
        ];
    }

    /**
     * Sauvegarde en base de données
     */
    private function saveToDatabase($notification)
    {
        // En production, sauvegarder dans table notifications
        // Pour l'exemple, utilisation du cache
        Cache::put("db_notification_{$notification['id']}", $notification, now()->addMonths(3));

        return [
            'success' => true,
            'saved_at' => now()
        ];
    }

    /**
     * Notifications système automatiques
     */
    public function sendSystemAlert($level, $message, $details = [])
    {
        $priority = match($level) {
            'critical' => 'critical',
            'error' => 'high',
            'warning' => 'normal',
            'info' => 'low',
            default => 'normal'
        };

        $channels = match($priority) {
            'critical' => ['email', 'sms', 'slack', 'database'],
            'high' => ['email', 'slack', 'database'],
            'normal' => ['email', 'database'],
            'low' => ['database']
        };

        return $this->send('system_alert', $message, $details, $priority, $channels);
    }

    /**
     * Notification échec backup
     */
    public function notifyBackupFailure($backupType, $error)
    {
        $message = "🚨 ÉCHEC BACKUP {$backupType}: {$error}";

        return $this->send('backup_status', $message, [
            'backup_type' => $backupType,
            'error' => $error,
            'timestamp' => now()
        ], 'high', ['email', 'slack', 'sms']);
    }

    /**
     * Notification performance dégradée
     */
    public function notifyPerformanceIssue($metric, $value, $threshold)
    {
        $message = "⚡ PERFORMANCE DÉGRADÉE: {$metric} = {$value} (seuil: {$threshold})";

        return $this->send('performance_warning', $message, [
            'metric' => $metric,
            'value' => $value,
            'threshold' => $threshold,
            'severity' => $this->calculateSeverity($value, $threshold)
        ], 'high', ['email', 'slack']);
    }

    /**
     * Notification restaurant hors ligne
     */
    public function notifyRestaurantOffline($restaurantId, $restaurantName, $duration)
    {
        $message = "🏪 RESTAURANT HORS LIGNE: {$restaurantName} (ID: {$restaurantId}) - Durée: {$duration}min";

        return $this->send('restaurant_offline', $message, [
            'restaurant_id' => $restaurantId,
            'restaurant_name' => $restaurantName,
            'offline_duration' => $duration,
            'impact_level' => $duration > 30 ? 'high' : 'medium'
        ], $duration > 30 ? 'high' : 'normal', ['email', 'database']);
    }

    /**
     * Notification formation obligatoire
     */
    public function notifyTrainingReminder($userId, $moduleName, $deadline)
    {
        $message = "🎓 FORMATION OBLIGATOIRE: {$moduleName} - Échéance: {$deadline->format('d/m/Y')}";

        return $this->send('training_reminder', $message, [
            'user_id' => $userId,
            'module_name' => $moduleName,
            'deadline' => $deadline,
            'days_remaining' => $deadline->diffInDays(now())
        ], 'normal', ['email', 'database']);
    }

    /**
     * Obtenir historique notifications
     */
    public function getNotificationHistory($filters = [])
    {
        // Récupérer depuis cache ou base de données
        $notifications = [];

        // Simuler récupération
        $cacheKeys = Cache::get('notification_keys', []);
        foreach ($cacheKeys as $key) {
            $notification = Cache::get($key);
            if ($notification && $this->matchesFilters($notification, $filters)) {
                $notifications[] = $notification;
            }
        }

        // Trier par date
        usort($notifications, function($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return [
            'success' => true,
            'notifications' => $notifications,
            'total' => count($notifications),
            'filters' => $filters
        ];
    }

    /**
     * Statistiques notifications
     */
    public function getNotificationStats($period = '24h')
    {
        $stats = [
            'period' => $period,
            'total_sent' => 0,
            'by_type' => [],
            'by_priority' => [],
            'by_channel' => [],
            'success_rate' => 0,
            'response_times' => [],
            'top_issues' => []
        ];

        // Calculer stats depuis historique
        $history = $this->getNotificationHistory();

        foreach ($history['notifications'] as $notification) {
            $stats['total_sent']++;

            // Par type
            $type = $notification['type'];
            $stats['by_type'][$type] = ($stats['by_type'][$type] ?? 0) + 1;

            // Par priorité
            $priority = $notification['priority'];
            $stats['by_priority'][$priority] = ($stats['by_priority'][$priority] ?? 0) + 1;
        }

        return [
            'success' => true,
            'stats' => $stats,
            'generated_at' => now()
        ];
    }

    /**
     * Test du système de notifications
     */
    public function testNotificationSystem()
    {
        $results = [];
        $testNotification = [
            'id' => 'TEST_' . uniqid(),
            'type' => 'system_alert',
            'message' => '🧪 Test système notifications RestroSaaS',
            'data' => ['test' => true, 'timestamp' => now()],
            'priority' => 'low',
            'created_at' => now(),
            'status' => 'test'
        ];

        foreach (self::CHANNELS as $channel => $name) {
            try {
                $result = $this->sendToChannel($testNotification, $channel);
                $results[$channel] = [
                    'name' => $name,
                    'success' => $result['success'] ?? false,
                    'details' => $result
                ];
            } catch (\Exception $e) {
                $results[$channel] = [
                    'name' => $name,
                    'success' => false,
                    'details' => ['error' => $e->getMessage()]
                ];
            }
        }

        return [
            'success' => true,
            'test_results' => $results,
            'timestamp' => now()
        ];
    }

    // Méthodes utilitaires privées

    private function getEmailRecipients($type, $priority)
    {
        // Configuration des destinataires par type/priorité
        $config = [
            'critical' => ['admin@restro-saas.com', 'emergency@restro-saas.com'],
            'high' => ['admin@restro-saas.com'],
            'normal' => ['support@restro-saas.com'],
            'low' => ['logs@restro-saas.com']
        ];

        return $config[$priority] ?? ['admin@restro-saas.com'];
    }

    private function getSMSRecipients($type, $priority)
    {
        // Numéros d'urgence selon priorité
        if ($priority === 'critical') {
            return ['+33612345678', '+33687654321']; // Manager + CTO
        }
        return ['+33612345678']; // Manager uniquement
    }

    private function getPushTokens($type)
    {
        // Récupérer tokens FCM des admins connectés
        return Cache::get('admin_push_tokens', []);
    }

    private function getWebhooks($type)
    {
        // Configuration webhooks par type
        $webhooks = [];

        // Ajouter seulement si URL configurée
        if ($url = env('WEBHOOK_URL_1')) {
            $webhooks[] = ['url' => $url, 'secret' => env('WEBHOOK_SECRET_1', '')];
        }

        return $webhooks;
    }

    private function formatEmailMessage($notification)
    {
        return "RestroSaaS - {$notification['type']}\n\n" .
               "{$notification['message']}\n\n" .
               "Priorité: {$notification['priority']}\n" .
               "Timestamp: {$notification['created_at']}\n\n" .
               "Détails: " . json_encode($notification['data'], JSON_PRETTY_PRINT);
    }

    private function formatSMSMessage($notification)
    {
        return "RestroSaaS ALERT: {$notification['message']} [{$notification['priority']}]";
    }

    private function formatPushMessage($notification)
    {
        return $notification['message'];
    }

    private function formatSlackMessage($notification)
    {
        return "🚨 *RestroSaaS Alert*\n{$notification['message']}";
    }

    private function getEmailSubject($notification)
    {
        $prefix = match($notification['priority']) {
            'critical' => '🚨 CRITIQUE',
            'high' => '⚠️ URGENT',
            'normal' => '📊 INFO',
            'low' => '💬 LOG'
        };

        return "{$prefix} RestroSaaS - " . (self::TYPES[$notification['type']] ?? $notification['type']);
    }

    private function getPushTitle($notification)
    {
        return "RestroSaaS - " . (self::TYPES[$notification['type']] ?? $notification['type']);
    }

    private function getSlackEmoji($type)
    {
        $emojis = [
            'system_alert' => ':warning:',
            'performance_warning' => ':chart_with_downwards_trend:',
            'security_incident' => ':shield:',
            'backup_status' => ':floppy_disk:',
            'order_critical' => ':shopping_cart:',
            'restaurant_offline' => ':x:'
        ];

        return $emojis[$type] ?? ':bell:';
    }

    private function getSlackColor($priority)
    {
        return match($priority) {
            'critical' => '#ff0000',
            'high' => '#ff6600',
            'normal' => '#ffcc00',
            'low' => '#00cc00'
        };
    }

    private function generateWebhookSignature($notification, $secret)
    {
        return hash_hmac('sha256', json_encode($notification), $secret);
    }

    private function calculateSeverity($value, $threshold)
    {
        $ratio = $value / $threshold;
        if ($ratio >= 2) return 'critical';
        if ($ratio >= 1.5) return 'high';
        if ($ratio >= 1.2) return 'medium';
        return 'low';
    }

    private function matchesFilters($notification, $filters)
    {
        foreach ($filters as $key => $value) {
            if (isset($notification[$key]) && $notification[$key] !== $value) {
                return false;
            }
        }
        return true;
    }

    private function updateNotificationStatus($notificationId, $results)
    {
        $notification = Cache::get("notification_{$notificationId}");
        if ($notification) {
            $notification['channels_status'] = $results;
            $notification['status'] = 'sent';
            $notification['sent_at'] = now();
            Cache::put("notification_{$notificationId}", $notification, now()->addDays(7));
        }
    }
}
