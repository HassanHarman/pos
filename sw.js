const CACHE_NAME = 'pos-pwa-v1';
const CORE_ASSETS = [
  '/pos/',
  '/pos/index.php',
  '/pos/manifest.webmanifest',
  '/pos/pwa.js',
  '/pos/favicon/favicon.ico',
  '/pos/favicon/favicon-16x16.png',
  '/pos/favicon/favicon-32x32.png',
  '/pos/favicon/apple-touch-icon.png',
  '/pos/favicon/android-chrome-192x192.png',
  '/pos/favicon/android-chrome-512x512.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(CORE_ASSETS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  event.respondWith(
    caches.match(event.request).then((cached) =>
      cached || fetch(event.request).then((response) => {
        if (response && response.ok && response.type === 'basic') {
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
        }
        return response;
      }).catch(() => cached)
    )
  );
});
