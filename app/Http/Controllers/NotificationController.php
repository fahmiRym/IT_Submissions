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
        // - title mengandung 'baru' / 'pengajuan' → sound "new" (notif.mp3)
        // - title mengandung 'update'/'perubahan'/'edit' → sound "update" (update_notif.mp3)
        $latest = (clone $baseQ)->latest()->first(['id', 'title']);
        $type = 'new';
        if ($latest) {
            $t = mb_strtolower($latest->title ?? '');
            if (str_contains($t, 'update') || str_contains($t, 'perubahan') || str_contains($t, 'edit') || str_contains($t, 'diperbarui')) {
                $type = 'update';
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
