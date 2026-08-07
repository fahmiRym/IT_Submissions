{{--
    Firebase Cloud Messaging — Web Push init (client side).
    Register Service Worker + minta permission + kirim FCM token ke backend.
    Include di layouts/app.blade.php (setelah user login).
--}}

@auth
@if(!empty(config('services.fcm.web.api_key')) && !empty(config('services.fcm.web.vapid_key')))
<script src="https://www.gstatic.com/firebasejs/10.13.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.13.2/firebase-messaging-compat.js"></script>
<script>
(function () {
    if (!('serviceWorker' in navigator) || !('Notification' in window)) return;

    @php
        $__fbCfg = [
            'apiKey'            => config('services.fcm.web.api_key'),
            'authDomain'        => config('services.fcm.web.auth_domain'),
            'projectId'         => config('services.fcm.web.project_id'),
            'storageBucket'     => config('services.fcm.web.storage_bucket'),
            'messagingSenderId' => config('services.fcm.web.messaging_sender_id'),
            'appId'             => config('services.fcm.web.app_id'),
        ];
    @endphp
    const FIREBASE_CONFIG = {!! json_encode($__fbCfg) !!};
    const VAPID_KEY  = {!! json_encode(config('services.fcm.web.vapid_key')) !!};
    const REGISTER_URL = "{{ route('web-push.register') }}";
    const CSRF = "{{ csrf_token() }}";

    firebase.initializeApp(FIREBASE_CONFIG);
    const messaging = firebase.messaging();

    async function registerWebPush() {
        try {
            const reg = await navigator.serviceWorker.register('/firebase-messaging-sw.js', { scope: '/' });

            const perm = await Notification.requestPermission();
            if (perm !== 'granted') return;

            const token = await messaging.getToken({
                vapidKey: VAPID_KEY,
                serviceWorkerRegistration: reg,
            });
            if (!token) return;

            // Register token ke backend (idempotent — upsert by token)
            const lastSent = localStorage.getItem('itsub_web_push_token');
            if (lastSent === token) return; // sudah pernah register

            await fetch(REGISTER_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ token: token }),
            });
            localStorage.setItem('itsub_web_push_token', token);
        } catch (e) {
            console.log('Web push init gagal:', e.message);
        }
    }

    // Trigger init on first user interaction (permission API perlu user gesture di beberapa browser)
    if (Notification.permission === 'granted') {
        registerWebPush();
    } else if (Notification.permission === 'default') {
        document.addEventListener('click', function once() {
            registerWebPush();
            document.removeEventListener('click', once);
        }, { once: true });
    } else if (Notification.permission === 'denied') {
        console.warn('[WebPush] Notifikasi DIBLOKIR. Chrome → icon 🔒 sebelah URL → Notifications → Allow, lalu refresh.');
    }

    // Diagnostic tool — jalankan di console browser utk cek status:
    //   itsubWebPushStatus()
    window.itsubWebPushStatus = async function () {
        const out = {
            permission: Notification.permission,
            serviceWorker: 'unregistered',
            fcmToken: null,
            localStorageToken: localStorage.getItem('itsub_web_push_token'),
        };
        try {
            const reg = await navigator.serviceWorker.getRegistration('/');
            if (reg) out.serviceWorker = 'registered (' + (reg.active ? 'active' : 'installing/waiting') + ')';
        } catch (e) {}
        try {
            const token = await messaging.getToken({ vapidKey: VAPID_KEY });
            out.fcmToken = token ? (token.substring(0, 20) + '...') : 'null';
        } catch (e) { out.fcmToken = 'ERROR: ' + e.message; }
        console.table(out);
        return out;
    };

    // Foreground message handler — saat browser tab AKTIF, FCM tidak auto-fire OS notif.
    // NOTE: Sound TIDAK di-play di sini — poller widget (_share_inbox_widget) sudah handle
    // sound, kalau keduanya play akan tumpuk. FCM foreground hanya fire OS notif.
    messaging.onMessage(async function (payload) {
        console.log('[FCM] foreground message diterima:', payload);
        if (Notification.permission !== 'granted') return;
        try {
            const reg = await navigator.serviceWorker.ready;
            const notifData = (payload.notification || {});
            const dataPayload = (payload.data || {});
            await reg.showNotification(
                notifData.title || 'IT Submissions',
                {
                    body:  notifData.body || '',
                    icon:  '/img/logo.png',
                    badge: '/img/logo.png',
                    tag:   dataPayload.tag || ('itsub-' + Date.now()),
                    data:  dataPayload,
                    requireInteraction: false,
                }
            );
            console.log('[FCM] showNotification foreground fired');
        } catch (e) { console.log('[FCM] showNotification (foreground) gagal:', e.message); }
    });

    // Terima pesan dari service worker (klik notif desktop → buka modal share)
    navigator.serviceWorker.addEventListener('message', function (event) {
        if (event.data && event.data.type === 'open-share-modal' && event.data.share_id) {
            if (typeof window.openShareInboxModal === 'function') {
                window.openShareInboxModal(event.data.share_id);
            }
        }
    });
})();
</script>
@endif
@endauth
