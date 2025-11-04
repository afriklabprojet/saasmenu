<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RestaurantController;
use App\Http\Controllers\API\MenuController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\Api\ApiDocumentationController;
use App\Http\Controllers\Api\PosApiController;
use App\Http\Controllers\Api\LoyaltyApiController;
use App\Http\Controllers\Api\TableQrApiController;
use App\Http\Controllers\Performance\PerformanceTestController;

/*
|--------------------------------------------------------------------------
| API Routes - E-menu Mobile & Integrations
|--------------------------------------------------------------------------
|
| Routes API pour l'application mobile E-menu et les intégrations tierces.
| Toutes les routes sont protégées par authentification sauf les routes publiques.
|
*/

// Routes Performance Testing (pour développement)
Route::prefix('performance')->group(function () {
    Route::post('/web-vitals', [PerformanceTestController::class, 'recordWebVitals']);
    Route::get('/asset-speed', [PerformanceTestController::class, 'testAssetSpeed']);
    Route::get('/benchmark', [PerformanceTestController::class, 'benchmarkWebVitals']);
    Route::get('/images', [PerformanceTestController::class, 'testImageOptimization']);
    Route::get('/report', [PerformanceTestController::class, 'performanceReport']);
});

// Route de test API
Route::get('/test', function () {
    return response()->json([
        'message' => 'API E-menu fonctionnelle',
        'version' => '1.0.0',
        'timestamp' => now(),
        'status' => 'active'
    ]);
});

// Routes d'authentification (publiques)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Routes sociales
    Route::post('/google', [AuthController::class, 'googleLogin']);
    Route::post('/facebook', [AuthController::class, 'facebookLogin']);
});

// Routes publiques restaurants
Route::prefix('restaurants')->group(function () {
    Route::get('/', [RestaurantController::class, 'index']);
    Route::get('/search', [RestaurantController::class, 'search']);
    Route::get('/{slug}', [RestaurantController::class, 'show']);
    Route::get('/{slug}/menu', [MenuController::class, 'getByRestaurant']);
});

// Routes protégées (nécessitent authentification)
Route::middleware(['auth:sanctum'])->group(function () {

    // Profil utilisateur
    Route::prefix('user')->group(function () {
        Route::get('/profile', [CustomerController::class, 'profile']);
        Route::put('/profile', [CustomerController::class, 'updateProfile']);
        Route::post('/avatar', [CustomerController::class, 'uploadAvatar']);
        Route::delete('/account', [CustomerController::class, 'deleteAccount']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    // Gestion des commandes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::put('/{id}/cancel', [OrderController::class, 'cancel']);
        Route::post('/{id}/review', [OrderController::class, 'addReview']);
        Route::get('/{id}/track', [OrderController::class, 'track']);
    });

    // Favoris
    Route::prefix('favorites')->group(function () {
        Route::get('/', [CustomerController::class, 'favorites']);
        Route::post('/restaurants/{id}', [CustomerController::class, 'addRestaurantFavorite']);
        Route::delete('/restaurants/{id}', [CustomerController::class, 'removeRestaurantFavorite']);
        Route::post('/items/{id}', [CustomerController::class, 'addItemFavorite']);
        Route::delete('/items/{id}', [CustomerController::class, 'removeItemFavorite']);
    });

    // Adresses
    Route::prefix('addresses')->group(function () {
        Route::get('/', [CustomerController::class, 'addresses']);
        Route::post('/', [CustomerController::class, 'addAddress']);
        Route::put('/{id}', [CustomerController::class, 'updateAddress']);
        Route::delete('/{id}', [CustomerController::class, 'deleteAddress']);
        Route::post('/{id}/default', [CustomerController::class, 'setDefaultAddress']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'delete']);

        // Push notifications
        Route::post('/subscribe', [NotificationController::class, 'subscribePush']);
        Route::delete('/unsubscribe', [NotificationController::class, 'unsubscribePush']);
    });

    // Panier
    Route::prefix('cart')->group(function () {
        Route::get('/', [OrderController::class, 'getCart']);
        Route::post('/add', [OrderController::class, 'addToCart']);
        Route::put('/update/{id}', [OrderController::class, 'updateCartItem']);
        Route::delete('/remove/{id}', [OrderController::class, 'removeFromCart']);
        Route::delete('/clear', [OrderController::class, 'clearCart']);
        Route::post('/apply-coupon', [OrderController::class, 'applyCoupon']);
    });

    // Programme de fidélité
    Route::prefix('loyalty')->group(function () {
        Route::get('/points', [CustomerController::class, 'getLoyaltyPoints']);
        Route::get('/history', [CustomerController::class, 'getLoyaltyHistory']);
        Route::post('/redeem', [CustomerController::class, 'redeemPoints']);
    });
});

