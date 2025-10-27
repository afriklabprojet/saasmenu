<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayPalController;

/*
|--------------------------------------------------------------------------
| PayPal Payment Gateway Routes
|--------------------------------------------------------------------------
|
| Routes pour l'intégration complète PayPal avec paiements Express,
| abonnements, remboursements et webhooks de confirmation
|
*/

Route::middleware(['web'])->prefix('paypal')->name('paypal.')->group(function () {

    // Configuration PayPal (Admin)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/settings', [PayPalController::class, 'getSettings'])->name('settings');
        Route::post('/settings', [PayPalController::class, 'updateSettings'])->name('settings.update');
        Route::post('/test-connection', [PayPalController::class, 'testConnection'])->name('test');
    });

    // Paiements PayPal Express
    Route::prefix('express')->name('express.')->group(function () {
        Route::post('/create-payment', [PayPalController::class, 'createExpressPayment'])->name('create');
        Route::get('/execute-payment', [PayPalController::class, 'executePayment'])->name('execute');
        Route::get('/cancel-payment', [PayPalController::class, 'cancelPayment'])->name('cancel');
        Route::post('/capture-payment/{paymentId}', [PayPalController::class, 'capturePayment'])->name('capture');
    });

    // Paiements directs (Credit Card via PayPal)
    Route::prefix('direct')->name('direct.')->group(function () {
        Route::post('/create-payment', [PayPalController::class, 'createDirectPayment'])->name('create');
        Route::post('/execute-payment', [PayPalController::class, 'executeDirectPayment'])->name('execute');
    });

    // Gestion des abonnements PayPal
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [PayPalController::class, 'listSubscriptions'])->name('index');
        Route::post('/create', [PayPalController::class, 'createSubscription'])->name('create');
        Route::get('/{subscriptionId}', [PayPalController::class, 'getSubscription'])->name('show');
        Route::post('/{subscriptionId}/activate', [PayPalController::class, 'activateSubscription'])->name('activate');
        Route::post('/{subscriptionId}/suspend', [PayPalController::class, 'suspendSubscription'])->name('suspend');
        Route::post('/{subscriptionId}/cancel', [PayPalController::class, 'cancelSubscription'])->name('cancel');

        // Plans d'abonnement
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/', [PayPalController::class, 'listPlans'])->name('index');
            Route::post('/', [PayPalController::class, 'createPlan'])->name('create');
            Route::get('/{planId}', [PayPalController::class, 'getPlan'])->name('show');
            Route::patch('/{planId}', [PayPalController::class, 'updatePlan'])->name('update');
            Route::post('/{planId}/deactivate', [PayPalController::class, 'deactivatePlan'])->name('deactivate');
        });
    });

    // Remboursements PayPal
    Route::prefix('refunds')->name('refunds.')->group(function () {
        Route::post('/create/{transactionId}', [PayPalController::class, 'createRefund'])->name('create');
        Route::get('/{refundId}', [PayPalController::class, 'getRefund'])->name('show');
        Route::get('/transaction/{transactionId}', [PayPalController::class, 'getTransactionRefunds'])->name('transaction');
    });

    // Gestion des disputes PayPal
    Route::prefix('disputes')->name('disputes.')->group(function () {
        Route::get('/', [PayPalController::class, 'listDisputes'])->name('index');
        Route::get('/{disputeId}', [PayPalController::class, 'getDispute'])->name('show');
        Route::post('/{disputeId}/evidence', [PayPalController::class, 'submitEvidence'])->name('evidence');
        Route::post('/{disputeId}/accept', [PayPalController::class, 'acceptDispute'])->name('accept');
    });

    // Transactions et historique
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [PayPalController::class, 'listTransactions'])->name('index');
        Route::get('/{transactionId}', [PayPalController::class, 'getTransaction'])->name('show');
        Route::get('/order/{orderId}', [PayPalController::class, 'getOrderTransactions'])->name('order');
    });

    // Webhooks PayPal
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::post('/handler', [PayPalController::class, 'handleWebhook'])->name('handler');
        Route::get('/events', [PayPalController::class, 'listWebhookEvents'])->name('events');
        Route::post('/verify/{eventId}', [PayPalController::class, 'verifyWebhook'])->name('verify');

        // Configuration des webhooks
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/config', [PayPalController::class, 'getWebhookConfig'])->name('config');
            Route::post('/config', [PayPalController::class, 'updateWebhookConfig'])->name('config.update');
            Route::post('/register', [PayPalController::class, 'registerWebhook'])->name('register');
            Route::delete('/unregister/{webhookId}', [PayPalController::class, 'unregisterWebhook'])->name('unregister');
        });
    });

    // Rapports PayPal
    Route::prefix('reports')->middleware(['auth:sanctum'])->name('reports.')->group(function () {
        Route::get('/payments', [PayPalController::class, 'paymentsReport'])->name('payments');
        Route::get('/subscriptions', [PayPalController::class, 'subscriptionsReport'])->name('subscriptions');
        Route::get('/refunds', [PayPalController::class, 'refundsReport'])->name('refunds');
        Route::get('/disputes', [PayPalController::class, 'disputesReport'])->name('disputes');
        Route::get('/analytics', [PayPalController::class, 'analyticsReport'])->name('analytics');
        Route::post('/export', [PayPalController::class, 'exportReport'])->name('export');
    });

    // Gestion des clients PayPal
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/{customerId}/payments', [PayPalController::class, 'getCustomerPayments'])->name('payments');
        Route::get('/{customerId}/subscriptions', [PayPalController::class, 'getCustomerSubscriptions'])->name('subscriptions');
        Route::post('/{customerId}/vault-payment-method', [PayPalController::class, 'vaultPaymentMethod'])->name('vault');
        Route::delete('/{customerId}/payment-method/{methodId}', [PayPalController::class, 'deletePaymentMethod'])->name('delete.method');
    });

    // Paiements récurrents
    Route::prefix('recurring')->name('recurring.')->group(function () {
        Route::post('/create-agreement', [PayPalController::class, 'createBillingAgreement'])->name('agreement.create');
        Route::post('/execute-agreement', [PayPalController::class, 'executeBillingAgreement'])->name('agreement.execute');
        Route::post('/charge/{agreementId}', [PayPalController::class, 'chargeAgreement'])->name('charge');
        Route::post('/cancel-agreement/{agreementId}', [PayPalController::class, 'cancelAgreement'])->name('agreement.cancel');
    });

    // API pour applications mobiles
    Route::prefix('mobile')->middleware(['api'])->name('mobile.')->group(function () {
        Route::post('/create-order', [PayPalController::class, 'createMobileOrder'])->name('create.order');
        Route::post('/capture-order/{orderId}', [PayPalController::class, 'captureMobileOrder'])->name('capture.order');
        Route::get('/payment-methods', [PayPalController::class, 'getMobilePaymentMethods'])->name('payment.methods');
    });

    // Utilitaires PayPal
    Route::prefix('utils')->name('utils.')->group(function () {
        Route::post('/validate-webhook', [PayPalController::class, 'validateWebhookSignature'])->name('validate.webhook');
        Route::get('/currencies', [PayPalController::class, 'getSupportedCurrencies'])->name('currencies');
        Route::get('/countries', [PayPalController::class, 'getSupportedCountries'])->name('countries');
        Route::post('/verify-payment', [PayPalController::class, 'verifyPaymentStatus'])->name('verify.payment');
    });
});

// Routes publiques PayPal (sans authentification)
Route::prefix('paypal-public')->name('paypal.public.')->group(function () {
    Route::get('/ipn-listener', [PayPalController::class, 'ipnListener'])->name('ipn');
    Route::post('/webhook-receiver', [PayPalController::class, 'webhookReceiver'])->name('webhook.receiver');
    Route::get('/return/{orderId}', [PayPalController::class, 'paymentReturn'])->name('return');
    Route::get('/cancel/{orderId}', [PayPalController::class, 'paymentCancel'])->name('cancel');
});

// Routes d'intégration e-commerce
Route::middleware(['web'])->prefix('paypal-checkout')->name('paypal.checkout.')->group(function () {
    Route::post('/create-order', [PayPalController::class, 'createCheckoutOrder'])->name('create.order');
    Route::post('/capture-order', [PayPalController::class, 'captureCheckoutOrder'])->name('capture.order');
    Route::get('/order-details/{orderId}', [PayPalController::class, 'getOrderDetails'])->name('order.details');
    Route::post('/validate-order', [PayPalController::class, 'validateOrder'])->name('validate.order');
});
