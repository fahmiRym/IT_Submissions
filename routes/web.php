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
use App\Http\Controllers\Superadmin\UnitController as SuperUnit;
use App\Http\Controllers\Superadmin\ManagerController as SuperManager;
use App\Http\Controllers\Superadmin\UserController as SuperUser;
use App\Http\Controllers\Superadmin\ProfileController as SuperProfile;
use App\Http\Controllers\Superadmin\LaporanController as SuperLaporan;
use App\Http\Controllers\Superadmin\NotificationController as SuperNotification;
use App\Http\Controllers\Superadmin\BackupController as SuperBackup;
use App\Http\Controllers\Superadmin\SettingController as SuperSetting;

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

/*
|--------------------------------------------------------------------------
| ROOT & DEBUG
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

// FILE PREVIEW — Tidak perlu auth karena filename tidak bisa ditebak
// Ini agar preview bisa jalan di HTTP maupun HTTPS tanpa masalah session cookie
Route::get('/preview-file/{filename}', function ($filename) {
    // Hanya izinkan nama file yang aman (alfanumeric, -, _, .)
    if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $filename)) {
        abort(403);
    }

    $path = storage_path('app/public/bukti_scan/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    $mime = mime_content_type($path);

    return response()->file($path, [
        'Content-Type'        => $mime,
        'Content-Disposition' => 'inline; filename="' . $filename . '"',
        'Cache-Control'       => 'private, max-age=3600',
    ]);
})->name('preview.file');

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
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');
});

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth','role:admin'])
    ->name('admin.') // Prefix Nama: semua route di sini otomatis diawali 'admin.'
    ->group(function () {

        Route::get('dashboard', [AdminDashboard::class,'index'])
            ->name('dashboard');

        // =================================================================
        // PERBAIKAN UTAMA DI SINI
        // =================================================================
        // Tambahkan 'edit' ke dalam only().
        // Ini otomatis membuat route bernama 'admin.arsip.edit'
        // dan mengarah ke method edit() di controller.
        Route::resource('arsip', AdminArsip::class)
            ->only(['index', 'store', 'update', 'edit']); 

        // ✅ PROFILE ADMIN
        Route::get('profile', [AdminProfile::class,'index'])->name('profile');
        Route::put('profile', [AdminProfile::class,'update'])->name('profile.update');

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

        Route::resource('arsip', SuperArsip::class);

        // ✅ PROFILE SUPERADMIN
        Route::get('profile', [SuperProfile::class,'index'])->name('profile');
        Route::put('profile', [SuperProfile::class,'update'])->name('profile.update');

        Route::get('laporan/pdf', [SuperLaporan::class, 'printPdf'])->name('laporan.pdf');
        Route::get('laporan', [SuperLaporan::class, 'index'])->name('laporan.index');
        
        // Notifikasi Superadmin
        Route::get('notifications',[SuperNotification::class,'index'])->name('notifications.index');
        Route::put('notifications/{notification}/read',[SuperNotification::class,'read'])->name('notifications.read');
        
        // Custom Arsip Action
        Route::put('arsip/{id}/arsip-sistem',[SuperArsip::class, 'arsipSistem'])->name('arsip.arsip-sistem');
        Route::post('arsip/cleanup-storage', [SuperArsip::class, 'cleanupStorage'])->name('arsip.cleanup-storage');
        Route::patch('arsip/{id}/no-registrasi', [SuperBackup::class, 'updateNoRegistrasi'])->name('arsip.update-no-registrasi');
        Route::get('arsip/search-simple', [SuperArsip::class, 'searchSimple'])->name('arsip.search-simple');

        // Backup & Restore
        Route::get('backup/export', [SuperBackup::class, 'export'])->name('backup.export');
        Route::post('backup/import', [SuperBackup::class, 'import'])->name('backup.import');
        Route::get('backup', fn() => view('superadmin.backup.index'))->name('backup.index');

        // MASTER DATA
        Route::patch('departments/{department}/toggle', [SuperDepartment::class, 'toggleIsActive'])->name('departments.toggle');
        Route::resource('departments', SuperDepartment::class);

        Route::patch('units/{unit}/toggle', [SuperUnit::class, 'toggleIsActive'])->name('units.toggle');
        Route::resource('units', SuperUnit::class);

        Route::patch('managers/{manager}/toggle', [SuperManager::class, 'toggleIsActive'])->name('managers.toggle');
        Route::resource('managers', SuperManager::class);

        Route::patch('users/{user}/toggle', [SuperUser::class, 'toggleIsActive'])->name('users.toggle');
        Route::resource('users', SuperUser::class);

        // ✅ SETTINGS
        Route::get('settings', [SuperSetting::class, 'index'])->name('settings.index');
        Route::post('settings', [SuperSetting::class, 'update'])->name('settings.update');
    });