// Routes admin (pour les restaurants)
Route::middleware(['auth:sanctum', 'role:restaurant'])->prefix('admin')->group(function () {

    // Dashboard
    Route::get('/dashboard', [RestaurantController::class, 'dashboard']);
    Route::get('/analytics', [RestaurantController::class, 'analytics']);

    // Gestion des commandes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'adminIndex']);
        Route::put('/{id}/status', [OrderController::class, 'updateStatus']);
        Route::put('/{id}/accept', [OrderController::class, 'acceptOrder']);
        Route::put('/{id}/reject', [OrderController::class, 'rejectOrder']);
        Route::get('/statistics', [OrderController::class, 'getStatistics']);
    });

    // Gestion du menu
    Route::prefix('menu')->group(function () {
        Route::get('/', [MenuController::class, 'adminIndex']);
        Route::post('/items', [MenuController::class, 'store']);
        Route::put('/items/{id}', [MenuController::class, 'update']);
        Route::delete('/items/{id}', [MenuController::class, 'destroy']);
        Route::put('/items/{id}/availability', [MenuController::class, 'updateAvailability']);
    });

    // Gestion restaurant
    Route::prefix('restaurant')->group(function () {
        Route::get('/profile', [RestaurantController::class, 'getProfile']);
        Route::put('/profile', [RestaurantController::class, 'updateProfile']);
        Route::put('/hours', [RestaurantController::class, 'updateHours']);
        Route::put('/delivery-zones', [RestaurantController::class, 'updateDeliveryZones']);
    });
});

// Routes d'intégration (webhooks, etc.)
Route::prefix('webhooks')->group(function () {
    Route::post('/cinetpay', [OrderController::class, 'cinetpayWebhook']);
    Route::post('/paypal', [OrderController::class, 'paypalWebhook']);
    Route::post('/firebase', [NotificationController::class, 'firebaseWebhook']);
});

