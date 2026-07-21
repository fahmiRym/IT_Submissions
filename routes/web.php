<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Auth\LoginController;

/* ADMIN IMPORTS */
// Gunakan alias agar rapi
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\ArsipController as AdminArsip;
use App\Http\Controllers\Admin\ProfileController as AdminProfile;
use App\Http\Controllers\Admin\NotificationController as AdminNotification;

/* SUPERADMIN IMPORTS */
use App\Http\Controllers\Superadmin\DashboardController as SuperDashboard;
use App\Http\Controllers\Superadmin\ArsipController as SuperArsip;
use App\Http\Controllers\Superadmin\DepartmentController as SuperDepartment;
use App\Http\Controllers\Superadmin\LocationController as SuperLocation;
use App\Http\Controllers\Superadmin\UnitController as SuperUnit;
use App\Http\Controllers\Superadmin\ManagerController as SuperManager;
use App\Http\Controllers\Superadmin\UserController as SuperUser;
use App\Http\Controllers\Superadmin\ProfileController as SuperProfile;
use App\Http\Controllers\Superadmin\LaporanController as SuperLaporan;
use App\Http\Controllers\Superadmin\NotificationController as SuperNotification;
use App\Http\Controllers\Superadmin\BackupController as SuperBackup;
use App\Http\Controllers\Superadmin\SettingController as SuperSetting;
use App\Http\Controllers\Superadmin\ProductController as SuperProduct;
use App\Http\Controllers\Superadmin\ActivityLogController as SuperActivity;
use App\Http\Controllers\Superadmin\ServerStatController as SuperServer;

// Shared Notification Controller (Jika dipakai di middleware auth umum)
use App\Http\Controllers\NotificationController; 

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class,'showLogin'])->name('login');
Route::post('/login', [LoginController::class,'loginProcess'])->name('login.process');
Route::post('/logout', [LoginController::class,'logout'])->name('logout');

/* AUTH SETUP — link NIK + ganti password (wajib untuk user lama) */
Route::middleware('auth')->group(function () {
    Route::get('/link-nik', [\App\Http\Controllers\Auth\AccountSetupController::class, 'showLinkNik'])->name('auth.link-nik');
    Route::post('/link-nik', [\App\Http\Controllers\Auth\AccountSetupController::class, 'linkNik'])->name('auth.link-nik.submit');
    Route::get('/change-password', [\App\Http\Controllers\Auth\AccountSetupController::class, 'showChangePassword'])->name('auth.change-password');
    Route::post('/change-password', [\App\Http\Controllers\Auth\AccountSetupController::class, 'changePassword'])->name('auth.change-password.submit');
});

