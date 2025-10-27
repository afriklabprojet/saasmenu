<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TableQRController;
use App\Http\Controllers\Admin\TableQRAdminController;

/*
|--------------------------------------------------------------------------
| TableQR Routes - Commandes par QR Code
|--------------------------------------------------------------------------
|
| Système de commande directe par QR code pour les tables de restaurant.
| Les clients scannent le QR code de leur table et commandent directement.
|
*/

// Routes publiques pour les clients (scan QR)
Route::prefix('table')->group(function () {

    // Affichage du menu après scan QR
    Route::get('/{restaurant_slug}/{table_code}', [TableQRController::class, 'showMenu'])
        ->name('table.menu');

    // Information sur la table
    Route::get('/{restaurant_slug}/{table_code}/info', [TableQRController::class, 'getTableInfo'])
        ->name('table.info');

    // Passer une commande depuis la table
    Route::post('/{restaurant_slug}/{table_code}/order', [TableQRController::class, 'placeOrder'])
        ->name('table.order');

    // Suivi de commande en temps réel
    Route::get('/{restaurant_slug}/{table_code}/order/{order_id}/status', [TableQRController::class, 'getOrderStatus'])
        ->name('table.order.status');

    // Appeler le serveur
    Route::post('/{restaurant_slug}/{table_code}/call-waiter', [TableQRController::class, 'callWaiter'])
        ->name('table.call.waiter');

    // Demander l'addition
    Route::post('/{restaurant_slug}/{table_code}/request-bill', [TableQRController::class, 'requestBill'])
        ->name('table.request.bill');

    // Évaluation service table
    Route::post('/{restaurant_slug}/{table_code}/rate', [TableQRController::class, 'rateService'])
        ->name('table.rate');
});

// Routes pour les restaurants (authentifiés)
Route::middleware(['auth', 'role:restaurant'])->prefix('admin/tables')->group(function () {

    // Gestion des tables
    Route::get('/', [TableQRAdminController::class, 'index'])->name('admin.tables.index');
    Route::post('/', [TableQRAdminController::class, 'store'])->name('admin.tables.store');
    Route::get('/{id}', [TableQRAdminController::class, 'show'])->name('admin.tables.show');
    Route::put('/{id}', [TableQRAdminController::class, 'update'])->name('admin.tables.update');
    Route::delete('/{id}', [TableQRAdminController::class, 'destroy'])->name('admin.tables.destroy');

    // Génération et téléchargement des QR codes
    Route::get('/{id}/qr', [TableQRAdminController::class, 'generateQR'])->name('admin.tables.qr');
    Route::get('/{id}/qr/download', [TableQRAdminController::class, 'downloadQR'])->name('admin.tables.qr.download');
    Route::post('/bulk-generate', [TableQRAdminController::class, 'bulkGenerateQR'])->name('admin.tables.bulk.qr');

    // ✨ NOUVELLES ROUTES QR AMÉLIORÉES
    // Téléchargement en masse
    Route::get('/qr/download-all-pdf', [TableQRAdminController::class, 'downloadAllQRPDF'])->name('admin.tables.qr.download.pdf');
    Route::get('/qr/download-all-zip', [TableQRAdminController::class, 'downloadAllQRZip'])->name('admin.tables.qr.download.zip');

    // Personnalisation QR Code
    Route::post('/{id}/qr/customize', [TableQRAdminController::class, 'customizeQR'])->name('admin.tables.qr.customize');
    Route::get('/{id}/qr/custom-download', [TableQRAdminController::class, 'downloadCustomQR'])->name('admin.tables.qr.custom.download');
    Route::get('/{id}/qr/preview', [TableQRAdminController::class, 'previewCustomQR'])->name('admin.tables.qr.preview');

    // Statistiques de scan
    Route::get('/{id}/scan-stats', [TableQRAdminController::class, 'scanStats'])->name('admin.tables.scan.stats');
    Route::get('/scan-stats/restaurant', [TableQRAdminController::class, 'restaurantScanStats'])->name('admin.tables.scan.restaurant.stats');

    // Gestion des commandes par table
    Route::get('/{id}/orders', [TableQRAdminController::class, 'getTableOrders'])->name('admin.tables.orders');
    Route::get('/{id}/orders/active', [TableQRAdminController::class, 'getActiveOrders'])->name('admin.tables.orders.active');

    // Statuts des tables en temps réel
    Route::get('/status/live', [TableQRAdminController::class, 'getLiveStatus'])->name('admin.tables.status.live');
    Route::put('/{id}/status', [TableQRAdminController::class, 'updateTableStatus'])->name('admin.tables.status.update');

    // Notifications de table (serveur appelé, addition demandée)
    Route::get('/notifications', [TableQRAdminController::class, 'getNotifications'])->name('admin.tables.notifications');
    Route::put('/notifications/{id}/resolve', [TableQRAdminController::class, 'resolveNotification'])->name('admin.tables.notifications.resolve');

    // Statistiques des tables
    Route::get('/analytics', [TableQRAdminController::class, 'getAnalytics'])->name('admin.tables.analytics');
    Route::get('/analytics/revenue', [TableQRAdminController::class, 'getRevenueByTable'])->name('admin.tables.analytics.revenue');
});

// Routes AJAX pour le temps réel
Route::middleware(['auth', 'role:restaurant'])->prefix('ajax/tables')->group(function () {

    // Notifications temps réel pour le restaurant
    Route::get('/live-notifications', [TableQRAdminController::class, 'getLiveNotifications']);
    Route::post('/mark-notification-seen/{id}', [TableQRAdminController::class, 'markNotificationSeen']);

    // Statut des commandes en temps réel
    Route::get('/{table_id}/live-orders', [TableQRAdminController::class, 'getLiveOrders']);
    Route::put('/orders/{order_id}/update-status', [TableQRAdminController::class, 'updateOrderStatus']);
});

// Routes publiques pour prévisualisation (avant scan)
Route::get('/qr-preview/{restaurant_slug}', [TableQRController::class, 'showPreview'])
    ->name('table.preview');

// API Routes pour applications mobiles
Route::prefix('api/tableqr')->group(function () {

    // Vérification validité QR code
    Route::get('/validate/{restaurant_slug}/{table_code}', [TableQRController::class, 'validateQR']);

    // Menu format JSON pour apps mobiles
    Route::get('/{restaurant_slug}/{table_code}/menu/json', [TableQRController::class, 'getMenuJson']);

    // Commande via API mobile
    Route::post('/{restaurant_slug}/{table_code}/order/api', [TableQRController::class, 'placeOrderAPI']);

    // Websocket endpoints pour notifications temps réel
    Route::get('/{restaurant_slug}/notifications/stream', [TableQRController::class, 'getNotificationStream']);
});

// Routes utilitaires
Route::prefix('tableqr-utils')->group(function () {

    // Génération d'un QR code de test
    Route::get('/test-qr/{restaurant_slug}', [TableQRController::class, 'generateTestQR'])
        ->middleware('auth');

    // Vérification de la connectivité pour les tables
    Route::get('/ping/{restaurant_slug}/{table_code}', [TableQRController::class, 'ping']);

    // Statistiques publiques (si activées par le restaurant)
    Route::get('/{restaurant_slug}/stats', [TableQRController::class, 'getPublicStats']);
});
