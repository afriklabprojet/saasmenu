<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\Admin\LoyaltyAdminController;

/*
|--------------------------------------------------------------------------
| Loyalty Program Routes - Programme de Fidélité
|--------------------------------------------------------------------------
|
| Système complet de fidélisation avec points, récompenses, niveaux VIP,
| cashback et défis pour augmenter la rétention client et le panier moyen.
|
*/

// Routes publiques pour les clients
Route::prefix('loyalty')->group(function () {

    // Inscription au programme de fidélité
    Route::post('/join', [LoyaltyController::class, 'joinProgram'])->name('loyalty.join');

    // Consultation du solde de points (avec email/téléphone)
    Route::post('/balance', [LoyaltyController::class, 'getBalance'])->name('loyalty.balance');

    // Historique des points
    Route::post('/history', [LoyaltyController::class, 'getHistory'])->name('loyalty.history');

    // Récompenses disponibles
    Route::get('/rewards', [LoyaltyController::class, 'getAvailableRewards'])->name('loyalty.rewards');

    // Utiliser des points pour une récompense
    Route::post('/redeem/{reward_id}', [LoyaltyController::class, 'redeemReward'])->name('loyalty.redeem');

    // Défis du moment
    Route::get('/challenges', [LoyaltyController::class, 'getCurrentChallenges'])->name('loyalty.challenges');

    // Tableau de classement (si activé)
    Route::get('/leaderboard', [LoyaltyController::class, 'getLeaderboard'])->name('loyalty.leaderboard');
});

// Routes authentifiées pour membres
Route::middleware(['auth'])->prefix('loyalty')->group(function () {

    // Profil de fidélité complet
    Route::get('/profile', [LoyaltyController::class, 'getProfile'])->name('loyalty.profile');

    // Parrainage - obtenir le code de parrainage
    Route::get('/referral-code', [LoyaltyController::class, 'getReferralCode'])->name('loyalty.referral.code');

    // Parrainer un ami
    Route::post('/refer-friend', [LoyaltyController::class, 'referFriend'])->name('loyalty.refer');

    // Notifications de fidélité
    Route::get('/notifications', [LoyaltyController::class, 'getNotifications'])->name('loyalty.notifications');

    // Marquer notification comme lue
    Route::put('/notifications/{id}/read', [LoyaltyController::class, 'markNotificationRead'])
        ->name('loyalty.notifications.read');
});

// Routes AJAX pour intégration dans les commandes
Route::prefix('ajax/loyalty')->group(function () {

    // Calculer les points pour un panier
    Route::post('/calculate-points', [LoyaltyController::class, 'calculatePointsForCart']);

    // Appliquer une récompense à une commande
    Route::post('/apply-reward', [LoyaltyController::class, 'applyRewardToOrder']);

    // Vérifier l'éligibilité à une promotion
    Route::post('/check-eligibility', [LoyaltyController::class, 'checkPromotionEligibility']);

    // Suggestions de récompenses pour le panier actuel
    Route::post('/suggest-rewards', [LoyaltyController::class, 'suggestRewards']);
});

// Webhooks pour l'attribution de points automatique
Route::prefix('webhooks/loyalty')->group(function () {

    // Attribution de points après commande complétée
    Route::post('/order-completed', [LoyaltyController::class, 'handleOrderCompleted']);

    // Attribution de points pour actions spéciales
    Route::post('/special-action', [LoyaltyController::class, 'handleSpecialAction']);

    // Notification d'anniversaire
    Route::post('/birthday-bonus', [LoyaltyController::class, 'handleBirthdayBonus']);
});

