<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Services\FcmService;
use Illuminate\Http\Request;

class FcmController extends Controller
{
    /**
     * Daftarkan / perbarui FCM token milik perangkat user yang login.
     * Dipanggil Android setelah login & saat token di-refresh oleh Firebase.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:255',
            'platform' => 'nullable|string|max:50',
            'device_name' => 'nullable|string|max:255',
        ]);

        // updateOrCreate berdasarkan token (unik). Jika token pernah dipakai
        // user lain (mis. ganti akun di HP yang sama), kepemilikan dipindah.
        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $validated['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $validated['platform'] ?? 'android',
                'device_name' => $validated['device_name'] ?? null,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Token perangkat berhasil didaftarkan',
            'data' => $deviceToken,
        ], 200);
    }

    /**
     * Hapus token perangkat (dipanggil saat logout / uninstall).
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        DeviceToken::where('token', $validated['token'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Token perangkat berhasil dihapus',
        ], 200);
    }

    /**
     * Kirim test push ke perangkat user yang sedang login.
     * Berguna untuk memverifikasi setup FCM dari aplikasi.
     */
    public function test(Request $request, FcmService $fcm)
    {
        if (! $fcm->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'FCM belum dikonfigurasi di server (file service account belum ada).',
            ], 503);
        }

        $tokenCount = DeviceToken::where('user_id', $request->user()->id)->count();
        if ($tokenCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada token perangkat terdaftar untuk akun ini.',
            ], 404);
        }

        $fcm->sendToUser(
            $request->user()->id,
            'Test Notifikasi',
            'Push notification FCM berhasil dikirim 🎉',
            ['type' => 'test']
        );

        return response()->json([
            'success' => true,
            'message' => "Test push dikirim ke {$tokenCount} perangkat.",
        ], 200);
    }
}
