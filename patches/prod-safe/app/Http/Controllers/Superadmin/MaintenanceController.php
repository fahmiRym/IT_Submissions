<?php

/**
 * ============================================================================
 * MAINTENANCE MODE UI CONTROLLER — Superadmin
 * ----------------------------------------------------------------------------
 * Fungsi: superadmin bisa toggle Laravel maintenance mode (down/up) via UI,
 * tanpa perlu SSH ke server.
 *
 * Mekanisme: pakai Laravel built-in Artisan::call('down' / 'up').
 * Saat down = Laravel serve 503 → nginx catch (fallback config sudah aktif
 * per Paket A) → user lihat public/maintenance.html (dark theme).
 * Saat up = Laravel back to normal.
 *
 * NON-MIGRATION: tidak butuh tabel baru, cuma manipulate file
 * storage/framework/down yang Laravel manage internally.
 *
 * DEPLOY: SCP ke /root/it_submissions/app/Http/Controllers/Superadmin/MaintenanceController.php
 *         + add 2 route di routes/web.php (GET index + POST toggle)
 *         + add menu di sidebar superadmin
 * ============================================================================
 */

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;

class MaintenanceController extends Controller
{
    /**
     * Halaman status + toggle maintenance mode.
     */
    public function index()
    {
        return view('superadmin.maintenance.index', [
            'isDown' => App::isDownForMaintenance(),
            'downFile' => storage_path('framework/down'),
        ]);
    }

    /**
     * Aktifkan maintenance mode.
     * Optional: refresh (auto-reload browser interval), retry (HTTP header),
     * secret (bypass URL untuk superadmin sendiri).
     */
    public function enable(Request $request)
    {
        $request->validate([
            'refresh' => 'nullable|integer|min:0|max:3600',
            'retry'   => 'nullable|integer|min:0|max:86400',
        ]);

        $options = [];

        if ($request->filled('refresh')) {
            $options['--refresh'] = (int) $request->refresh;
        }
        if ($request->filled('retry')) {
            $options['--retry'] = (int) $request->retry;
        }
        // Generate secret token supaya superadmin sendiri bisa bypass via
        // /{secret} URL. Simpan token di session flash supaya user bisa copy.
        $secret = bin2hex(random_bytes(16));
        $options['--secret'] = $secret;

        Artisan::call('down', $options);

        return redirect()
            ->route('superadmin.maintenance.index')
            ->with('success', 'Maintenance mode AKTIF. Semua request non-superadmin akan lihat halaman maintenance.')
            ->with('bypass_secret', $secret)
            ->with('bypass_url', url('/' . $secret));
    }

    /**
     * Nonaktifkan maintenance mode.
     */
    public function disable()
    {
        Artisan::call('up');

        return redirect()
            ->route('superadmin.maintenance.index')
            ->with('success', 'Maintenance mode NONAKTIF. Aplikasi kembali normal.');
    }
}
