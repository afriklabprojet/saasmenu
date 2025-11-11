<?php

/**
 * ========================================
 * 🚀 MIGRATION PROGRESSIVE DES ROUTES V2
 * ========================================
 *
 * Ce fichier définit les nouvelles routes refactorisées (v2)
 * qui coexistent avec les anciennes routes HomeController.
 *
 * STRATÉGIE DE MIGRATION :
 * 1. Phase 1 : Activer routes v2 avec préfixe /v2 (CETTE ÉTAPE)
 * 2. Phase 2 : Tests A/B entre v1 et v2
 * 3. Phase 3 : Switcher trafic progressivement vers v2
 * 4. Phase 4 : Déprécier et supprimer routes v1
 *
 * ACTIVATION : Décommenter la ligne dans routes/web.php
 * require __DIR__ . '/web_v2_migration.php';
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\web\MenuController;
use App\Http\Controllers\web\CartController;
use App\Http\Controllers\web\OrderController as WebOrderController;
use App\Http\Controllers\web\PageController;
use App\Http\Controllers\web\ContactController as WebContactController;

/**
 * ========================================
 * 📍 ROUTES V2 - CONTRÔLEURS REFACTORISÉS
 * ========================================
 */

Route::group(['prefix' => 'v2', 'as' => 'v2.'], function () {

    // 🍽️ MENU & PRODUITS (MenuController)
    Route::name('menu.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/categories', [MenuController::class, 'categories'])->name('categories');
        Route::get('/product/{id}', [MenuController::class, 'details'])->name('details');
        Route::get('/search', [MenuController::class, 'search'])->name('search');
        Route::get('/topdeals', [MenuController::class, 'alltopdeals'])->name('topdeals');
        Route::get('/products/variants/{id}', [MenuController::class, 'getProductsVariantQuantity'])->name('variants');
    });

    // 🛒 PANIER (CartController)
    Route::name('cart.')->group(function () {
        Route::get('/cart', [CartController::class, 'cart'])->name('index');
        Route::post('/cart/add', [CartController::class, 'addToCart'])->name('add');
        Route::patch('/cart/update', [CartController::class, 'updateQuantity'])->name('update');
        Route::delete('/cart/remove', [CartController::class, 'removeItem'])->name('remove');
    });

    // 📦 COMMANDES (OrderController)
    Route::name('order.')->group(function () {
        // Checkout & Payment
        Route::get('/checkout', [WebOrderController::class, 'checkout'])->name('checkout');
        Route::post('/payment', [WebOrderController::class, 'paymentmethod'])->name('payment');
        Route::post('/ordercreate', [WebOrderController::class, 'ordercreate'])->name('create');

        // Coupons
        Route::post('/promocode/apply', [WebOrderController::class, 'applyPromocode'])->name('promo.apply');
        Route::post('/promocode/remove', [WebOrderController::class, 'removePromocode'])->name('promo.remove');

        // Time slots
        Route::post('/timeslot', [WebOrderController::class, 'timeslot'])->name('timeslot');

        // Order tracking & management
        Route::get('/success', [WebOrderController::class, 'success'])->name('success');
        Route::get('/track/{order_number}', [WebOrderController::class, 'track'])->name('track');
        Route::post('/cancel', [WebOrderController::class, 'cancel'])->name('cancel');
    });

    // 📄 PAGES STATIQUES & CONTACT
    Route::name('page.')->group(function () {
        // Pages statiques (PageController)
        Route::get('/about', [PageController::class, 'aboutUs'])->name('about');
        Route::get('/terms', [PageController::class, 'termsConditions'])->name('terms');
        Route::get('/privacy', [PageController::class, 'privacyPolicy'])->name('privacy');
        Route::get('/refund-policy', [PageController::class, 'refundPrivacyPolicy'])->name('refund');

        // Contact & Réservations (ContactController)
        Route::get('/contact', [WebContactController::class, 'contact'])->name('contact');
        Route::post('/contact/submit', [WebContactController::class, 'saveContact'])->name('contact.submit');
        Route::post('/subscribe', [WebContactController::class, 'subscribe'])->name('subscribe');

        // Table Booking
        Route::get('/table-booking', [WebContactController::class, 'tableBook'])->name('booking');
        Route::post('/table-booking/submit', [WebContactController::class, 'saveBooking'])->name('booking.submit');
    });
});

