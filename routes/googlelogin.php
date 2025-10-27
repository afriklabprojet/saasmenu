<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleLoginController;
use App\Http\Controllers\FacebookLoginController;

/*
|--------------------------------------------------------------------------
| Google Login Routes
|--------------------------------------------------------------------------
|
| Routes pour l'authentification via Google OAuth2
| Simplifie l'inscription et connexion des utilisateurs
|
*/

Route::prefix('auth/google')->name('google.')->group(function () {

    // Redirection vers Google OAuth
    Route::get('/redirect', [GoogleLoginController::class, 'redirect'])->name('redirect');

    // Callback de retour Google
    Route::get('/callback', [GoogleLoginController::class, 'callback'])->name('callback');

    // Déconnexion Google
    Route::post('/logout', [GoogleLoginController::class, 'logout'])->name('logout');

    // Lier un compte Google existant
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/link', [GoogleLoginController::class, 'linkAccount'])->name('link');
        Route::delete('/unlink', [GoogleLoginController::class, 'unlinkAccount'])->name('unlink');
    });
});

/*
|--------------------------------------------------------------------------
| Facebook Login Routes
|--------------------------------------------------------------------------
|
| Routes pour l'authentification via Facebook OAuth2
| Permet connexion rapide avec profil Facebook
|
*/

Route::prefix('auth/facebook')->name('facebook.')->group(function () {

    // Redirection vers Facebook OAuth
    Route::get('/redirect', [FacebookLoginController::class, 'redirect'])->name('redirect');

    // Callback de retour Facebook
    Route::get('/callback', [FacebookLoginController::class, 'callback'])->name('callback');

    // Déconnexion Facebook
    Route::post('/logout', [FacebookLoginController::class, 'logout'])->name('logout');

    // Lier un compte Facebook existant
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/link', [FacebookLoginController::class, 'linkAccount'])->name('link');
        Route::delete('/unlink', [FacebookLoginController::class, 'unlinkAccount'])->name('unlink');
    });
});

/*
|--------------------------------------------------------------------------
| Social Authentication API Routes
|--------------------------------------------------------------------------
|
| Routes API pour les applications mobiles
| Authentification via tokens sociaux
|
*/

Route::prefix('api/social')->middleware(['api'])->name('api.social.')->group(function () {

    // Google API Auth
    Route::prefix('google')->name('google.')->group(function () {
        Route::post('/auth', [GoogleLoginController::class, 'authenticateWithToken'])->name('auth');
        Route::post('/register', [GoogleLoginController::class, 'registerWithToken'])->name('register');
        Route::post('/link', [GoogleLoginController::class, 'linkAccountWithToken'])->name('link');
    });

    // Facebook API Auth
    Route::prefix('facebook')->name('facebook.')->group(function () {
        Route::post('/auth', [FacebookLoginController::class, 'authenticateWithToken'])->name('auth');
        Route::post('/register', [FacebookLoginController::class, 'registerWithToken'])->name('register');
        Route::post('/link', [FacebookLoginController::class, 'linkAccountWithToken'])->name('link');
    });

    // Gestion des comptes sociaux liés
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/linked-accounts', [GoogleLoginController::class, 'getLinkedAccounts'])->name('linked.accounts');
        Route::delete('/unlink/{provider}', [GoogleLoginController::class, 'unlinkSocialAccount'])->name('unlink');
    });
});

/*
|--------------------------------------------------------------------------
| Social Profile Management
|--------------------------------------------------------------------------
|
| Gestion des profils et synchronisation des données sociales
|
*/

Route::middleware(['auth:sanctum'])->prefix('social/profile')->name('social.profile.')->group(function () {

    // Synchronisation des données Google
    Route::post('/sync/google', [GoogleLoginController::class, 'syncGoogleProfile'])->name('sync.google');

    // Synchronisation des données Facebook
    Route::post('/sync/facebook', [FacebookLoginController::class, 'syncFacebookProfile'])->name('sync.facebook');

    // Import de contacts sociaux
    Route::post('/import/google-contacts', [GoogleLoginController::class, 'importGoogleContacts'])->name('import.google.contacts');
    Route::post('/import/facebook-friends', [FacebookLoginController::class, 'importFacebookFriends'])->name('import.facebook.friends');

    // Partage social
    Route::post('/share/google', [GoogleLoginController::class, 'shareToGoogle'])->name('share.google');
    Route::post('/share/facebook', [FacebookLoginController::class, 'shareToFacebook'])->name('share.facebook');
});