// Routes pour les restaurants (authentifiés)
Route::middleware(['auth', 'role:restaurant'])->prefix('admin/loyalty')->group(function () {

    // Dashboard du programme de fidélité
    Route::get('/', [LoyaltyAdminController::class, 'index'])->name('admin.loyalty.index');

    // Configuration du programme
    Route::get('/settings', [LoyaltyAdminController::class, 'settings'])->name('admin.loyalty.settings');
    Route::put('/settings', [LoyaltyAdminController::class, 'updateSettings'])->name('admin.loyalty.settings.update');

    // Gestion des niveaux VIP
    Route::resource('/tiers', LoyaltyAdminController::class . '@tiers', ['as' => 'admin.loyalty']);
    Route::put('/tiers/{id}/toggle', [LoyaltyAdminController::class, 'toggleTier'])->name('admin.loyalty.tiers.toggle');

    // Gestion des récompenses
    Route::resource('/rewards', LoyaltyAdminController::class . '@rewards', ['as' => 'admin.loyalty']);
    Route::put('/rewards/{id}/toggle', [LoyaltyAdminController::class, 'toggleReward'])->name('admin.loyalty.rewards.toggle');

    // Gestion des défis
    Route::resource('/challenges', LoyaltyAdminController::class . '@challenges', ['as' => 'admin.loyalty']);
    Route::put('/challenges/{id}/toggle', [LoyaltyAdminController::class, 'toggleChallenge'])
        ->name('admin.loyalty.challenges.toggle');

    // Membres du programme
    Route::get('/members', [LoyaltyAdminController::class, 'members'])->name('admin.loyalty.members');
    Route::get('/members/{id}', [LoyaltyAdminController::class, 'showMember'])->name('admin.loyalty.members.show');
    Route::put('/members/{id}/tier', [LoyaltyAdminController::class, 'updateMemberTier'])
        ->name('admin.loyalty.members.tier');

    // Attribution manuelle de points
    Route::post('/members/{id}/points', [LoyaltyAdminController::class, 'addPoints'])
        ->name('admin.loyalty.members.points');
    Route::delete('/members/{id}/points', [LoyaltyAdminController::class, 'deductPoints'])
        ->name('admin.loyalty.members.points.deduct');

    // Historique des transactions de points
    Route::get('/transactions', [LoyaltyAdminController::class, 'transactions'])->name('admin.loyalty.transactions');
    Route::get('/transactions/export', [LoyaltyAdminController::class, 'exportTransactions'])
        ->name('admin.loyalty.transactions.export');

    // Analytiques avancées
    Route::get('/analytics', [LoyaltyAdminController::class, 'analytics'])->name('admin.loyalty.analytics');
    Route::get('/analytics/retention', [LoyaltyAdminController::class, 'retentionAnalytics'])
        ->name('admin.loyalty.analytics.retention');
    Route::get('/analytics/revenue', [LoyaltyAdminController::class, 'revenueAnalytics'])
        ->name('admin.loyalty.analytics.revenue');

    // Campagnes de fidélité
    Route::resource('/campaigns', LoyaltyAdminController::class . '@campaigns', ['as' => 'admin.loyalty']);
    Route::put('/campaigns/{id}/toggle', [LoyaltyAdminController::class, 'toggleCampaign'])
        ->name('admin.loyalty.campaigns.toggle');

    // Communication avec les membres
    Route::get('/communications', [LoyaltyAdminController::class, 'communications'])
        ->name('admin.loyalty.communications');
    Route::post('/communications/send', [LoyaltyAdminController::class, 'sendCommunication'])
        ->name('admin.loyalty.communications.send');

    // Segmentation des clients
    Route::get('/segments', [LoyaltyAdminController::class, 'segments'])->name('admin.loyalty.segments');
    Route::post('/segments', [LoyaltyAdminController::class, 'createSegment'])->name('admin.loyalty.segments.create');
});

// API Routes pour applications mobiles
Route::prefix('api/loyalty')->group(function () {

    // Profile API
    Route::middleware(['auth:api'])->group(function () {
        Route::get('/profile', [LoyaltyController::class, 'getAPIProfile']);
        Route::put('/profile', [LoyaltyController::class, 'updateAPIProfile']);
    });

    // Points et récompenses API
    Route::post('/check-member', [LoyaltyController::class, 'checkMemberAPI']);
    Route::post('/calculate-benefits', [LoyaltyController::class, 'calculateBenefitsAPI']);
    Route::post('/redeem-mobile', [LoyaltyController::class, 'redeemRewardMobile']);

    // Gamification API
    Route::get('/leaderboard/mobile', [LoyaltyController::class, 'getMobileLeaderboard']);
    Route::get('/achievements', [LoyaltyController::class, 'getUserAchievements']);
    Route::post('/claim-achievement', [LoyaltyController::class, 'claimAchievement']);
});

// Routes temps réel pour notifications
Route::middleware(['auth'])->prefix('realtime/loyalty')->group(function () {

    // Stream des notifications de fidélité
    Route::get('/notifications/stream', [LoyaltyController::class, 'getNotificationStream']);

    // Mise à jour du solde en temps réel
    Route::get('/balance/live', [LoyaltyController::class, 'getLiveBalance']);

    // Événements de gamification en direct
    Route::get('/events/stream', [LoyaltyController::class, 'getGameEvents']);
});

// Routes utilitaires
Route::prefix('loyalty-utils')->group(function () {

    // Vérification rapide d'un membre (pour POS)
    Route::post('/quick-check', [LoyaltyController::class, 'quickMemberCheck']);

    // Calcul de conversion points/monnaie
    Route::get('/conversion-rate', [LoyaltyController::class, 'getConversionRate']);

    // Conditions générales du programme
    Route::get('/terms', [LoyaltyController::class, 'getTermsAndConditions']);

    // Status du programme (actif/inactif)
    Route::get('/status/{restaurant_slug}', [LoyaltyController::class, 'getProgramStatus']);
});
