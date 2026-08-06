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
    }

    // Foreground message handler — saat browser tab AKTIF, FCM tidak fire notification popup.
    // Handle manual supaya user tetap dapat sinyal (toast + sound).
    messaging.onMessage(function (payload) {
        try {
            const audio = document.getElementById('share-inbox-sound');
            if (audio) { audio.currentTime = 0; audio.play().catch(() => {}); }
        } catch (e) {}
        // Optional: toast rendered by _share_inbox_widget saat poller berikutnya jalan
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
