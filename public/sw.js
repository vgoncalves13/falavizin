const STATIC_CACHE = 'falavizin-static-v1';
const OFFLINE_URL = '/offline.html';
const STATIC_ASSETS = [
    OFFLINE_URL,
    '/assets/icons/icon-192.png',
    '/assets/icons/icon-512.png',
    '/assets/icons/badge-96.png',
    '/assets/images/logo.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(STATIC_CACHE).then((cache) => cache.addAll(STATIC_ASSETS)));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('falavizin-static-') && key !== STATIC_CACHE)
                    .map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    if (
        request.method !== 'GET'
        || request.mode !== 'navigate'
        || new URL(request.url).origin !== self.location.origin
    ) {
        return;
    }

    event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
});

self.addEventListener('push', (event) => {
    let payload = {};

    try {
        payload = event.data?.json() ?? {};
    } catch {
        payload = {
            title: 'FalaVizin',
            body: 'Você recebeu uma nova notificação.',
        };
    }

    const title = payload.title || 'FalaVizin';
    const options = {
        body: payload.body || '',
        icon: payload.icon || '/assets/icons/icon-192.png',
        badge: payload.badge || '/assets/icons/badge-96.png',
        tag: payload.tag,
        data: payload.data || { url: '/' },
        actions: payload.actions || [],
        renotify: payload.renotify || false,
        requireInteraction: payload.requireInteraction || false,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    let targetUrl = new URL('/', self.location.origin);

    try {
        const requestedUrl = new URL(event.notification.data?.url || '/', self.location.origin);

        if (requestedUrl.origin === self.location.origin) {
            targetUrl = requestedUrl;
        }
    } catch {
        targetUrl = new URL('/', self.location.origin);
    }

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(async (clients) => {
            const existingClient = clients.find((client) => new URL(client.url).origin === self.location.origin);

            if (existingClient) {
                await existingClient.focus();

                return existingClient.navigate(targetUrl.href);
            }

            return self.clients.openWindow(targetUrl.href);
        }),
    );
});
