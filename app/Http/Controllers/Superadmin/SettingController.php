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
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:100',
            'app_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        // 1. Update Nama Aplikasi
        Setting::set('app_name', $request->app_name);

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
            $file->storeAs('settings', $filename, 'public');

            Setting::set('app_logo', $filename);
        }

        return back()->with('success', 'Pengaturan aplikasi berhasil diperbarui');
    }
}
