<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        return view('superadmin.settings.index', [
            'app_logo' => Setting::get('app_logo'),
            'app_name' => Setting::get('app_name', config('app.name')),
            'kota_ba' => Setting::get('kota_ba', 'PASURUAN'),
            'wm_done' => Setting::get('wm_done', 'DONE'),
            'wm_void' => Setting::get('wm_void', 'VOID'),
            'wm_reject' => Setting::get('wm_reject', 'REJECT'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'kota_ba' => 'nullable|string|max:100',
            'wm_done' => 'nullable|string|max:50',
            'wm_void' => 'nullable|string|max:50',
            'wm_reject' => 'nullable|string|max:50',
        ]);

        // Log data yang masuk untuk debugging
        \Log::info("Update Settings Request:", $request->all());

        // 1. Update Nama Aplikasi (Gunakan DB langsung agar lebih pasti)
        \DB::table('settings')->updateOrInsert(['key' => 'app_name'], ['value' => $request->app_name, 'updated_at' => now()]);

        // 2. Update Kota BA
        \DB::table('settings')->updateOrInsert(['key' => 'kota_ba'], ['value' => strtoupper($request->kota_ba ?? 'PASURUAN'), 'updated_at' => now()]);

        // 3. Update Watermarks
        $wms = ['wm_done', 'wm_void', 'wm_reject'];
        foreach ($wms as $wm) {
            \DB::table('settings')->updateOrInsert(['key' => $wm], ['value' => strtoupper($request->$wm ?? ''), 'updated_at' => now()]);
        }

        // 2. Update Logo Jika Ada
        if ($request->hasFile('app_logo')) {
            // Hapus logo lama
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete('settings/' . $oldLogo);
            }

            // Simpan logo baru
            $file = $request->file('app_logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();

            // Simpan ke storage (public disk)
            $file->storeAs('settings', $filename, 'public');

            // Tambahan: Juga copy ke public/favicon.ico untuk menjamin tab PDF menampilkan logo brand
            // Kita gunakan copy() ke path fisik public/favicon.ico
            try {
                copy($file->getRealPath(), public_path('favicon.ico'));
            } catch (\Exception $e) {
                // Jangan gagalkan proses utama jika penulisan favicon.ico gagal (misal: permission)
                \Log::error("Gagal mengupdate root favicon.ico: " . $e->getMessage());
            }

            \DB::table('settings')->updateOrInsert(['key' => 'app_logo'], ['value' => $filename, 'updated_at' => now()]);
        }

        return back()->with('success', 'Pengaturan aplikasi berhasil diperbarui');
    }
}
