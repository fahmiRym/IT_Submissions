<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Superadmin — kelola versi APK Android untuk mekanisme auto-update.
 *
 * User bisa:
 *   - List semua app registered
 *   - Tambah baris app baru (mis. daftarkan `itapproval`, `itasistant`, dst)
 *   - Update metadata (latest_version, version_code, changelog, force_update, apk_url_override)
 *   - Upload APK ke storage/app/public/apk/ (auto-hitung sha256)
 *   - Hapus baris (soft-delete tidak diaktifkan; hard delete + file)
 */
class AppVersionController extends Controller
{
    public function index()
    {
        $versions = AppVersion::orderBy('app_slug')->get();
        return view('superadmin.app_versions.index', compact('versions'));
    }

    /**
     * Tambah/update metadata sekaligus (form combined create+update).
     */
    public function upsert(Request $request)
    {
        $data = $request->validate([
            'app_slug'          => 'required|string|max:40|regex:/^[a-z0-9_-]+$/',
            'app_name'          => 'required|string|max:100',
            'latest_version'    => 'required|string|max:20',
            'version_code'      => 'required|integer|min:1',
            'apk_url_override'  => 'nullable|url|max:500',
            'force_update'      => 'nullable|boolean',
            'changelog'         => 'nullable|string|max:5000',
        ]);
        $data['force_update'] = $request->boolean('force_update');

        $ver = AppVersion::where('app_slug', $data['app_slug'])->first();
        $wasNew = !$ver;
        $oldValues = $ver?->toArray();

        if (!$ver) {
            $data['uploaded_by'] = auth()->id();
            $ver = AppVersion::create($data);
        } else {
            $ver->update($data);
        }

        $this->audit($wasNew ? 'app_version.create' : 'app_version.update', $ver, $oldValues);

        return back()->with('success',
            $wasNew
                ? "App '{$ver->app_name}' berhasil didaftarkan (v{$ver->latest_version})."
                : "App '{$ver->app_name}' diperbarui ke v{$ver->latest_version}."
        );
    }

    /**
     * Upload file APK — auto-hitung file_size + sha256.
     */
    public function uploadApk(Request $request, $id)
    {
        $request->validate([
            'apk_file' => 'required|file|mimetypes:application/vnd.android.package-archive,application/octet-stream|max:204800', // 200 MB
        ]);

        $ver = AppVersion::findOrFail($id);
        $file = $request->file('apk_file');

        // Hapus APK lama kalau ada
        if ($ver->apk_path && Storage::disk('public')->exists($ver->apk_path)) {
            Storage::disk('public')->delete($ver->apk_path);
        }

        $filename = "{$ver->app_slug}-{$ver->latest_version}-{$ver->version_code}.apk";
        $path = $file->storeAs('apk', $filename, 'public');

        $ver->update([
            'apk_path'       => $path,
            'apk_url_override' => null, // reset override — pakai path lokal
            'file_size'      => $file->getSize(),
            'file_hash'      => hash_file('sha256', Storage::disk('public')->path($path)),
        ]);

        $this->audit('app_version.upload_apk', $ver, ['filename' => $filename, 'size' => $ver->file_size]);

        return back()->with('success', "APK {$filename} ter-upload (" . round($ver->file_size / 1024 / 1024, 2) . " MB).");
    }

    /**
     * Hapus baris app_version + file APK.
     */
    public function destroy($id)
    {
        $ver = AppVersion::findOrFail($id);

        if ($ver->apk_path && Storage::disk('public')->exists($ver->apk_path)) {
            Storage::disk('public')->delete($ver->apk_path);
        }
        $slug = $ver->app_slug;
        $this->audit('app_version.delete', $ver, $ver->toArray());
        $ver->delete();

        return back()->with('success', "App '{$slug}' dihapus.");
    }

    /**
     * Ringan-write audit log ke laravel.log (kalau ada tabel audit_logs bisa diperluas nanti).
     */
    private function audit(string $action, AppVersion $ver, $extra = null): void
    {
        Log::info("[AppVersion] {$action}", [
            'actor' => auth()->id(),
            'actor_name' => auth()->user()?->name,
            'app_slug' => $ver->app_slug,
            'version' => "{$ver->latest_version}({$ver->version_code})",
            'extra' => $extra,
        ]);
    }
}
