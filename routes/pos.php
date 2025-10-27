<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\POSController;
use App\Http\Controllers\Admin\POSAdminController;

/*
|--------------------------------------------------------------------------
| POS System Routes - Point de Vente
|--------------------------------------------------------------------------
|
| Système de point de vente unifié synchronisant commandes online/offline,
| inventaire temps réel, rapports de vente et gestion multi-terminaux.
|
*/

// Routes principales POS (nécessitent authentification restaurant)
Route::middleware(['auth', 'role:restaurant'])->prefix('pos')->group(function () {

    // Interface principale POS
    Route::get('/', [POSController::class, 'index'])->name('pos.index');
    Route::get('/terminal/{terminal_id?}', [POSController::class, 'terminal'])->name('pos.terminal');

    // Gestion du panier POS
    Route::post('/cart/add', [POSController::class, 'addToCart'])->name('pos.cart.add');
    Route::put('/cart/update/{item_id}', [POSController::class, 'updateCartItem'])->name('pos.cart.update');
    Route::delete('/cart/remove/{item_id}', [POSController::class, 'removeFromCart'])->name('pos.cart.remove');
    Route::delete('/cart/clear', [POSController::class, 'clearCart'])->name('pos.cart.clear');
    Route::get('/cart/summary', [POSController::class, 'getCartSummary'])->name('pos.cart.summary');

    // Gestion des commandes POS
    Route::post('/orders', [POSController::class, 'createOrder'])->name('pos.orders.create');
    Route::get('/orders/current', [POSController::class, 'getCurrentOrders'])->name('pos.orders.current');
    Route::put('/orders/{order_id}/status', [POSController::class, 'updateOrderStatus'])->name('pos.orders.status');
    Route::get('/orders/{order_id}', [POSController::class, 'getOrder'])->name('pos.orders.show');
    Route::post('/orders/{order_id}/print', [POSController::class, 'printOrder'])->name('pos.orders.print');

    // Paiements POS
    Route::post('/payments/process', [POSController::class, 'processPayment'])->name('pos.payments.process');
    Route::post('/payments/split', [POSController::class, 'splitPayment'])->name('pos.payments.split');
    Route::post('/payments/refund', [POSController::class, 'processRefund'])->name('pos.payments.refund');
    Route::get('/payments/methods', [POSController::class, 'getPaymentMethods'])->name('pos.payments.methods');

    // Clients & Fidélité POS
    Route::get('/customers/search', [POSController::class, 'searchCustomers'])->name('pos.customers.search');
    Route::post('/customers', [POSController::class, 'createCustomer'])->name('pos.customers.create');
    Route::get('/customers/{id}/loyalty', [POSController::class, 'getCustomerLoyalty'])->name('pos.customers.loyalty');
    Route::post('/loyalty/apply', [POSController::class, 'applyLoyaltyDiscount'])->name('pos.loyalty.apply');

    // Inventaire temps réel
    Route::get('/inventory/check', [POSController::class, 'checkInventory'])->name('pos.inventory.check');
    Route::put('/inventory/adjust', [POSController::class, 'adjustInventory'])->name('pos.inventory.adjust');
    Route::get('/inventory/low-stock', [POSController::class, 'getLowStockItems'])->name('pos.inventory.low-stock');

    // Rapports POS
    Route::get('/reports/daily', [POSController::class, 'getDailyReport'])->name('pos.reports.daily');
    Route::get('/reports/shift', [POSController::class, 'getShiftReport'])->name('pos.reports.shift');
    Route::post('/reports/export', [POSController::class, 'exportReport'])->name('pos.reports.export');

    // Gestion des sessions de caisse
    Route::post('/cash/open', [POSController::class, 'openCashDrawer'])->name('pos.cash.open');
    Route::post('/cash/close', [POSController::class, 'closeCashDrawer'])->name('pos.cash.close');
    Route::get('/cash/status', [POSController::class, 'getCashStatus'])->name('pos.cash.status');
    Route::post('/cash/count', [POSController::class, 'cashCount'])->name('pos.cash.count');
});

// Routes AJAX pour l'interface temps réel
Route::middleware(['auth', 'role:restaurant'])->prefix('ajax/pos')->group(function () {

    // Recherche produits rapide
    Route::get('/products/search', [POSController::class, 'searchProducts']);
    Route::get('/products/barcode/{barcode}', [POSController::class, 'getProductByBarcode']);
    Route::get('/products/category/{category_id}', [POSController::class, 'getProductsByCategory']);

    // Synchronisation temps réel
    Route::get('/sync/orders', [POSController::class, 'syncOrders']);
    Route::get('/sync/inventory', [POSController::class, 'syncInventory']);
    Route::get('/sync/status', [POSController::class, 'getSyncStatus']);

    // Notifications POS
    Route::get('/notifications', [POSController::class, 'getNotifications']);
    Route::put('/notifications/{id}/read', [POSController::class, 'markNotificationRead']);

    // Calculatrice & outils
    Route::post('/calculate/tax', [POSController::class, 'calculateTax']);
    Route::post('/calculate/discount', [POSController::class, 'calculateDiscount']);
    Route::post('/calculate/tip', [POSController::class, 'calculateTip']);

    // Gestion des tables (intégration TableQR)
    Route::get('/tables/status', [POSController::class, 'getTablesStatus']);
    Route::post('/tables/{table_id}/assign-order', [POSController::class, 'assignOrderToTable']);
    Route::put('/tables/{table_id}/status', [POSController::class, 'updateTableStatus']);
});