// Routes de développement (à supprimer en production)
Route::middleware(['auth:sanctum'])->prefix('dev')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/test-notification', function () {
        return response()->json([
            'message' => 'Test notification envoyée',
            'timestamp' => now()
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| RestroSaaS Addons API Routes
|--------------------------------------------------------------------------
|
| API endpoints for the 8 priority addons: POS System, Loyalty Program,
| Table QR, Import/Export, Notifications, PayPal, Social Login, Firebase
|
*/

// Health check and documentation
Route::get('/health', [ApiDocumentationController::class, 'health']);

// POS System API
Route::middleware(['auth:sanctum', 'addon.permission:pos_api'])->prefix('pos')->group(function () {
    Route::get('/terminals', [PosApiController::class, 'getTerminals']);
    Route::post('/sessions', [PosApiController::class, 'createSession']);
    Route::get('/sessions/{sessionId}', [PosApiController::class, 'getSession']);
    Route::post('/sessions/{sessionId}/cart', [PosApiController::class, 'addCartItem']);
    Route::post('/sessions/{sessionId}/checkout', [PosApiController::class, 'checkout']);
});

// Loyalty Program API
Route::middleware(['auth:sanctum', 'addon.permission:loyalty_api'])->prefix('loyalty')->group(function () {
    Route::get('/programs', [LoyaltyApiController::class, 'getPrograms']);
    Route::post('/programs/{programId}/members', [LoyaltyApiController::class, 'enrollMember']);
    Route::get('/members/{memberId}', [LoyaltyApiController::class, 'getMember']);
    Route::post('/members/{memberId}/transactions', [LoyaltyApiController::class, 'createTransaction']);
    Route::get('/members/{memberId}/transactions', [LoyaltyApiController::class, 'getTransactions']);
});

// Table QR Code API
Route::middleware(['auth:sanctum', 'addon.permission:tableqr_api'])->prefix('tableqr')->group(function () {
    Route::get('/tables', [TableQrApiController::class, 'getTables']);
    Route::get('/tables/{tableId}', [TableQrApiController::class, 'getTable']);
    Route::post('/tables', [TableQrApiController::class, 'createTable']);
    Route::post('/scan', [TableQrApiController::class, 'scanQrCode']);
    Route::get('/analytics', [TableQrApiController::class, 'getAnalytics']);
});

/*
|--------------------------------------------------------------------------
| WhatsApp API Routes
|--------------------------------------------------------------------------
*/

// Webhook WhatsApp (publique - utilisé par Meta)
Route::prefix('whatsapp')->group(function () {
    Route::get('/webhook', [\App\Http\Controllers\WhatsAppController::class, 'verifyWebhook']);
    Route::post('/webhook', [\App\Http\Controllers\WhatsAppController::class, 'handleWebhook']);
});

// WhatsApp Admin Routes (protégées)
Route::middleware(['auth:sanctum'])->prefix('whatsapp')->group(function () {
    Route::post('/test-message', [\App\Http\Controllers\WhatsAppController::class, 'testMessage']);
    Route::post('/test-connection', [\App\Http\Controllers\WhatsAppController::class, 'testConnection']);
    Route::get('/statistics', [\App\Http\Controllers\WhatsAppController::class, 'getStatistics']);
    Route::get('/messages/history', [\App\Http\Controllers\WhatsAppController::class, 'getMessageHistory']);
    Route::post('/messages/{messageId}/retry', [\App\Http\Controllers\WhatsAppController::class, 'retryMessage']);
});

// Routes pour démonstration système deferred équivalent Laravel 12
Route::prefix('v1')->group(function() {
    Route::post('/orders/optimized', [\App\Http\Controllers\Api\OptimizedOrderController::class, 'store'])->name('orders.optimized');
    Route::get('/queue/stats', [\App\Http\Controllers\Api\OptimizedOrderController::class, 'queueStats'])->name('queue.stats');
});

// Routes Analytics & Business Intelligence
Route::prefix('analytics')->group(function() {
    Route::get('/dashboard/{vendorId}', [\App\Http\Controllers\Api\AnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
    Route::get('/revenue/{vendorId}', [\App\Http\Controllers\Api\AnalyticsController::class, 'revenue'])->name('analytics.revenue');
    Route::get('/products/{vendorId}', [\App\Http\Controllers\Api\AnalyticsController::class, 'products'])->name('analytics.products');
    Route::get('/customers/{vendorId}', [\App\Http\Controllers\Api\AnalyticsController::class, 'customers'])->name('analytics.customers');
    Route::get('/insights/{vendorId}', [\App\Http\Controllers\Api\AnalyticsController::class, 'insights'])->name('analytics.insights');
    Route::get('/realtime/{vendorId}', [\App\Http\Controllers\Api\AnalyticsController::class, 'realtime'])->name('analytics.realtime');
    Route::get('/export/{vendorId}', [\App\Http\Controllers\Api\AnalyticsController::class, 'export'])->name('analytics.export');
    Route::get('/compare/{vendorId}', [\App\Http\Controllers\Api\AnalyticsController::class, 'compare'])->name('analytics.compare');
});

// Routes Dashboard Widgets
Route::middleware(['auth:sanctum'])->prefix('dashboard-widgets')->group(function() {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardWidgetController::class, 'getWidgets'])->name('widgets.all');
    Route::get('/by-type', [\App\Http\Controllers\Admin\DashboardWidgetController::class, 'getWidgetsByType'])->name('widgets.by-type');
    Route::get('/config', [\App\Http\Controllers\Admin\DashboardWidgetController::class, 'getDashboardConfig'])->name('widgets.config');
    Route::post('/refresh', [\App\Http\Controllers\Admin\DashboardWidgetController::class, 'refreshWidgets'])->name('widgets.refresh');
    Route::get('/realtime-metrics', [\App\Http\Controllers\Admin\DashboardWidgetController::class, 'getRealTimeMetrics'])->name('widgets.realtime');
    Route::get('/export', [\App\Http\Controllers\Admin\DashboardWidgetController::class, 'exportDashboard'])->name('widgets.export');
    Route::get('/performance-history', [\App\Http\Controllers\Admin\DashboardWidgetController::class, 'getPerformanceHistory'])->name('widgets.history');
});