/*
|--------------------------------------------------------------------------
| Social Login Configuration (Admin)
|--------------------------------------------------------------------------
|
| Configuration des paramètres sociaux par les administrateurs
|
*/

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin/social')->name('admin.social.')->group(function () {

    // Configuration Google
    Route::prefix('google')->name('google.')->group(function () {
        Route::get('/settings', [GoogleLoginController::class, 'getSettings'])->name('settings');
        Route::post('/settings', [GoogleLoginController::class, 'updateSettings'])->name('settings.update');
        Route::post('/test', [GoogleLoginController::class, 'testConnection'])->name('test');
        Route::get('/stats', [GoogleLoginController::class, 'getLoginStats'])->name('stats');
    });

    // Configuration Facebook
    Route::prefix('facebook')->name('facebook.')->group(function () {
        Route::get('/settings', [FacebookLoginController::class, 'getSettings'])->name('settings');
        Route::post('/settings', [FacebookLoginController::class, 'updateSettings'])->name('settings.update');
        Route::post('/test', [FacebookLoginController::class, 'testConnection'])->name('test');
        Route::get('/stats', [FacebookLoginController::class, 'getLoginStats'])->name('stats');
    });

    // Rapports et analytics
    Route::get('/analytics', [GoogleLoginController::class, 'getSocialAnalytics'])->name('analytics');
    Route::get('/users', [GoogleLoginController::class, 'getSocialUsers'])->name('users');
    Route::post('/bulk-actions', [GoogleLoginController::class, 'bulkSocialActions'])->name('bulk.actions');
});

/*
|--------------------------------------------------------------------------
| Social Login Webhooks
|--------------------------------------------------------------------------
|
| Webhooks pour les événements des plateformes sociales
|
*/

Route::prefix('webhooks/social')->name('webhooks.social.')->group(function () {

    // Google webhooks
    Route::post('/google', [GoogleLoginController::class, 'handleGoogleWebhook'])->name('google');

    // Facebook webhooks
    Route::post('/facebook', [FacebookLoginController::class, 'handleFacebookWebhook'])->name('facebook');
});

/*
|--------------------------------------------------------------------------
| Social Features Integration
|--------------------------------------------------------------------------
|
| Intégration des fonctionnalités sociales avec le système
|
*/

Route::middleware(['auth:sanctum'])->prefix('social/features')->name('social.features.')->group(function () {

    // Invitations sociales
    Route::post('/invite/google', [GoogleLoginController::class, 'sendGoogleInvite'])->name('invite.google');
    Route::post('/invite/facebook', [FacebookLoginController::class, 'sendFacebookInvite'])->name('invite.facebook');

    // Recommandations sociales
    Route::get('/recommendations', [GoogleLoginController::class, 'getSocialRecommendations'])->name('recommendations');

    // Reviews et ratings sociaux
    Route::post('/review/share/google', [GoogleLoginController::class, 'shareReviewToGoogle'])->name('review.share.google');
    Route::post('/review/share/facebook', [FacebookLoginController::class, 'shareReviewToFacebook'])->name('review.share.facebook');
});

/*
|--------------------------------------------------------------------------
| Social Login Utilities
|--------------------------------------------------------------------------
|
| Utilitaires et helpers pour l'authentification sociale
|
*/

Route::prefix('social/utils')->name('social.utils.')->group(function () {

    // Validation des tokens
    Route::post('/validate/google-token', [GoogleLoginController::class, 'validateGoogleToken'])->name('validate.google');
    Route::post('/validate/facebook-token', [FacebookLoginController::class, 'validateFacebookToken'])->name('validate.facebook');

    // Informations sur les providers
    Route::get('/providers', [GoogleLoginController::class, 'getAvailableProviders'])->name('providers');
    Route::get('/provider/{provider}/info', [GoogleLoginController::class, 'getProviderInfo'])->name('provider.info');

    // Status de connexion sociale
    Route::get('/status', [GoogleLoginController::class, 'getSocialLoginStatus'])->name('status');
});