// Routes d'administration POS
Route::middleware(['auth', 'role:restaurant'])->prefix('admin/pos')->group(function () {

    // Configuration POS
    Route::get('/settings', [POSAdminController::class, 'settings'])->name('admin.pos.settings');
    Route::put('/settings', [POSAdminController::class, 'updateSettings'])->name('admin.pos.settings.update');

    // Gestion des terminaux
    Route::resource('/terminals', POSAdminController::class . '@terminals', ['as' => 'admin.pos']);
    Route::put('/terminals/{id}/toggle', [POSAdminController::class, 'toggleTerminal'])->name('admin.pos.terminals.toggle');
    Route::post('/terminals/{id}/sync', [POSAdminController::class, 'syncTerminal'])->name('admin.pos.terminals.sync');

    // Gestion des utilisateurs POS
    Route::get('/users', [POSAdminController::class, 'users'])->name('admin.pos.users');
    Route::post('/users', [POSAdminController::class, 'createUser'])->name('admin.pos.users.create');
    Route::put('/users/{id}', [POSAdminController::class, 'updateUser'])->name('admin.pos.users.update');
    Route::put('/users/{id}/permissions', [POSAdminController::class, 'updateUserPermissions'])
        ->name('admin.pos.users.permissions');

    // Historique des sessions
    Route::get('/sessions', [POSAdminController::class, 'sessions'])->name('admin.pos.sessions');
    Route::get('/sessions/{id}', [POSAdminController::class, 'showSession'])->name('admin.pos.sessions.show');
    Route::post('/sessions/{id}/reconcile', [POSAdminController::class, 'reconcileSession'])
        ->name('admin.pos.sessions.reconcile');

    // Analytiques POS
    Route::get('/analytics', [POSAdminController::class, 'analytics'])->name('admin.pos.analytics');
    Route::get('/analytics/sales', [POSAdminController::class, 'salesAnalytics'])->name('admin.pos.analytics.sales');
    Route::get('/analytics/performance', [POSAdminController::class, 'performanceAnalytics'])
        ->name('admin.pos.analytics.performance');

    // Gestion des périphériques
    Route::get('/devices', [POSAdminController::class, 'devices'])->name('admin.pos.devices');
    Route::post('/devices/printer/test', [POSAdminController::class, 'testPrinter'])->name('admin.pos.devices.printer.test');
    Route::post('/devices/scanner/test', [POSAdminController::class, 'testScanner'])->name('admin.pos.devices.scanner.test');
    Route::put('/devices/{type}/configure', [POSAdminController::class, 'configureDevice'])
        ->name('admin.pos.devices.configure');

    // Sauvegarde et restauration
    Route::post('/backup/create', [POSAdminController::class, 'createBackup'])->name('admin.pos.backup.create');
    Route::get('/backup/download/{backup_id}', [POSAdminController::class, 'downloadBackup'])
        ->name('admin.pos.backup.download');
    Route::post('/backup/restore', [POSAdminController::class, 'restoreBackup'])->name('admin.pos.backup.restore');
});

// API POS pour applications mobiles et synchronisation
Route::prefix('api/pos')->group(function () {

    // Authentification API POS
    Route::post('/auth/login', [POSController::class, 'apiLogin']);
    Route::middleware('auth:api')->group(function () {

        // Synchronisation des données
        Route::get('/sync/menu', [POSController::class, 'syncMenu']);
        Route::get('/sync/orders/{timestamp?}', [POSController::class, 'syncOrdersAPI']);
        Route::post('/sync/orders/push', [POSController::class, 'pushOrdersAPI']);

        // Interface POS mobile
        Route::post('/orders/mobile', [POSController::class, 'createMobileOrder']);
        Route::get('/products/mobile', [POSController::class, 'getMobileProducts']);
        Route::post('/payments/mobile', [POSController::class, 'processMobilePayment']);

        // Mode hors ligne
        Route::get('/offline/data', [POSController::class, 'getOfflineData']);
        Route::post('/offline/sync', [POSController::class, 'syncOfflineData']);
        Route::get('/offline/status', [POSController::class, 'getOfflineStatus']);
    });
});

// Webhooks pour intégrations externes
Route::prefix('webhooks/pos')->group(function () {

    // Synchronisation commandes online vers POS
    Route::post('/order-created', [POSController::class, 'handleOnlineOrderCreated']);
    Route::post('/order-updated', [POSController::class, 'handleOnlineOrderUpdated']);

    // Notifications de paiement
    Route::post('/payment-confirmed', [POSController::class, 'handlePaymentConfirmed']);
    Route::post('/payment-failed', [POSController::class, 'handlePaymentFailed']);

    // Mise à jour inventaire
    Route::post('/inventory-updated', [POSController::class, 'handleInventoryUpdated']);

    // Intégration comptabilité
    Route::post('/accounting-sync', [POSController::class, 'syncWithAccounting']);
});

// Routes utilitaires POS
Route::prefix('pos-utils')->group(function () {

    // Vérification connectivité
    Route::get('/health-check', [POSController::class, 'healthCheck']);
    Route::get('/system-status', [POSController::class, 'getSystemStatus']);

    // Outils de diagnostic
    Route::middleware(['auth', 'role:restaurant'])->group(function () {
        Route::get('/diagnostics', [POSController::class, 'runDiagnostics']);
        Route::post('/diagnostics/fix', [POSController::class, 'fixIssues']);
        Route::get('/logs', [POSController::class, 'getLogs']);
    });

    // Conversion et calculs
    Route::post('/convert/currency', [POSController::class, 'convertCurrency']);
    Route::post('/calculate/change', [POSController::class, 'calculateChange']);
    Route::get('/tax-rates', [POSController::class, 'getTaxRates']);
});
