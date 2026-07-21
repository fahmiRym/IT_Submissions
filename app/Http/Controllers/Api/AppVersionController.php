<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint publik untuk cek versi APK Android (auto-update).
 *
 * Public karena Android splash panggil SEBELUM login — user belum punya token.
 * Response ringan (no DB heavy). Aman di-cache di CDN.
 *
 * Endpoint:
 *   GET /api/mobile/version?app=itsubmissions   → info versi terbaru
 *   GET /api/mobile/versions                    → list semua app registered (opsional)
 */
class AppVersionController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $slug = trim((string) $request->query('app', 'itsubmissions'));
        $slug = strtolower($slug);

        $ver = AppVersion::where('app_slug', $slug)->first();

        if (!$ver) {
            return response()->json([
                'success' => false,
                'message' => "Info versi untuk app '{$slug}' tidak tersedia. Register dulu di panel superadmin.",
            ], 404);
        }

        return response()->json([
            'success'          => true,
            'app_slug'         => $ver->app_slug,
            'app_name'         => $ver->app_name,
            'latest_version'   => $ver->latest_version,
            'version_code'     => $ver->version_code,
            'apk_url'          => $ver->apk_url,
            'force_update'     => $ver->force_update,
            'changelog'        => $ver->changelog,
            'file_size'        => $ver->file_size,
            'file_hash'        => $ver->file_hash,
            'updated_at'       => optional($ver->updated_at)->toIso8601String(),
        ]);
    }

    public function index(): JsonResponse
    {
        $versions = AppVersion::orderBy('app_slug')->get()->map(fn($v) => [
            'app_slug'        => $v->app_slug,
            'app_name'        => $v->app_name,
            'latest_version'  => $v->latest_version,
            'version_code'    => $v->version_code,
            'apk_url'         => $v->apk_url,
            'force_update'    => $v->force_update,
            'updated_at'      => optional($v->updated_at)->toIso8601String(),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $versions,
        ]);
    }
}
