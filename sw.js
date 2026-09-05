const CACHE_NAME = 'tokoku-v2';
const ASSETS = [
    '/',
    '/login.php',
    '/dashboard.php',
    '/kasir.php',
    '/products.php',
    '/purchases.php',
    '/finance.php',
    '/reports.php',
    '/settings.php',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'assets/css/app.css',
    'assets/js/app.js',
    'assets/js/kasir.js',
    'assets/js/kasir2.js',
    'assets/js/kasir3.js',
    'assets/js/products.js',
    'assets/js/purchases.js',
    'assets/js/finance.js',
    'assets/js/settings.js'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS)).catch(() => {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    // Ignore API calls and non-GET
    if (event.request.method !== 'GET' || event.request.url.includes('api/')) {
        return;
    }
    const isStatic = /\.(css|js|png|jpg|jpeg|svg|ico|woff2?)$/.test(new URL(event.request.url).pathname);

    if (isStatic) {
        // Static assets: cache-first
        event.respondWith(
            caches.match(event.request).then(cached =>
                cached || fetch(event.request).then(res => {
                    const copy = res.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
                    return res;
                })
            )
        );
    } else {
        // Pages: network-first (always fresh when online, cache only as offline fallback)
        event.respondWith(
            fetch(event.request).then(res => {
                if (res.status === 200 && res.type === 'basic') {
                    const copy = res.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy));
                }
                return res;
            }).catch(() => caches.match(event.request).then(r => r || caches.match('/')))
        );
    }
});
