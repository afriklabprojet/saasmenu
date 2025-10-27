<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| PWA Routes - Progressive Web App
|--------------------------------------------------------------------------
|
| Routes pour la fonctionnalité Progressive Web App (PWA) d'E-menu.
| Permet aux utilisateurs d'installer l'application comme une app native.
|
*/

// Manifest PWA
Route::get('/manifest.json', function () {
    $app_name = \App\Helpers\helper::appdata('')->website_title ?? 'E-menu';
    $app_description = "Application de commande en ligne pour restaurants";
    $app_url = url('/');
    $icon_path = \App\Helpers\helper::image_path(\App\Helpers\helper::appdata('')->favicon ?? 'default-favicon.png');

    return response()->json([
        'name' => $app_name,
        'short_name' => $app_name,
        'description' => $app_description,
        'start_url' => $app_url,
        'display' => 'standalone',
        'background_color' => '#ffffff',
        'theme_color' => \App\Helpers\helper::appdata('')->primary_color ?? '#007bff',
        'orientation' => 'portrait-primary',
        'icons' => [
            [
                'src' => $icon_path,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'maskable any'
            ],
            [
                'src' => $icon_path,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable any'
            ]
        ],
        'categories' => ['food', 'restaurant', 'delivery'],
        'lang' => 'fr-FR',
        'dir' => 'ltr'
    ])->header('Content-Type', 'application/json');
})->name('pwa.manifest');

// Service Worker
Route::get('/sw.js', function () {
    $content = "
const CACHE_NAME = 'e-menu-v1.0.0';
const urlsToCache = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/images/logo.png',
    '/offline.html'
];

// Installation du Service Worker
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('Cache ouvert');
                return cache.addAll(urlsToCache);
            })
    );
});

// Interception des requêtes
self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Retourner la ressource depuis le cache si disponible
                if (response) {
                    return response;
                }
                return fetch(event.request);
            }
        )
    );
});

// Mise à jour du cache
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Suppression ancien cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Notifications Push (optionnel)
self.addEventListener('push', event => {
    const options = {
        body: event.data ? event.data.text() : 'Nouvelle notification E-menu',
        icon: '/images/icon-192x192.png',
        badge: '/images/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Voir',
                icon: '/images/checkmark.png'
            },
            {
                action: 'close',
                title: 'Fermer',
                icon: '/images/xmark.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('E-menu', options)
    );
});
";

    return response($content)->header('Content-Type', 'application/javascript');
})->name('pwa.sw');

// Page offline
Route::get('/offline.html', function () {
    return view('pwa.offline');
})->name('pwa.offline');

// Installation PWA
Route::middleware(['web'])->group(function () {

    // Prompt d'installation PWA
    Route::get('/pwa/install', function (Request $request) {
        // Récupérer le vendor_id depuis le contexte ou utiliser un défaut
        $vendor_id = 1; // Default fallback

        // Essayer de récupérer le vendor_id depuis la session ou request
        if ($request->route('vendor')) {
            $vendor = \App\Models\User::where('slug', $request->route('vendor'))->first();
            if ($vendor) {
                $vendor_id = $vendor->id;
            }
        } else {
            // Fallback: premier vendor disponible
            $vendor = \App\Models\User::where('type', 2)->where('is_available', 1)->first();
            if ($vendor) {
                $vendor_id = $vendor->id;
            }
        }

        // Alias pour la compatibilité avec le layout front
        $vdata = $vendor_id;

        return view('pwa.install-simple', compact('vendor_id', 'vdata'));
    })->name('pwa.install');    // API pour vérifier si PWA est installée
    Route::get('/api/pwa/status', function (Request $request) {
        // Récupérer le vendor_id depuis le contexte ou utiliser un défaut
        $vendor_id = 1; // Default fallback

        // Essayer de récupérer le vendor_id depuis la session ou request
        if ($request->route('vendor')) {
            $vendor = \App\Models\User::where('slug', $request->route('vendor'))->first();
            if ($vendor) {
                $vendor_id = $vendor->id;
            }
        } else {
            // Fallback: premier vendor disponible
            $vendor = \App\Models\User::where('type', 2)->where('is_available', 1)->first();
            if ($vendor) {
                $vendor_id = $vendor->id;
            }
        }

        return response()->json([
            'installable' => true,
            'name' => \App\Helpers\helper::appdata($vendor_id)->website_title ?? 'E-menu',
            'version' => '1.0.0'
        ]);
    })->name('pwa.status');

});

// Routes pour les notifications push (si configurées)
Route::middleware(['web', 'auth'])->group(function () {

    // Souscrire aux notifications
    Route::post('/pwa/subscribe', function (Illuminate\Http\Request $request) {
        $user = auth()->user();

        // Sauvegarder la souscription push
        $subscription = $request->validate([
            'endpoint' => 'required|url',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string'
        ]);

        // Ici vous pouvez sauvegarder en base de données
        // DB::table('push_subscriptions')->updateOrInsert(...)

        return response()->json([
            'success' => true,
            'message' => 'Notifications activées'
        ]);
    })->name('pwa.subscribe');

    // Se désabonner des notifications
    Route::post('/pwa/unsubscribe', function (Illuminate\Http\Request $request) {
        $user = auth()->user();

        // Supprimer la souscription
        // DB::table('push_subscriptions')->where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifications désactivées'
        ]);
    })->name('pwa.unsubscribe');

});