/*
|--------------------------------------------------------------------------
| ROOT & DEBUG
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

// VERIFIKASI TANDA TANGAN DIGITAL (publik, via QR) — token UUID tidak bisa ditebak
Route::get('/verify/{token}', [\App\Http\Controllers\VerificationController::class, 'show'])
    ->name('verify.show');

// FILE PREVIEW — Tidak perlu auth karena filename tidak bisa ditebak
// Ini agar preview bisa jalan di HTTP maupun HTTPS tanpa masalah session cookie
Route::get('/preview-file/{filename}', function ($filename) {
    // Hanya izinkan nama file yang aman (alfanumeric, -, _, .)
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
        abort(403);
    }

    $path = storage_path('app/public/bukti_scan/' . $filename);

    if (!file_exists($path)) {
        \Illuminate\Support\Facades\Log::warning("File Bukti Scan 404: " . $path);
        abort(404);
    }

    $mime = mime_content_type($path);

    return response()->file($path, [
        'Content-Type'        => $mime,
        'Content-Disposition' => 'inline; filename="' . $filename . '"',
        'Cache-Control'       => 'private, max-age=3600',
    ]);
})->name('preview.file');

// PDF VIEWER — Menggunakan PDF.js lokal
Route::get('/pdf-viewer/{filename}', function ($filename) {
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
        abort(403);
    }

    $path = storage_path('app/public/bukti_scan/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $fileUrl = route('preview.file', ['filename' => $filename]);

    // Cari data arsip berdasarkan nama file (cek di bukti_scan, scan_ba_accounting, atau scan_final)
    $arsip = \App\Models\Arsip::where('bukti_scan', $filename)
                ->orWhere('scan_ba_accounting', $filename)
                ->orWhere('scan_final', $filename)
                ->first();

    return view('vendor.pdfjs.viewer', [
        'filename' => $filename,
        'fileUrl'  => $fileUrl,
        'arsip'    => $arsip
    ]);
})->name('pdf.viewer');

/*
|--------------------------------------------------------------------------
| COMMON AUTH AREA
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| COMMON AUTH AREA
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // User search untuk multi-pemohon picker (Tom-Select autocomplete)
    Route::get('/users/search', function (\Illuminate\Http\Request $request) {
        $q = trim((string) $request->get('q', ''));
        $query = \App\Models\User::query()
            ->where('is_active', true)
            ->whereNotNull('employee_id');
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('employee_id', 'like', "%{$q}%")
                  ->orWhere('name', 'like', "%{$q}%")
                  ->orWhere('username', 'like', "%{$q}%");
            });
        }
        $users = $query->with(['department:id,name', 'workUnit:id,name'])
            ->orderByRaw("CASE WHEN employee_id LIKE ? THEN 0 ELSE 1 END", ["{$q}%"])
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'employee_id', 'name', 'username', 'department_id', 'work_unit_id']);
        return response()->json([
            'data' => $users->map(fn($u) => [
                'id' => $u->id,
                'employee_id' => $u->employee_id,
                'name' => $u->name,
                'department' => $u->department?->name,
                'work_unit' => $u->workUnit?->name,
            ]),
        ]);
    })->name('users.search');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('/notifications/check', [NotificationController::class, 'checkUnread'])
        ->name('notifications.check');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');

    // Endpoint ringan untuk mengecek apakah ada perubahan data di tabel Arsip
    Route::get('/arsip/check-updates', function () {
        return response()->json([
            'last_update' => \App\Models\Arsip::max('updated_at'),
            'count'       => \App\Models\Arsip::count(),
        ]);
    })->name('arsip.check-updates');

    // ── ARSIP SHARES (common endpoint untuk admin + superadmin) ──
    // Controller meng-enforce permission: hanya superadmin yang boleh store/destroy.
    // Name di-namespace 'arsip.shares.*' (tetap supaya modal JS lama tidak putus).
    Route::get('arsip/{arsip}/shares', [\App\Http\Controllers\Admin\ArsipShareController::class, 'index'])->name('arsip.shares.index');
    Route::post('arsip/{arsip}/shares', [\App\Http\Controllers\Admin\ArsipShareController::class, 'store'])->name('arsip.shares.store');
    Route::delete('arsip/{arsip}/shares/{share}', [\App\Http\Controllers\Admin\ArsipShareController::class, 'destroy'])->name('arsip.shares.destroy');
    Route::get('share-user-search', [\App\Http\Controllers\Admin\ArsipShareController::class, 'searchUsers'])->name('arsip.shares.user-search');

    // ── PERSONAL NOTES per arsip — siapapun yang punya edit-access bisa add/edit/hapus catatan ──
    Route::get('arsip/{arsip}/notes', [\App\Http\Controllers\Admin\ArsipNoteController::class, 'index'])->name('arsip.notes.index');
    Route::post('arsip/{arsip}/notes', [\App\Http\Controllers\Admin\ArsipNoteController::class, 'store'])->name('arsip.notes.store');
    Route::put('arsip/{arsip}/notes/{note}', [\App\Http\Controllers\Admin\ArsipNoteController::class, 'update'])->name('arsip.notes.update');
    Route::delete('arsip/{arsip}/notes/{note}', [\App\Http\Controllers\Admin\ArsipNoteController::class, 'destroy'])->name('arsip.notes.destroy');

    // ── DASHBOARD POPUP: shared endpoint untuk popup tabel pengajuan dari card stat ──
    Route::get('dashboard/popup', [\App\Http\Controllers\DashboardStatController::class, 'popup'])->name('dashboard.popup');
});

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth','role:admin,accounting,spv,kabag,manager','ensure.nik','force.password'])
    ->name('admin.')
    ->group(function () {

        Route::get('dashboard', [AdminDashboard::class,'index'])
            ->name('dashboard');

        Route::resource('arsip', AdminArsip::class)
            ->only(['index', 'store', 'update', 'edit']); 

        Route::get('arsip/{id}/print-draft', [AdminArsip::class, 'printDraft'])->name('arsip.print-draft');

        // ✅ DETAIL PRODUK BARU (barcode, tgl dibuat, log)
        Route::get('arsip/{id}/produk-detail', [AdminArsip::class, 'produkDetail'])->name('arsip.produk-detail');

        // ✅ RE-UPLOAD BA SCAN (Khusus Accounting setelah Approve)
        Route::post('arsip/{id}/reupload-ba', [AdminArsip::class, 'reuploadBaScan'])->name('arsip.reupload-ba');

        // ✅ LAMPIRAN PDF (multi-file) + show document (draft + lampiran merged)
        Route::post('arsip/{id}/upload-lampiran', [AdminArsip::class, 'uploadLampiran'])->name('arsip.upload-lampiran');
        Route::get('arsip/{id}/lampiran', [AdminArsip::class, 'listLampiran'])->name('arsip.list-lampiran');
        Route::get('arsip/{arsip}/lampiran/{lampiran}/view', [AdminArsip::class, 'viewLampiran'])->name('arsip.view-lampiran');
        Route::delete('arsip/{arsip}/lampiran/{lampiran}', [AdminArsip::class, 'deleteLampiran'])->name('arsip.delete-lampiran');
        Route::get('arsip/{id}/show-document', [AdminArsip::class, 'showDocument'])->name('arsip.show-document');

        // ✅ PROFILE ADMIN
        Route::get('profile', [AdminProfile::class,'index'])->name('profile');
        Route::put('profile', [AdminProfile::class,'update'])->name('profile.update');
        Route::post('profile/signature', [AdminProfile::class,'updateSignature'])->name('profile.signature');

        // ✅ TANDA TANGAN DIGITAL pada pengajuan
        Route::post('arsip/{id}/sign', [AdminArsip::class, 'signArsip'])->name('arsip.sign');

        // ✅ MASTER HARGA (akses dijaga Gate 'view-price' di controller)
        Route::get('prices', [\App\Http\Controllers\Admin\PriceController::class, 'index'])->name('prices.index');
        Route::post('prices', [\App\Http\Controllers\Admin\PriceController::class, 'store'])->name('prices.store');
        Route::put('prices/{price}', [\App\Http\Controllers\Admin\PriceController::class, 'update'])->name('prices.update');
        Route::delete('prices/{price}', [\App\Http\Controllers\Admin\PriceController::class, 'destroy'])->name('prices.destroy');

        // ✅ ARSIP SHARED INBOX (di group admin agar middleware ensure.nik & force.password aktif)
        Route::get('shared-inbox', [\App\Http\Controllers\Admin\ArsipShareController::class, 'inbox'])->name('arsip.shared-inbox');

        // ✅ APPROVAL BERTINGKAT
        Route::get('approvals', [AdminArsip::class, 'myApprovals'])->name('approvals.index');
        Route::post('arsip/{id}/approve', [AdminArsip::class, 'approveArsip'])->name('arsip.approve');
        Route::post('arsip/{id}/reject', [AdminArsip::class, 'rejectArsip'])->name('arsip.reject');

        // ✅ NOTIFIKASI ADMIN
        Route::get('notifications', [AdminNotification::class,'index'])->name('notifications.index');
        Route::put('notifications/{notification}/read',[AdminNotification::class,'read'])->name('notifications.read');

    });

/*
|--------------------------------------------------------------------------
| SUPERADMIN AREA
|--------------------------------------------------------------------------
*/
Route::prefix('superadmin')
    ->middleware(['auth','role:superadmin'])
    ->name('superadmin.')
    ->group(function () {

        Route::get('dashboard', [SuperDashboard::class, 'index'])->name('dashboard');

        // ⚠️ HARUS sebelum Route::resource karena `arsip/{arsip}` show route bisa
        //    menelan path string seperti `arsip/search-simple`.
        Route::get('arsip/search-simple', [SuperArsip::class, 'searchSimple'])->name('arsip.search-simple');

        Route::resource('arsip', SuperArsip::class);

        // ✅ PROFILE SUPERADMIN
        Route::get('profile', [SuperProfile::class,'index'])->name('profile');
        Route::put('profile', [SuperProfile::class,'update'])->name('profile.update');
        Route::post('profile/signature', [SuperProfile::class,'updateSignature'])->name('profile.signature');

        // ✅ TANDA TANGAN DIGITAL pada pengajuan
        Route::post('arsip/{id}/sign', [SuperArsip::class, 'signArsip'])->name('arsip.sign');

        // ✅ APPROVAL BERTINGKAT
        Route::get('approvals', [SuperArsip::class, 'myApprovals'])->name('approvals.index');
        Route::post('arsip/{id}/approve', [SuperArsip::class, 'approveArsip'])->name('arsip.approve');
        Route::post('arsip/{id}/reject', [SuperArsip::class, 'rejectArsip'])->name('arsip.reject');

        Route::get('laporan/pdf-viewer', [SuperLaporan::class, 'pdfViewer'])->name('laporan.pdf-viewer');
        Route::get('laporan/pdf', [SuperLaporan::class, 'printPdf'])->name('laporan.pdf');
        Route::get('laporan', [SuperLaporan::class, 'index'])->name('laporan.index');
        
        // Notifikasi Superadmin
        Route::get('notifications',[SuperNotification::class,'index'])->name('notifications.index');
        Route::put('notifications/{notification}/read',[SuperNotification::class,'read'])->name('notifications.read');
        
        // Custom Arsip Action
        Route::get('arsip/{id}/print-draft', [SuperArsip::class, 'printDraft'])->name('arsip.print-draft');
        Route::post('arsip/{id}/upload-lampiran', [SuperArsip::class, 'uploadLampiran'])->name('arsip.upload-lampiran');
        Route::get('arsip/{id}/lampiran', [SuperArsip::class, 'listLampiran'])->name('arsip.list-lampiran');
        Route::get('arsip/{arsip}/lampiran/{lampiran}/view', [SuperArsip::class, 'viewLampiran'])->name('arsip.view-lampiran');
        Route::delete('arsip/{arsip}/lampiran/{lampiran}', [SuperArsip::class, 'deleteLampiran'])->name('arsip.delete-lampiran');
        Route::get('arsip/{id}/show-document', [SuperArsip::class, 'showDocument'])->name('arsip.show-document');
        Route::get('arsip/{id}/produk-detail', [SuperArsip::class, 'produkDetail'])->name('arsip.produk-detail');
        Route::put('arsip/{id}/arsip-sistem',[SuperArsip::class, 'arsipSistem'])->name('arsip.arsip-sistem');
        Route::post('arsip/cleanup-storage', [SuperArsip::class, 'cleanupStorage'])->name('arsip.cleanup-storage');
        Route::patch('arsip/{id}/no-registrasi', [SuperBackup::class, 'updateNoRegistrasi'])->name('arsip.update-no-registrasi');

        // Backup & Restore
        Route::get('backup/export', [SuperBackup::class, 'export'])->name('backup.export');
        Route::post('backup/import', [SuperBackup::class, 'import'])->name('backup.import');
        Route::get('backup', fn() => view('superadmin.backup.index'))->name('backup.index');

        // AKSES PENGAJUAN — per ROLE (baseline). Exception per-arsip via share.
        Route::get('pengajuan-access', [\App\Http\Controllers\Superadmin\PengajuanAccessController::class, 'index'])->name('pengajuan-access.index');
        Route::put('pengajuan-access', [\App\Http\Controllers\Superadmin\PengajuanAccessController::class, 'updateBulk'])->name('pengajuan-access.update-bulk');
        Route::post('pengajuan-access/{role}/grant-all', [\App\Http\Controllers\Superadmin\PengajuanAccessController::class, 'grantAll'])->name('pengajuan-access.grant-all');
        Route::post('pengajuan-access/{role}/revoke-all', [\App\Http\Controllers\Superadmin\PengajuanAccessController::class, 'revokeAll'])->name('pengajuan-access.revoke-all');

        // LOG AKTIVITAS
        Route::get('activity-logs', [SuperActivity::class, 'index'])->name('activity-logs.index');

        // STATISTIK SERVER
        Route::get('server-stats', [SuperServer::class, 'index'])->name('server-stats.index');
        Route::get('server-stats/metrics', [SuperServer::class, 'metrics'])->name('server-stats.metrics');

        // KELOLA APK ANDROID (auto-update mechanism)
        Route::get('app-versions',                    [\App\Http\Controllers\Superadmin\AppVersionController::class, 'index'])->name('app-versions.index');
        Route::post('app-versions',                   [\App\Http\Controllers\Superadmin\AppVersionController::class, 'upsert'])->name('app-versions.upsert');
        Route::post('app-versions/{id}/upload-apk',   [\App\Http\Controllers\Superadmin\AppVersionController::class, 'uploadApk'])->name('app-versions.upload-apk');
        Route::delete('app-versions/{id}',            [\App\Http\Controllers\Superadmin\AppVersionController::class, 'destroy'])->name('app-versions.destroy');

        // MASTER DATA
        Route::patch('departments/{department}/toggle', [SuperDepartment::class, 'toggleIsActive'])->name('departments.toggle');
        Route::resource('departments', SuperDepartment::class);

        Route::patch('locations/{location}/toggle', [SuperLocation::class, 'toggleIsActive'])->name('locations.toggle');
        Route::resource('locations', SuperLocation::class);

        Route::patch('units/{unit}/toggle', [SuperUnit::class, 'toggleIsActive'])->name('units.toggle');
        Route::resource('units', SuperUnit::class);

        Route::patch('managers/{manager}/toggle', [SuperManager::class, 'toggleIsActive'])->name('managers.toggle');
        Route::resource('managers', SuperManager::class);

        Route::patch('users/{user}/toggle', [SuperUser::class, 'toggleIsActive'])->name('users.toggle');
        Route::post('users/{user}/delegate', [SuperUser::class, 'setDelegate'])->name('users.set-delegate');
        Route::delete('users/{user}/delegate', [SuperUser::class, 'clearDelegate'])->name('users.clear-delegate');
        Route::resource('users', SuperUser::class);

        // ✅ MASTER PRODUK
        Route::patch('products/{id}/toggle', [SuperProduct::class, 'toggleStatus'])->name('products.toggle');
        Route::resource('products', SuperProduct::class);

        // ✅ SETTINGS
        Route::get('settings', [SuperSetting::class, 'index'])->name('settings.index');
        Route::post('settings', [SuperSetting::class, 'update'])->name('settings.update');
    });