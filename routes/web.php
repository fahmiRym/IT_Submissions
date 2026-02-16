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

Route::get('/debug-db', function() {
    return Schema::getColumnListing('arsips');
});

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

    // SECURE FILE VIEW (Bypass Symlink Issues)
    Route::get('/preview-file/{filename}', function ($filename) {
        $path = storage_path('app/public/bukti_scan/' . $filename);
        
        if (!file_exists($path)) {
            abort(404);
        }

        $mime = mime_content_type($path);

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.$filename.'"'
        ]);
    })->name('preview.file');
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

        // MASTER DATA
        Route::resource('departments', SuperDepartment::class);
        Route::resource('units', SuperUnit::class);
        Route::resource('managers', SuperManager::class);
        Route::resource('users', SuperUser::class);
    });