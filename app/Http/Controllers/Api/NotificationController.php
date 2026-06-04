<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Bangun query notifikasi yang relevan untuk user yang login.
     *
     * - superadmin : notifikasi dengan role_target = 'superadmin' (kotak bersama)
     * - lainnya    : notifikasi milik user tsb (user_id)
     */
    private function scopedQuery(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'superadmin') {
            return Notification::where('role_target', 'superadmin');
        }

        return Notification::where('user_id', $user->id);
    }

    /**
     * Daftar notifikasi (paginated) untuk Android.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $notifications = $this->scopedQuery($request)
            ->with('arsip:id,no_registrasi,jenis_pengajuan,ket_process')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar notifikasi berhasil diambil',
            'data' => $notifications,
        ], 200);
    }

    /**
     * Jumlah notifikasi belum dibaca (untuk badge).
     */
    public function unreadCount(Request $request)
    {
        $count = $this->scopedQuery($request)->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ], 200);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $this->scopedQuery($request)->findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca',
        ], 200);
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        $this->scopedQuery($request)->where('is_read', false)->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sudah dibaca',
        ], 200);
    }
}
