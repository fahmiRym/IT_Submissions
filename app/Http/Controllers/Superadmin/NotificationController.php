<?php

namespace App\Http\Controllers\Superadmin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $paginatedNotifications = Notification::where('role_target', 'superadmin')
            ->latest()
            ->paginate(15);

        return view('notifications.superadmin.index', compact('paginatedNotifications'));
    }

    public function read(Notification $notification)
    {
        $notification->update(['is_read' => 1]);
        return back();
    }
}
