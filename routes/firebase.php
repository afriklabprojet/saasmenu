<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseController;

/*
|--------------------------------------------------------------------------
| Firebase Push Notifications Routes
|--------------------------------------------------------------------------
|
| Routes pour les notifications push temps réel via Firebase Cloud Messaging
| Support multi-platform (Android, iOS, Web) avec ciblage avancé
|
*/

Route::middleware(['web'])->prefix('firebase')->name('firebase.')->group(function () {

    // Configuration Firebase (Admin)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/settings', [FirebaseController::class, 'getSettings'])->name('settings');
        Route::post('/settings', [FirebaseController::class, 'updateSettings'])->name('settings.update');
        Route::post('/test-connection', [FirebaseController::class, 'testConnection'])->name('test');
        Route::post('/validate-config', [FirebaseController::class, 'validateConfig'])->name('validate');
    });

    // Gestion des tokens d'appareils
    Route::prefix('devices')->name('devices.')->group(function () {
        Route::post('/register', [FirebaseController::class, 'registerDevice'])->name('register');
        Route::put('/update/{deviceId}', [FirebaseController::class, 'updateDevice'])->name('update');
        Route::delete('/unregister/{deviceId}', [FirebaseController::class, 'unregisterDevice'])->name('unregister');
        Route::get('/list', [FirebaseController::class, 'listDevices'])->name('list');
        Route::post('/verify/{deviceId}', [FirebaseController::class, 'verifyDevice'])->name('verify');
    });

    // Envoi de notifications push
    Route::prefix('notifications')->name('notifications.')->group(function () {
        // Notifications individuelles
        Route::post('/send', [FirebaseController::class, 'sendNotification'])->name('send');
        Route::post('/send-to-user/{userId}', [FirebaseController::class, 'sendToUser'])->name('send.user');
        Route::post('/send-to-device/{deviceId}', [FirebaseController::class, 'sendToDevice'])->name('send.device');

        // Notifications groupées
        Route::post('/send-bulk', [FirebaseController::class, 'sendBulkNotifications'])->name('send.bulk');
        Route::post('/send-to-topic/{topic}', [FirebaseController::class, 'sendToTopic'])->name('send.topic');
        Route::post('/send-to-segment', [FirebaseController::class, 'sendToSegment'])->name('send.segment');

        // Notifications programmées
        Route::post('/schedule', [FirebaseController::class, 'scheduleNotification'])->name('schedule');
        Route::get('/scheduled', [FirebaseController::class, 'getScheduledNotifications'])->name('scheduled.list');
        Route::delete('/cancel/{notificationId}', [FirebaseController::class, 'cancelScheduledNotification'])->name('cancel');
    });

    // Gestion des topics/sujets
    Route::prefix('topics')->name('topics.')->group(function () {
        Route::get('/', [FirebaseController::class, 'listTopics'])->name('list');
        Route::post('/create', [FirebaseController::class, 'createTopic'])->name('create');
        Route::delete('/{topic}', [FirebaseController::class, 'deleteTopic'])->name('delete');
        Route::post('/{topic}/subscribe', [FirebaseController::class, 'subscribeToTopic'])->name('subscribe');
        Route::post('/{topic}/unsubscribe', [FirebaseController::class, 'unsubscribeFromTopic'])->name('unsubscribe');
        Route::get('/{topic}/subscribers', [FirebaseController::class, 'getTopicSubscribers'])->name('subscribers');
    });

    // Segments d'utilisateurs
    Route::prefix('segments')->name('segments.')->group(function () {
        Route::get('/', [FirebaseController::class, 'listSegments'])->name('list');
        Route::post('/create', [FirebaseController::class, 'createSegment'])->name('create');
        Route::put('/{segmentId}', [FirebaseController::class, 'updateSegment'])->name('update');
        Route::delete('/{segmentId}', [FirebaseController::class, 'deleteSegment'])->name('delete');
        Route::get('/{segmentId}/users', [FirebaseController::class, 'getSegmentUsers'])->name('users');
        Route::post('/{segmentId}/add-users', [FirebaseController::class, 'addUsersToSegment'])->name('add.users');
        Route::post('/{segmentId}/remove-users', [FirebaseController::class, 'removeUsersFromSegment'])->name('remove.users');
    });

    // Templates de notifications
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [FirebaseController::class, 'listTemplates'])->name('list');
        Route::post('/create', [FirebaseController::class, 'createTemplate'])->name('create');
        Route::get('/{templateId}', [FirebaseController::class, 'getTemplate'])->name('show');
        Route::put('/{templateId}', [FirebaseController::class, 'updateTemplate'])->name('update');
        Route::delete('/{templateId}', [FirebaseController::class, 'deleteTemplate'])->name('delete');
        Route::post('/{templateId}/send', [FirebaseController::class, 'sendTemplateNotification'])->name('send');
    });

    // Campagnes de notifications
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', [FirebaseController::class, 'listCampaigns'])->name('list');
        Route::post('/create', [FirebaseController::class, 'createCampaign'])->name('create');
        Route::get('/{campaignId}', [FirebaseController::class, 'getCampaign'])->name('show');
        Route::put('/{campaignId}', [FirebaseController::class, 'updateCampaign'])->name('update');
        Route::post('/{campaignId}/start', [FirebaseController::class, 'startCampaign'])->name('start');
        Route::post('/{campaignId}/pause', [FirebaseController::class, 'pauseCampaign'])->name('pause');
        Route::post('/{campaignId}/stop', [FirebaseController::class, 'stopCampaign'])->name('stop');
        Route::get('/{campaignId}/stats', [FirebaseController::class, 'getCampaignStats'])->name('stats');
    });

    // Automatisations de notifications
    Route::prefix('automations')->name('automations.')->group(function () {
        Route::get('/', [FirebaseController::class, 'listAutomations'])->name('list');
        Route::post('/create', [FirebaseController::class, 'createAutomation'])->name('create');
        Route::get('/{automationId}', [FirebaseController::class, 'getAutomation'])->name('show');
        Route::put('/{automationId}', [FirebaseController::class, 'updateAutomation'])->name('update');
        Route::post('/{automationId}/activate', [FirebaseController::class, 'activateAutomation'])->name('activate');
        Route::post('/{automationId}/deactivate', [FirebaseController::class, 'deactivateAutomation'])->name('deactivate');
        Route::delete('/{automationId}', [FirebaseController::class, 'deleteAutomation'])->name('delete');
    });

    // Rapports et analytics
    Route::prefix('analytics')->middleware(['auth:sanctum'])->name('analytics.')->group(function () {
        Route::get('/overview', [FirebaseController::class, 'getAnalyticsOverview'])->name('overview');
        Route::get('/delivery-stats', [FirebaseController::class, 'getDeliveryStats'])->name('delivery');
        Route::get('/engagement-stats', [FirebaseController::class, 'getEngagementStats'])->name('engagement');
        Route::get('/device-stats', [FirebaseController::class, 'getDeviceStats'])->name('devices');
        Route::get('/topic-stats', [FirebaseController::class, 'getTopicStats'])->name('topics');
        Route::post('/export', [FirebaseController::class, 'exportAnalytics'])->name('export');
    });

    // Webhooks et callbacks
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::post('/delivery-receipt', [FirebaseController::class, 'handleDeliveryReceipt'])->name('delivery');
        Route::post('/open-tracking', [FirebaseController::class, 'handleOpenTracking'])->name('open');
        Route::post('/click-tracking', [FirebaseController::class, 'handleClickTracking'])->name('click');
        Route::post('/unsubscribe', [FirebaseController::class, 'handleUnsubscribe'])->name('unsubscribe');
    });
});

