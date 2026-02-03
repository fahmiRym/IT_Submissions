<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
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
