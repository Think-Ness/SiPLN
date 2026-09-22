const CACHE_NAME = 'sipln-cache-v2';
const PRECACHE_ASSETS = [
    './manifest.json',
    './assets/logopln.png',
    './assets/icons/icon-192x192.png',
    './assets/icons/icon-512x512.png',
    './assets/offline/css/bootstrap.min.css',
    './assets/offline/css/bootstrap-icons.css',
    './assets/offline/css/inter.css',
    './assets/offline/css/sweetalert2.min.css',
    './assets/offline/js/bootstrap.bundle.min.js',
    './assets/offline/js/sweetalert2.all.min.js'
];

// Install Event - Pre-cache core assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('Some precache assets failed to load:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate Event - Clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch Event - Network first, fallback to cache
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // For static assets (CSS, JS, images, fonts), try cache first or network
    if (url.pathname.includes('/assets/') || url.pathname.includes('/icons/')) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                if (cachedResponse) return cachedResponse;
                return fetch(event.request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(event.request, responseToCache);
                        });
                    }
                    return networkResponse;
                }).catch(() => {
                    return new Response('', { status: 408, statusText: 'Request Timeout' });
                });
            })
        );
        return;
    }

    // For HTML/PHP navigation requests, use network first
    event.respondWith(
        fetch(event.request).catch(async () => {
            const cached = await caches.match(event.request);
            if (cached) return cached;
            return new Response('<h3>Mode Offline</h3><p>Koneksi terputus. Silakan hubungkan kembali ke jaringan.</p>', {
                status: 503,
                headers: { 'Content-Type': 'text/html; charset=utf-8' }
            });
        })
    );
});

