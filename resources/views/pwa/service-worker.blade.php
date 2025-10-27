const CACHE_NAME = '{{ $cacheName }}';
const STATIC_CACHE = CACHE_NAME + '-static';
const DYNAMIC_CACHE = CACHE_NAME + '-dynamic';
const OFFLINE_URL = '/pwa/offline';

// Ressources à mettre en cache lors de l'installation
const CACHE_URLS = [
    '/',
    '/pwa/offline',
    '/css/app.css',
    '/js/app.js',
    '/images/logo.png',
    // Ajoutez d'autres ressources critiques
];

// Installation du Service Worker
self.addEventListener('install', event => {
    console.log('[SW] Installation...');

    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('[SW] Mise en cache des ressources statiques');
                return cache.addAll(CACHE_URLS);
            })
            .then(() => {
                console.log('[SW] Installation terminée');
                return self.skipWaiting();
            })
    );
});

// Activation du Service Worker
self.addEventListener('activate', event => {
    console.log('[SW] Activation...');

    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE &&
                            cacheName !== DYNAMIC_CACHE &&
                            cacheName.startsWith('e-menu-v')) {
                            console.log('[SW] Suppression de l\'ancien cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[SW] Activation terminée');
                return self.clients.claim();
            })
    );
});

// Interception des requêtes réseau
self.addEventListener('fetch', event => {
    // Ignorer les requêtes non-GET
    if (event.request.method !== 'GET') {
        return;
    }

    // Ignorer les requêtes vers des domaines externes
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then(cachedResponse => {
                if (cachedResponse) {
                    console.log('[SW] Réponse depuis le cache:', event.request.url);
                    return cachedResponse;
                }

                return fetch(event.request)
                    .then(response => {
                        // Vérifier si la réponse est valide
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Cloner la réponse
                        const responseToCache = response.clone();

                        // Mettre en cache les ressources dynamiques
                        if (shouldCache(event.request.url)) {
                            caches.open(DYNAMIC_CACHE)
                                .then(cache => {
                                    console.log('[SW] Mise en cache dynamique:', event.request.url);
                                    cache.put(event.request, responseToCache);
                                });
                        }

                        return response;
                    })
                    .catch(() => {
                        // Si la requête échoue, retourner la page hors ligne
                        if (event.request.destination === 'document') {
                            return caches.match(OFFLINE_URL);
                        }

                        // Pour les autres types de ressources
                        return new Response('Contenu non disponible hors ligne', {
                            status: 503,
                            statusText: 'Service Unavailable'
                        });
                    });
            })
    );
});

// Gestion des notifications push
self.addEventListener('push', event => {
    console.log('[SW] Notification push reçue');

    let notificationData = {
        title: 'E-menu',
        body: 'Nouvelle notification',
        icon: '/images/logo.png',
        badge: '/images/badge.png',
        tag: 'e-menu-notification',
        requireInteraction: false,
        actions: [
            {
                action: 'view',
                title: 'Voir',
                icon: '/images/view-icon.png'
            },
            {
                action: 'dismiss',
                title: 'Ignorer'
            }
        ]
    };

    if (event.data) {
        try {
            const data = event.data.json();
            notificationData = { ...notificationData, ...data };
        } catch (e) {
            console.error('[SW] Erreur parsing notification:', e);
            notificationData.body = event.data.text();
        }
    }

    event.waitUntil(
        self.registration.showNotification(notificationData.title, {
            body: notificationData.body,
            icon: notificationData.icon,
            badge: notificationData.badge,
            tag: notificationData.tag,
            requireInteraction: notificationData.requireInteraction,
            actions: notificationData.actions,
            data: {
                url: notificationData.url || '/',
                timestamp: Date.now()
            }
        })
    );
});

// Gestion des clics sur les notifications
self.addEventListener('notificationclick', event => {
    console.log('[SW] Clic sur notification:', event.notification.tag);

    event.notification.close();

    if (event.action === 'dismiss') {
        return;
    }

    const urlToOpen = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(clientList => {
                // Vérifier si l'application est déjà ouverte
                for (const client of clientList) {
                    if (client.url === urlToOpen && 'focus' in client) {
                        return client.focus();
                    }
                }

                // Ouvrir une nouvelle fenêtre
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

// Gestion de la synchronisation en arrière-plan
self.addEventListener('sync', event => {
    console.log('[SW] Synchronisation en arrière-plan:', event.tag);

    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

// Fonction pour déterminer si une URL doit être mise en cache
function shouldCache(url) {
    // Ne pas mettre en cache les API externes et certaines routes
    const excludePatterns = [
        '/api/external',
        '/admin/logs',
        '/debug',
        'chrome-extension://'
    ];

    return !excludePatterns.some(pattern => url.includes(pattern));
}

// Fonction de synchronisation en arrière-plan
function doBackgroundSync() {
    return new Promise((resolve, reject) => {
        // Implémenter la logique de synchronisation
        // Par exemple, envoyer les données en attente
        console.log('[SW] Synchronisation des données...');

        // Simuler une synchronisation
        setTimeout(() => {
            console.log('[SW] Synchronisation terminée');
            resolve();
        }, 1000);
    });
}

// Gestion des messages depuis l'application
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: CACHE_NAME });
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName.startsWith('e-menu-v')) {
                            return caches.delete(cacheName);
                        }
                    })
                );
            }).then(() => {
                event.ports[0].postMessage({ success: true });
            })
        );
    }
});

console.log('[SW] Service Worker E-menu chargé - Version:', CACHE_NAME);
