<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator;

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
        // Auto-detect HTTPS dari Cloudflare Tunnel / Reverse Proxy
        // Cloudflare mengirim header X-Forwarded-Proto: https
        // Saat di local (HTTP biasa), header ini tidak ada jadi tidak di-force
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }

        // Force Bootstrap 5 pagination globally (kita pakai Bootstrap 5, bukan Tailwind)
        Paginator::useBootstrapFive();

        // ── Gate: siapa yang boleh melihat HARGA / NILAI RUPIAH ──
        // Lihat aturan di App\Models\User::canViewPrice() — satu source of truth.
        Gate::define('view-price', fn ($user) => $user->canViewPrice());

        View::composer('*', function ($view) {
            // Data pengaturan global
            $logo = Setting::get('app_logo');
            // Feature flag: Produk Baru bisa di-disable sementara dari Settings.
            // Default ON ('1'). Bila '0' → semua UI/menu/filter/form opsi Produk_Baru disembunyikan.
            $produkBaruEnabled = Setting::get('produk_baru_enabled', '1') === '1';
            $view->with([
                'app_logo' => $logo,
                'app_logo_url' => $logo ? asset('storage/settings/' . $logo . '?v=' . filemtime(public_path('storage/settings/' . $logo))) : asset('img/logo.png'),
                'app_name' => Setting::get('app_name', config('app.name')),
                'produkBaruEnabled' => $produkBaruEnabled,
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
                    'pendingApprovalCount' => \App\Models\ArsipApproval::pendingCountFor($user),
                ]);
            }
        });
    }
}
