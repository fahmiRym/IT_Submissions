<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $paginatedNotifications = Notification::where('user_id', auth()->id())
            ->where('role_target', 'admin')
            ->latest()
            ->paginate(15);

        return view('notifications.admin.index', compact('paginatedNotifications'));
    }

    public function read(Notification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);

        $notification->update(['is_read' => 1]);
        return back();
    }
}