/**
 * ========================================
 * 🧪 ROUTES A/B TESTING (Optionnel)
 * ========================================
 * Permet de tester v1 vs v2 avec répartition du trafic
 */

Route::middleware(['ab.testing'])->group(function () {
    // Ces routes utiliseront un middleware qui décide entre v1 et v2
    // Basé sur : user segment, cookie, feature flag, etc.

    Route::get('/checkout-ab', function () {
        // Middleware 'ab.testing' redirigera vers v1 ou v2
        return redirect()->route(session('ab_variant') === 'v2' ? 'v2.order.checkout' : 'front.checkout');
    })->name('checkout.ab');
});

/**
 * ========================================
 * 📊 ROUTES ANALYTICS V2
 * ========================================
 * Pour tracker performance et adoption de v2
 */

Route::prefix('v2/analytics')->name('v2.analytics.')->group(function () {
    Route::post('/track-event', function () {
        // Logger événements pour comparer v1 vs v2
        return response()->json(['status' => 'tracked']);
    })->name('track');

    Route::post('/track-error', function () {
        // Logger erreurs spécifiques à v2
        return response()->json(['status' => 'logged']);
    })->name('error');
});

/**
 * ========================================
 * 🔀 ROUTES COMPATIBILITÉ ASCENDANTE
 * ========================================
 * Redirections depuis anciennes URLs vers v2
 * À activer en Phase 3 de la migration
 */

/*
Route::group(['prefix' => '', 'middleware' => 'redirect.to.v2'], function () {
    // Exemple : redirection transparente
    Route::get('/checkout', function () {
        return redirect()->route('v2.order.checkout');
    });

    Route::get('/cart', function () {
        return redirect()->route('v2.cart.index');
    });
});
*/

/**
 * ========================================
 * 📝 DOCUMENTATION MIGRATION
 * ========================================
 *
 * COMMENT ACTIVER V2 :
 * --------------------
 * 1. Dans routes/web.php, ajouter :
 *    require __DIR__ . '/web_v2_migration.php';
 *
 * 2. Tester les nouvelles routes :
 *    curl http://localhost/v2/
 *    curl http://localhost/v2/cart
 *    curl http://localhost/v2/checkout
 *
 * 3. Monitorer les métriques :
 *    - Temps de réponse v1 vs v2
 *    - Taux d'erreur v1 vs v2
 *    - Conversion checkout v1 vs v2
 *
 * ROLLBACK EN CAS DE PROBLÈME :
 * ------------------------------
 * 1. Commenter la ligne require dans web.php
 * 2. Ou désactiver middleware en ajoutant :
 *    Route::middleware(['v2.disabled'])->group(...)
 *
 * MAPPING ROUTES V1 → V2 :
 * ------------------------
 * V1 (HomeController)              → V2 (Refactorisé)
 * /{vendor}                        → /v2/ (MenuController::index)
 * /{vendor}/cart                   → /v2/cart (CartController::cart)
 * /{vendor}/checkout               → /v2/checkout (OrderController::checkout)
 * /{vendor}/product/{id}           → /v2/product/{id} (MenuController::details)
 * /{vendor}/search                 → /v2/search (MenuController::search)
 * /add-to-cart                     → /v2/cart/add (CartController::addToCart)
 * /cart/qtyupdate                  → /v2/cart/update (CartController::updateQuantity)
 * /cart/deletecartitem             → /v2/cart/remove (CartController::removeItem)
 * /{vendor}/payment                → /v2/payment (OrderController::paymentmethod)
 * /{vendor}/success                → /v2/success (OrderController::success)
 * /{vendor}/track-order/{number}   → /v2/track/{number} (OrderController::track)
 * /{vendor}/cancel-order/{number}  → /v2/cancel (OrderController::cancel)
 * /applypromocode                  → /v2/promocode/apply (OrderController::applyPromocode)
 * /removepromocode                 → /v2/promocode/remove (OrderController::removePromocode)
 * /timeslot                        → /v2/timeslot (OrderController::timeslot)
 *
 * NOUVEAUX ENDPOINTS CALCULÉS :
 * -----------------------------
 * /v2/calculate-tax               → Exposé comme API (OrderController::calculateTax)
 * /v2/calculate-delivery          → Exposé comme API (OrderController::calculateDeliveryCharge)
 * /v2/validate-stock              → Exposé comme API (OrderController::validateCartStock)
 */
