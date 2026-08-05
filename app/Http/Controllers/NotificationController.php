<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function checkUnread()
    {
        $user = Auth::user();

        $baseQ = Notification::where('role_target', $user->role)->where('is_read', false);
        $unreadCount = (clone $baseQ)->count();

        // Latest notif untuk sound differentiation:
        // - "Pengajuan Baru" → notif.mp3 (pengajuan baru masuk)
        // - Lainnya (Update Tahap, Pengajuan Selesai/Dibatalkan/Ditunda/dsb) → update_notif.mp3
        $latest = (clone $baseQ)->latest()->first(['id', 'title']);
        $type = 'update'; // default: semua perubahan status
        if ($latest) {
            $t = mb_strtolower(trim($latest->title ?? ''));
            // Hanya title EXACT "pengajuan baru" yang dianggap "new"
            if ($t === 'pengajuan baru' || str_contains($t, 'baru')) {
                $type = 'new';
            }
        }

        return response()->json([
            'unreadCount'    => $unreadCount,
            'latestNotifId'  => optional($latest)->id,
            'notifType'      => $type,   // 'new' | 'update'
        ]);
    }

    public function index()
    {
        $user = Auth::user();

        // Redirect to specific routes/views based on role
        if ($user->role === 'superadmin') {
            return redirect()->route('superadmin.notifications.index');
        } elseif ($user->role === 'admin') {
            return redirect()->route('admin.notifications.index');
        }

        // Fallback for regular users
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function read(Notification $notification)
    {
        abort_if($notification->user_id !== auth()->id()
            && auth()->user()->role !== 'superadmin', 403);

        $notification->update(['is_read' => 1]);

        return back();
    }
}
