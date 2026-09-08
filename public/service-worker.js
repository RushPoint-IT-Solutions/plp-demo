const CACHE_VERSION = 'plp-pwa-v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const OFFLINE_URL = new URL('./offline.html', self.location).href;
const PRECACHE_URLS = [
    OFFLINE_URL,
    new URL('./pwa/icon-192.png', self.location).href,
    new URL('./pwa/icon-512.png', self.location).href,
    new URL('./pwa/icon-maskable-512.png', self.location).href
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('plp-pwa-') && key !== STATIC_CACHE)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (request.method !== 'GET') {
        return;
    }

    const requestUrl = new URL(request.url);
    if (requestUrl.origin !== self.location.origin) {
        return;
    }

    // Authenticated pages always come from the network. If the server cannot
    // be reached, show a static offline screen without storing private HTML.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Cache only files from known public asset folders. API/JSON requests,
    // uploaded records, private media, and all mutations remain network-only.
    if (!['style', 'script', 'image', 'font'].includes(request.destination)) {
        return;
    }

    const scopePath = new URL(self.registration.scope).pathname;
    const relativePath = requestUrl.pathname.startsWith(scopePath)
        ? requestUrl.pathname.slice(scopePath.length)
        : '';
    const publicAssetFolders = ['css/', 'fonts/', 'images/', 'img/', 'js/', 'pwa/', 'svg/', 'vendor/'];

    if (!publicAssetFolders.some((folder) => relativePath.startsWith(folder))) {
        return;
    }

    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            const networkResponse = fetch(request)
                .then((response) => {
                    if (response && response.ok) {
                        caches.open(STATIC_CACHE).then((cache) => cache.put(request, response.clone()));
                    }
                    return response;
                })
                .catch(() => cachedResponse || Response.error());

            return cachedResponse || networkResponse;
        })
    );
});
