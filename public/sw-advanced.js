/**
 * RestroSaaS Advanced Service Worker
 * Version: 2.0.0
 * Performance-optimized PWA with intelligent caching strategies
 */

const CACHE_VERSION = 'v2.0.0';
const STATIC_CACHE_NAME = `restro-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE_NAME = `restro-dynamic-${CACHE_VERSION}`;
const API_CACHE_NAME = `restro-api-${CACHE_VERSION}`;
const IMAGES_CACHE_NAME = `restro-images-${CACHE_VERSION}`;

// Cache strategies configuration
const CACHE_STRATEGIES = {
    STATIC_ASSETS: 'cache-first',
    API_CALLS: 'network-first',
    IMAGES: 'cache-first',
    PAGES: 'stale-while-revalidate',
    ANALYTICS: 'network-only'
};

// Critical files to cache on install
const STATIC_FILES = [
    '/',
    '/admin/dashboard',
    '/admin/orders',
    '/admin/products',
    '/offline.html',
    '/admin-assets/css/bootstrap.min.css',
    '/admin-assets/css/style.css',
    '/admin-assets/js/jquery.min.js',
    '/admin-assets/js/bootstrap.bundle.min.js',
    '/admin-assets/images/logo.png',
    '/manifest.json'
];

// API endpoints for network-first strategy
const API_PATTERNS = [
    '/api/dashboard',
    '/api/orders',
    '/api/products',
    '/api/analytics',
    '/admin/orders/ajax',
    '/admin/products/ajax'
];

// Image patterns for cache-first strategy
const IMAGE_PATTERNS = [
    '/admin-assets/images/',
    '/storage/app/public/',
    '.jpg',
    '.jpeg',
    '.png',
    '.gif',
    '.webp',
    '.svg'
];

// Network-only patterns (no cache)
const NETWORK_ONLY_PATTERNS = [
    '/api/analytics/real-time',
    '/api/notifications/push',
    '/admin/login',
    '/admin/logout'
];

/**
 * Service Worker Install Event
 */
self.addEventListener('install', event => {
    console.log('[SW] Installing Service Worker v' + CACHE_VERSION);

    event.waitUntil(
        caches.open(STATIC_CACHE_NAME)
            .then(cache => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => {
                console.log('[SW] Static assets cached successfully');
                // Force activation
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('[SW] Error caching static assets:', error);
            })
    );
});

/**
 * Service Worker Activate Event
 */
self.addEventListener('activate', event => {
    console.log('[SW] Activating Service Worker v' + CACHE_VERSION);

    event.waitUntil(
        Promise.all([
            // Clean old caches
            cleanOldCaches(),
            // Take control of all clients
            self.clients.claim()
        ])
    );
});

/**
 * Service Worker Fetch Event - Intelligent routing
 */
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip external requests
    if (!url.origin.includes(self.location.origin)) {
        return;
    }

    // Route requests based on patterns
    if (isNetworkOnlyRequest(request)) {
        event.respondWith(networkOnlyStrategy(request));
    } else if (isAPIRequest(request)) {
        event.respondWith(networkFirstStrategy(request));
    } else if (isImageRequest(request)) {
        event.respondWith(cacheFirstStrategy(request, IMAGES_CACHE_NAME));
    } else if (isStaticAsset(request)) {
        event.respondWith(cacheFirstStrategy(request, STATIC_CACHE_NAME));
    } else {
        event.respondWith(staleWhileRevalidateStrategy(request));
    }
});

/**
 * Background Sync for offline orders
 */
self.addEventListener('sync', event => {
    if (event.tag === 'order-sync') {
        event.waitUntil(syncOfflineOrders());
    }
});

/**
 * Push notifications
 */
self.addEventListener('push', event => {
    const options = {
        body: event.data ? event.data.text() : 'Nouvelle notification RestroSaaS',
        icon: '/admin-assets/images/icons/icon-192x192.png',
        badge: '/admin-assets/images/icons/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: event.data ? JSON.parse(event.data.text()) : {},
        actions: [
            {
                action: 'view',
                title: 'Voir',
                icon: '/admin-assets/images/icons/view-24x24.png'
            },
            {
                action: 'dismiss',
                title: 'Ignorer',
                icon: '/admin-assets/images/icons/close-24x24.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('RestroSaaS', options)
    );
});

/**
 * Notification click handler
 */
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'view') {
        event.waitUntil(
            clients.openWindow('/admin/orders')
        );
    }
});

// ==================== CACHE STRATEGIES ====================

/**
 * Cache First Strategy (for static assets, images)
 */
async function cacheFirstStrategy(request, cacheName) {
    try {
        const cache = await caches.open(cacheName);
        const cached = await cache.match(request);

        if (cached) {
            // Return cached version immediately
            updateCacheInBackground(request, cache);
            return cached;
        }

        // Not in cache, fetch from network
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;

    } catch (error) {
        console.error('[SW] Cache-first strategy failed:', error);
        return await fallbackResponse(request);
    }
}

/**
 * Network First Strategy (for API calls)
 */
async function networkFirstStrategy(request) {
    try {
        const response = await fetch(request);

        if (response.ok) {
            const cache = await caches.open(API_CACHE_NAME);
            cache.put(request, response.clone());
        }

        return response;

    } catch (error) {
        console.warn('[SW] Network failed, trying cache:', error);
        const cache = await caches.open(API_CACHE_NAME);
        const cached = await cache.match(request);

        if (cached) {
            return cached;
        }

        return await fallbackResponse(request);
    }
}

/**
 * Stale While Revalidate Strategy (for pages)
 */
async function staleWhileRevalidateStrategy(request) {
    try {
        const cache = await caches.open(DYNAMIC_CACHE_NAME);
        const cached = await cache.match(request);

        const fetchPromise = fetch(request).then(response => {
            if (response.ok) {
                cache.put(request, response.clone());
            }
            return response;
        });

        return cached || await fetchPromise;

    } catch (error) {
        console.error('[SW] Stale-while-revalidate failed:', error);
        return await fallbackResponse(request);
    }
}

/**
 * Network Only Strategy (for sensitive operations)
 */
async function networkOnlyStrategy(request) {
    try {
        return await fetch(request);
    } catch (error) {
        console.error('[SW] Network-only request failed:', error);
        return new Response('Network Error', { status: 503 });
    }
}

// ==================== HELPER FUNCTIONS ====================

/**
 * Check if request should use network-only strategy
 */
function isNetworkOnlyRequest(request) {
    return NETWORK_ONLY_PATTERNS.some(pattern =>
        request.url.includes(pattern)
    );
}

/**
 * Check if request is an API call
 */
function isAPIRequest(request) {
    return API_PATTERNS.some(pattern =>
        request.url.includes(pattern)
    );
}

/**
 * Check if request is for an image
 */
function isImageRequest(request) {
    return IMAGE_PATTERNS.some(pattern =>
        request.url.includes(pattern)
    );
}

/**
 * Check if request is for a static asset
 */
function isStaticAsset(request) {
    return request.url.includes('/admin-assets/') ||
           request.url.includes('.css') ||
           request.url.includes('.js') ||
           request.url.includes('.woff') ||
           request.url.includes('.ttf');
}

/**
 * Update cache in background
 */
async function updateCacheInBackground(request, cache) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            await cache.put(request, response);
        }
    } catch (error) {
        console.warn('[SW] Background cache update failed:', error);
    }
}

/**
 * Clean old caches
 */
async function cleanOldCaches() {
    const cacheNames = await caches.keys();
    const oldCaches = cacheNames.filter(name =>
        name.includes('restro-') && !name.includes(CACHE_VERSION)
    );

    return Promise.all(
        oldCaches.map(name => {
            console.log('[SW] Deleting old cache:', name);
            return caches.delete(name);
        })
    );
}

/**
 * Fallback response for failed requests
 */
async function fallbackResponse(request) {
    if (request.destination === 'document') {
        return await caches.match('/offline.html');
    }

    if (request.destination === 'image') {
        return new Response('<svg><rect width="100%" height="100%" fill="#f0f0f0"/></svg>', {
            headers: { 'Content-Type': 'image/svg+xml' }
        });
    }

    return new Response('Content not available offline', {
        status: 503,
        headers: { 'Content-Type': 'text/plain' }
    });
}

/**
 * Sync offline orders when connection restored
 */
async function syncOfflineOrders() {
    try {
        const orders = await getOfflineOrders();

        for (const order of orders) {
            try {
                const response = await fetch('/api/orders/sync', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(order)
                });

                if (response.ok) {
                    await removeOfflineOrder(order.id);
                }
            } catch (error) {
                console.error('[SW] Failed to sync order:', error);
            }
        }

        console.log('[SW] Offline orders synced');

    } catch (error) {
        console.error('[SW] Background sync failed:', error);
    }
}

/**
 * Get offline orders from IndexedDB
 */
async function getOfflineOrders() {
    // Implementation would use IndexedDB
    return [];
}

/**
 * Remove synced order from IndexedDB
 */
async function removeOfflineOrder(orderId) {
    // Implementation would use IndexedDB
    console.log('[SW] Removed synced order:', orderId);
}

console.log('[SW] Service Worker loaded - RestroSaaS v' + CACHE_VERSION);
