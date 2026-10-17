const CACHE_NAME = 'soho-coffee-v3';
const STATIC_ASSETS = [
    '/manifest.json',
    '/icons/Logo.png',
    '/icons/icon-192x192.png',
    '/icons/icon-512x512.png',
    '/icons/apple-touch-icon.png',
    '/icons/icon.svg',
    '/favicon.ico',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => {
                return Promise.all(
                    keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
                );
            })
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Do not intercept non-GET or HTML page navigations to prevent stale session/CSRF state
    if (request.method !== 'GET' || request.mode === 'navigate') {
        return;
    }

    const url = new URL(request.url);

    // Only cache local static assets (build assets, icons, manifest)
    if (url.origin === location.origin) {
        if (
            url.pathname.startsWith('/build/') ||
            url.pathname.startsWith('/icons/') ||
            url.pathname === '/manifest.json'
        ) {
            event.respondWith(
                caches.match(request).then((cached) => {
                    return cached || fetch(request).then((response) => {
                        if (response && response.status === 200) {
                            const copy = response.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                        }
                        return response;
                    });
                })
            );
        }
    }
});
