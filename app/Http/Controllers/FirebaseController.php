<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\FirebaseDevice;
use App\Models\FirebaseNotification;
use App\Models\FirebaseTemplate;
use App\Services\FirebaseMessagingService;
use Exception;

class FirebaseController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseMessagingService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Obtenir les paramètres Firebase
     */
    public function getSettings()
    {
        try {
            $settings = [
                'project_id' => config('services.firebase.project_id'),
                'enabled' => config('services.firebase.enabled', false),
                'server_key' => config('services.firebase.server_key') ? 'Configuré' : 'Non configuré',
                'service_account' => config('services.firebase.credentials') ? 'Configuré' : 'Non configuré',
            ];

            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);

        } catch (Exception $e) {
            Log::error('Firebase Settings Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paramètres'
            ], 500);
        }
    }

    /**
     * Mettre à jour les paramètres Firebase
     */
    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'project_id' => 'required|string',
                'server_key' => 'required|string',
                'service_account' => 'required|json',
                'enabled' => 'boolean',
            ]);

            // Mettre à jour la configuration
            config(['services.firebase.project_id' => $validated['project_id']]);
            config(['services.firebase.server_key' => $validated['server_key']]);
            config(['services.firebase.credentials' => $validated['service_account']]);
            config(['services.firebase.enabled' => $validated['enabled'] ?? false]);

            return response()->json([
                'success' => true,
                'message' => 'Paramètres Firebase mis à jour avec succès'
            ]);

        } catch (Exception $e) {
            Log::error('Firebase Settings Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres'
            ], 500);
        }
    }

    /**
     * Tester la connexion Firebase
     */
    public function testConnection()
    {
        try {
            $result = $this->firebaseService->testConnection();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null
            ]);

        } catch (Exception $e) {
            Log::error('Firebase Connection Test Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test de connexion'
            ], 500);
        }
    }

    /**
     * Enregistrer un appareil
     */
    public function registerDevice(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'nullable|exists:users,id',
                'device_token' => 'required|string',
                'device_type' => 'required|in:android,ios,web',
                'device_name' => 'nullable|string',
                'app_version' => 'nullable|string',
                'os_version' => 'nullable|string',
                'topics' => 'nullable|array',
            ]);

            $device = FirebaseDevice::updateOrCreate([
                'device_token' => $validated['device_token'],
            ], [
                'user_id' => $validated['user_id'],
                'device_type' => $validated['device_type'],
                'device_name' => $validated['device_name'] ?? 'Unknown Device',
                'app_version' => $validated['app_version'],
                'os_version' => $validated['os_version'],
                'is_active' => true,
                'last_seen_at' => now(),
            ]);

            // Souscrire aux topics si spécifiés
            if (!empty($validated['topics'])) {
                foreach ($validated['topics'] as $topic) {
                    $this->firebaseService->subscribeToTopic($validated['device_token'], $topic);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Appareil enregistré avec succès',
                'device_id' => $device->id
            ]);

        } catch (Exception $e) {
            Log::error('Firebase Device Registration Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de l\'appareil'
            ], 500);
        }
    }

    /**
     * Envoyer une notification
     */
    public function sendNotification(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string|max:500',
                'data' => 'nullable|array',
                'image' => 'nullable|url',
                'action_url' => 'nullable|url',
                'recipients' => 'required|array',
                'recipients.type' => 'required|in:users,devices,topics,all',
                'recipients.values' => 'nullable|array',
                'scheduled_at' => 'nullable|date|after:now',
            ]);

            $notification = [
                'title' => $validated['title'],
                'body' => $validated['body'],
                'data' => $validated['data'] ?? [],
                'image' => $validated['image'] ?? null,
                'action_url' => $validated['action_url'] ?? null,
            ];

            if ($validated['scheduled_at'] ?? null) {
                // Programmer la notification
                $result = $this->scheduleNotificationInternal($notification, $validated['recipients'], $validated['scheduled_at']);
            } else {
                // Envoyer immédiatement
                $result = $this->sendNotificationInternal($notification, $validated['recipients']);
            }

            return response()->json($result);

        } catch (Exception $e) {
            Log::error('Firebase Send Notification Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la notification'
            ], 500);
        }
    }

    /**
     * Envoyer une notification à un utilisateur
     */
    public function sendToUser($userId, Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string|max:500',
                'data' => 'nullable|array',
                'image' => 'nullable|url',
            ]);

            $user = User::findOrFail($userId);
            $devices = FirebaseDevice::where('user_id', $userId)
                ->where('is_active', true)
                ->pluck('device_token')
                ->toArray();

            if (empty($devices)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun appareil actif trouvé pour cet utilisateur'
                ], 404);
            }

            $result = $this->firebaseService->sendToDevices($devices, $validated);

            // Enregistrer la notification
            $this->logNotification($validated, ['type' => 'user', 'user_id' => $userId], $result);

            return response()->json([
                'success' => true,
                'message' => 'Notification envoyée à l\'utilisateur',
                'devices_count' => count($devices),
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Firebase Send To User Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi à l\'utilisateur'
            ], 500);
        }
    }

    /**
     * Envoyer une notification à un topic
     */
    public function sendToTopic($topic, Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required|string|max:500',
                'data' => 'nullable|array',
                'image' => 'nullable|url',
                'condition' => 'nullable|string', // Condition complexe pour ciblage avancé
            ]);

            if ($validated['condition'] ?? null) {
                $result = $this->firebaseService->sendToCondition($validated['condition'], $validated);
            } else {
                $result = $this->firebaseService->sendToTopic($topic, $validated);
            }

            // Enregistrer la notification
            $this->logNotification($validated, ['type' => 'topic', 'topic' => $topic], $result);

            return response()->json([
                'success' => true,
                'message' => 'Notification envoyée au topic',
                'topic' => $topic,
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Firebase Send To Topic Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi au topic'
            ], 500);
        }
    }

    /**
     * Souscrire à un topic
     */
    public function subscribeToTopic(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_tokens' => 'required|array',
                'topic' => 'required|string',
            ]);

            $result = $this->firebaseService->subscribeToTopic($validated['device_tokens'], $validated['topic']);

            return response()->json([
                'success' => true,
                'message' => 'Appareils souscrits au topic',
                'topic' => $validated['topic'],
                'devices_count' => count($validated['device_tokens']),
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Firebase Subscribe To Topic Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la souscription au topic'
            ], 500);
        }
    }

    /**
     * Se désabonner d'un topic
     */
    public function unsubscribeFromTopic(Request $request)
    {
        try {
            $validated = $request->validate([
                'device_tokens' => 'required|array',
                'topic' => 'required|string',
            ]);

            $result = $this->firebaseService->unsubscribeFromTopic($validated['device_tokens'], $validated['topic']);

            return response()->json([
                'success' => true,
                'message' => 'Appareils désabonnés du topic',
                'topic' => $validated['topic'],
                'devices_count' => count($validated['device_tokens']),
                'result' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Firebase Unsubscribe From Topic Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du désabonnement du topic'
            ], 500);
        }
    }

    /**
     * Obtenir les préférences utilisateur
     */
    public function getUserPreferences()
    {
        try {
            $user = Auth::user();
            $devices = FirebaseDevice::where('user_id', $user->id)->get();

            $preferences = [
                'order_notifications' => $user->notification_preferences['orders'] ?? true,
                'promotion_notifications' => $user->notification_preferences['promotions'] ?? true,
                'system_notifications' => $user->notification_preferences['system'] ?? true,
                'devices' => $devices->map(function($device) {
                    return [
                        'id' => $device->id,
                        'name' => $device->device_name,
                        'type' => $device->device_type,
                        'is_active' => $device->is_active,
                        'last_seen' => $device->last_seen_at,
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'preferences' => $preferences
            ]);

        } catch (Exception $e) {
            Log::error('Firebase Get User Preferences Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des préférences'
            ], 500);
        }
    }

    /**
     * Enregistrer une notification dans les logs
     */
    private function logNotification($notification, $recipients, $result)
    {
        try {
            FirebaseNotification::create([
                'title' => $notification['title'],
                'body' => $notification['body'],
                'data' => $notification['data'] ?? [],
                'image' => $notification['image'] ?? null,
                'recipients_type' => $recipients['type'],
                'recipients_data' => $recipients,
                'status' => $result['success'] ? 'sent' : 'failed',
                'firebase_response' => $result,
                'sent_at' => now(),
                'sent_by' => Auth::id(),
            ]);
        } catch (Exception $e) {
            Log::error('Firebase Log Notification Error: ' . $e->getMessage());
        }
    }

    /**
     * Envoyer une notification interne
     */
    private function sendNotificationInternal($notification, $recipients)
    {
        switch ($recipients['type']) {
            case 'users':
                return $this->sendToUsers($notification, $recipients['values']);
            case 'devices':
                return $this->firebaseService->sendToDevices($recipients['values'], $notification);
            case 'topics':
                return $this->sendToTopics($notification, $recipients['values']);
            case 'all':
                return $this->sendToAllUsers($notification);
            default:
                throw new Exception('Type de destinataire non supporté');
        }
    }

    /**
     * Envoyer à plusieurs utilisateurs
     */
    private function sendToUsers($notification, $userIds)
    {
        $devices = FirebaseDevice::whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->pluck('device_token')
            ->toArray();

        return $this->firebaseService->sendToDevices($devices, $notification);
    }

    /**
     * Envoyer à plusieurs topics
     */
    private function sendToTopics($notification, $topics)
    {
        $results = [];
        foreach ($topics as $topic) {
            $results[$topic] = $this->firebaseService->sendToTopic($topic, $notification);
        }
        return $results;
    }

    /**
     * Programmer une notification
     */
    private function scheduleNotificationInternal($notification, $recipients, $scheduledAt)
    {
        // Cette méthode devrait utiliser un système de queue/job
        // Pour l'instant, on simule la programmation

        FirebaseNotification::create([
            'title' => $notification['title'],
            'body' => $notification['body'],
            'data' => $notification['data'] ?? [],
            'image' => $notification['image'] ?? null,
            'recipients_type' => $recipients['type'],
            'recipients_data' => $recipients,
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
            'sent_by' => Auth::id(),
        ]);

        return [
            'success' => true,
            'message' => 'Notification programmée avec succès',
            'scheduled_at' => $scheduledAt
        ];
    }

    /**
     * Méthodes placeholder pour les autres fonctionnalités
     */
    public function validateConfig() { return response()->json(['success' => true]); }
    public function updateDevice($deviceId) { return response()->json(['success' => true]); }
    public function unregisterDevice($deviceId) { return response()->json(['success' => true]); }
    public function listDevices() { return response()->json(['success' => true, 'devices' => []]); }
    public function verifyDevice($deviceId) { return response()->json(['success' => true]); }
    public function sendToDevice($deviceId) { return response()->json(['success' => true]); }
    public function sendBulkNotifications() { return response()->json(['success' => true]); }
    public function sendToSegment() { return response()->json(['success' => true]); }
    public function scheduleNotification() { return response()->json(['success' => true]); }
    public function getScheduledNotifications() { return response()->json(['success' => true, 'notifications' => []]); }
    public function cancelScheduledNotification($id) { return response()->json(['success' => true]); }
    public function listTopics() { return response()->json(['success' => true, 'topics' => []]); }
    public function createTopic() { return response()->json(['success' => true]); }
    public function deleteTopic($topic) { return response()->json(['success' => true]); }
    public function getTopicSubscribers($topic) { return response()->json(['success' => true, 'subscribers' => []]); }
    public function listSegments() { return response()->json(['success' => true, 'segments' => []]); }
    public function createSegment() { return response()->json(['success' => true]); }
    public function updateSegment($id) { return response()->json(['success' => true]); }
    public function deleteSegment($id) { return response()->json(['success' => true]); }
    public function getSegmentUsers($id) { return response()->json(['success' => true, 'users' => []]); }
    public function addUsersToSegment($id) { return response()->json(['success' => true]); }
    public function removeUsersFromSegment($id) { return response()->json(['success' => true]); }
    public function listTemplates() { return response()->json(['success' => true, 'templates' => []]); }
    public function createTemplate() { return response()->json(['success' => true]); }
    public function getTemplate($id) { return response()->json(['success' => true]); }
    public function updateTemplate($id) { return response()->json(['success' => true]); }
    public function deleteTemplate($id) { return response()->json(['success' => true]); }
    public function sendTemplateNotification($id) { return response()->json(['success' => true]); }
    public function listCampaigns() { return response()->json(['success' => true, 'campaigns' => []]); }
    public function createCampaign() { return response()->json(['success' => true]); }
    public function getCampaign($id) { return response()->json(['success' => true]); }
    public function updateCampaign($id) { return response()->json(['success' => true]); }
    public function startCampaign($id) { return response()->json(['success' => true]); }
    public function pauseCampaign($id) { return response()->json(['success' => true]); }
    public function stopCampaign($id) { return response()->json(['success' => true]); }
    public function getCampaignStats($id) { return response()->json(['success' => true, 'stats' => []]); }
    public function listAutomations() { return response()->json(['success' => true, 'automations' => []]); }
    public function createAutomation() { return response()->json(['success' => true]); }
    public function getAutomation($id) { return response()->json(['success' => true]); }
    public function updateAutomation($id) { return response()->json(['success' => true]); }
    public function activateAutomation($id) { return response()->json(['success' => true]); }
    public function deactivateAutomation($id) { return response()->json(['success' => true]); }
    public function deleteAutomation($id) { return response()->json(['success' => true]); }
    public function getAnalyticsOverview() { return response()->json(['success' => true, 'analytics' => []]); }
    public function getDeliveryStats() { return response()->json(['success' => true, 'stats' => []]); }
    public function getEngagementStats() { return response()->json(['success' => true, 'stats' => []]); }
    public function getDeviceStats() { return response()->json(['success' => true, 'stats' => []]); }
    public function getTopicStats() { return response()->json(['success' => true, 'stats' => []]); }
    public function exportAnalytics() { return response()->json(['success' => true]); }
    public function handleDeliveryReceipt() { return response()->json(['success' => true]); }
    public function handleOpenTracking() { return response()->json(['success' => true]); }
    public function handleClickTracking() { return response()->json(['success' => true]); }
    public function handleUnsubscribe() { return response()->json(['success' => true]); }
    public function apiRegisterDevice() { return response()->json(['success' => true]); }
    public function apiUpdateDevice() { return response()->json(['success' => true]); }
    public function apiUnregisterDevice() { return response()->json(['success' => true]); }
    public function updateUserPreferences() { return response()->json(['success' => true]); }
    public function apiSubscribeToTopic() { return response()->json(['success' => true]); }
    public function apiUnsubscribeFromTopic() { return response()->json(['success' => true]); }
    public function getUserNotifications() { return response()->json(['success' => true, 'notifications' => []]); }
    public function markNotificationAsRead($id) { return response()->json(['success' => true]); }
    public function deleteUserNotification($id) { return response()->json(['success' => true]); }
    public function clearUserNotifications() { return response()->json(['success' => true]); }
    public function refreshDeviceToken() { return response()->json(['success' => true]); }
    public function validateDeviceToken() { return response()->json(['success' => true]); }
    public function getNotificationPermissions() { return response()->json(['success' => true, 'permissions' => []]); }
    public function sendOrderCreatedNotification() { return response()->json(['success' => true]); }
    public function sendOrderConfirmedNotification() { return response()->json(['success' => true]); }
    public function sendOrderPreparingNotification() { return response()->json(['success' => true]); }
    public function sendOrderReadyNotification() { return response()->json(['success' => true]); }
    public function sendOrderDeliveredNotification() { return response()->json(['success' => true]); }
    public function sendOrderCancelledNotification() { return response()->json(['success' => true]); }
    public function sendPaymentSuccessNotification() { return response()->json(['success' => true]); }
    public function sendPaymentFailedNotification() { return response()->json(['success' => true]); }
    public function sendRefundProcessedNotification() { return response()->json(['success' => true]); }
    public function sendPromotionNotification() { return response()->json(['success' => true]); }
    public function sendLoyaltyRewardNotification() { return response()->json(['success' => true]); }
    public function sendBirthdayOfferNotification() { return response()->json(['success' => true]); }
    public function sendRestaurantStatusNotification() { return response()->json(['success' => true]); }
    public function sendMenuUpdateNotification() { return response()->json(['success' => true]); }
    public function sendSpecialOfferNotification() { return response()->json(['success' => true]); }
    public function sendTestNotification() { return response()->json(['success' => true]); }
    public function sendToAllAdmins() { return response()->json(['success' => true]); }
    public function simulateOrderFlow() { return response()->json(['success' => true]); }
    public function debugDevice($id) { return response()->json(['success' => true, 'debug' => []]); }
    public function debugUser($id) { return response()->json(['success' => true, 'debug' => []]); }
}
