// Firebase Cloud Messaging — Service Worker (WEB PUSH)
// File ini harus ada di root public/ supaya scope-nya '/'.
// Browser akan menjalankan ini di background thread — bahkan saat semua tab
// aplikasi di-close, browser masih menerima push dan menampilkan notifikasi.
//
// PENTING: config di sini di-hardcode karena Service Worker tidak bisa
// akses env server-side. Values ini PUBLIC (aman di-expose ke client).

importScripts('https://www.gstatic.com/firebasejs/10.13.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.13.2/firebase-messaging-compat.js');

// ── Firebase config diinject saat build oleh route /firebase-messaging-sw-config.js ──
// Alternative: fetch config from same origin
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (e) => e.waitUntil(self.clients.claim()));

// Fetch config dari endpoint dinamis (server-rendered dari env)
async function initFirebase() {
    try {
        const res = await fetch('/api/web-push/config');
        if (!res.ok) throw new Error('config fetch failed');
        const cfg = await res.json();
        if (!cfg || !cfg.apiKey) return;

        firebase.initializeApp({
            apiKey:            cfg.apiKey,
            authDomain:        cfg.authDomain,
            projectId:         cfg.projectId,
            storageBucket:     cfg.storageBucket,
            messagingSenderId: cfg.messagingSenderId,
            appId:             cfg.appId,
        });

        const messaging = firebase.messaging();

        // Background message handler — muncul saat browser di-background/closed
        messaging.onBackgroundMessage(function (payload) {
            const title = (payload.notification && payload.notification.title) || 'IT Submissions';
            const body  = (payload.notification && payload.notification.body)  || '';
            const data  = payload.data || {};

            self.registration.showNotification(title, {
                body: body,
                icon: '/img/logo.png',
                badge: '/img/logo.png',
                tag:  data.tag || ('itsub-' + Date.now()),
                requireInteraction: false,
                data: data,
            });
        });
    } catch (e) {
        // silent — kalau config belum di-set, service worker tetap running tapi tidak init
    }
}

initFirebase();

// Klik notifikasi → fokus tab / buka URL
self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.click_url) || '/';
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (windowClients) {
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                if (client.url.indexOf(self.location.origin) === 0) {
                    return client.focus().then(function () {
                        // Kirim message ke tab supaya buka modal share detail
                        if (event.notification.data && event.notification.data.share_id) {
                            client.postMessage({
                                type: 'open-share-modal',
                                share_id: event.notification.data.share_id,
                            });
                        }
                    });
                }
            }
            if (clients.openWindow) return clients.openWindow(url);
        })
    );
});