/*
|--------------------------------------------------------------------------
| API Routes pour Firebase (Mobile/Web)
|--------------------------------------------------------------------------
|
| Routes API pour l'intégration mobile et web des notifications
|
*/

Route::prefix('api/firebase')->middleware(['api'])->name('api.firebase.')->group(function () {

    // Enregistrement d'appareils
    Route::post('/register-device', [FirebaseController::class, 'apiRegisterDevice'])->name('register.device');
    Route::put('/update-device', [FirebaseController::class, 'apiUpdateDevice'])->name('update.device');
    Route::delete('/unregister-device', [FirebaseController::class, 'apiUnregisterDevice'])->name('unregister.device');

    // Gestion des préférences utilisateur
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/preferences', [FirebaseController::class, 'getUserPreferences'])->name('preferences');
        Route::post('/preferences', [FirebaseController::class, 'updateUserPreferences'])->name('preferences.update');
        Route::post('/topics/subscribe', [FirebaseController::class, 'apiSubscribeToTopic'])->name('topics.subscribe');
        Route::post('/topics/unsubscribe', [FirebaseController::class, 'apiUnsubscribeFromTopic'])->name('topics.unsubscribe');
    });

    // Historique des notifications
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/notifications', [FirebaseController::class, 'getUserNotifications'])->name('notifications.list');
        Route::put('/notifications/{id}/read', [FirebaseController::class, 'markNotificationAsRead'])->name('notifications.read');
        Route::delete('/notifications/{id}', [FirebaseController::class, 'deleteUserNotification'])->name('notifications.delete');
        Route::post('/notifications/clear', [FirebaseController::class, 'clearUserNotifications'])->name('notifications.clear');
    });

    // Tokens et permissions
    Route::post('/refresh-token', [FirebaseController::class, 'refreshDeviceToken'])->name('token.refresh');
    Route::post('/validate-token', [FirebaseController::class, 'validateDeviceToken'])->name('token.validate');
    Route::get('/permissions', [FirebaseController::class, 'getNotificationPermissions'])->name('permissions');
});

