<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use Illuminate\Http\Request;

class WebPushController extends Controller
{
    /**
     * Serve Firebase Web SDK config ke Service Worker.
     * Values dari .env — client-side public config (bukan secret).
     */
    public function config()
    {
        return response()->json([
            'apiKey'            => config('services.fcm.web.api_key'),
            'authDomain'        => config('services.fcm.web.auth_domain'),
            'projectId'         => config('services.fcm.web.project_id'),
            'storageBucket'     => config('services.fcm.web.storage_bucket'),
            'messagingSenderId' => config('services.fcm.web.messaging_sender_id'),
            'appId'             => config('services.fcm.web.app_id'),
        ])->header('Cache-Control', 'public, max-age=300'); // cache 5 menit di CDN/browser
    }

    /**
     * Register FCM web token utk user login (upsert idempotent).
     * Dipanggil dari _web_push_init.blade.php setelah dapat token.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string|max:512',
        ]);

        $user = auth()->user();
        if (!$user) return response()->json(['error' => 'unauthenticated'], 401);

        // Upsert: kalau token sudah ada, update owner + last_used. Kalau belum, create baru.
        DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id'      => $user->id,
                'platform'     => 'web',
                'device_name'  => substr($request->userAgent() ?? 'browser', 0, 190),
                'last_used_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }
}
