<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notification;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $role = $user->role;

                // Ambil notifikasi untuk User ID ini ATAU untuk Role User ini
                $query = Notification::where(function($q) use ($user, $role) {
                    $q->where('user_id', $user->id)
                      ->orWhere('role_target', $role);
                });

                $notifications = (clone $query)->latest()->take(5)->get();
                $unreadCount   = (clone $query)->where('is_read', 0)->count();

                $view->with([
                    'notifications' => $notifications,
                    'unreadCount'   => $unreadCount,
                ]);
            }
        });
    }
}