/*
|--------------------------------------------------------------------------
| Notifications Système Automatiques
|--------------------------------------------------------------------------
|
| Routes pour les notifications automatiques du système
|
*/

Route::prefix('firebase/system')->name('firebase.system.')->group(function () {

    // Notifications de commandes
    Route::post('/order-created', [FirebaseController::class, 'sendOrderCreatedNotification'])->name('order.created');
    Route::post('/order-confirmed', [FirebaseController::class, 'sendOrderConfirmedNotification'])->name('order.confirmed');
    Route::post('/order-preparing', [FirebaseController::class, 'sendOrderPreparingNotification'])->name('order.preparing');
    Route::post('/order-ready', [FirebaseController::class, 'sendOrderReadyNotification'])->name('order.ready');
    Route::post('/order-delivered', [FirebaseController::class, 'sendOrderDeliveredNotification'])->name('order.delivered');
    Route::post('/order-cancelled', [FirebaseController::class, 'sendOrderCancelledNotification'])->name('order.cancelled');

    // Notifications de paiement
    Route::post('/payment-success', [FirebaseController::class, 'sendPaymentSuccessNotification'])->name('payment.success');
    Route::post('/payment-failed', [FirebaseController::class, 'sendPaymentFailedNotification'])->name('payment.failed');
    Route::post('/refund-processed', [FirebaseController::class, 'sendRefundProcessedNotification'])->name('refund.processed');

    // Notifications promotionnelles
    Route::post('/promotion-available', [FirebaseController::class, 'sendPromotionNotification'])->name('promotion.available');
    Route::post('/loyalty-reward', [FirebaseController::class, 'sendLoyaltyRewardNotification'])->name('loyalty.reward');
    Route::post('/birthday-offer', [FirebaseController::class, 'sendBirthdayOfferNotification'])->name('birthday.offer');

    // Notifications restaurant
    Route::post('/restaurant-status', [FirebaseController::class, 'sendRestaurantStatusNotification'])->name('restaurant.status');
    Route::post('/menu-update', [FirebaseController::class, 'sendMenuUpdateNotification'])->name('menu.update');
    Route::post('/special-offer', [FirebaseController::class, 'sendSpecialOfferNotification'])->name('special.offer');
});

/*
|--------------------------------------------------------------------------
| Routes de test et développement
|--------------------------------------------------------------------------
|
| Routes pour tester les notifications en développement
|
*/

Route::prefix('firebase/test')->middleware(['auth:sanctum'])->name('firebase.test.')->group(function () {
    Route::post('/send-test', [FirebaseController::class, 'sendTestNotification'])->name('send');
    Route::post('/send-to-all-admins', [FirebaseController::class, 'sendToAllAdmins'])->name('admins');
    Route::post('/simulate-order-flow', [FirebaseController::class, 'simulateOrderFlow'])->name('order.flow');
    Route::get('/debug-device/{deviceId}', [FirebaseController::class, 'debugDevice'])->name('debug.device');
    Route::get('/debug-user/{userId}', [FirebaseController::class, 'debugUser'])->name('debug.user');
});
