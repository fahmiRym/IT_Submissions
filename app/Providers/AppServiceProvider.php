<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

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
        // Force HTTPS di server produksi (mencegah Mixed Content Error di browser)
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            // Data pengaturan global
            $view->with([
                'app_logo' => Setting::get('app_logo'),
                'app_name' => Setting::get('app_name', config('app.name')),
            ]);

            // Data notifikasi untuk user yang login
            if (auth()->check()) {
                $user = auth()->user();
                $role = $user->role;

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
