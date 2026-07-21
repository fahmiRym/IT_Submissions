<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarcodeController;
use App\Http\Controllers\Api\ArsipApiController;
use App\Http\Controllers\Api\ApprovalApiController;
use App\Http\Controllers\Api\ServerStatApiController;
use App\Http\Controllers\Api\ActivityLogApiController;
use App\Http\Controllers\Api\AppVersionController;
use App\Http\Controllers\Api\FcmController;
use App\Http\Controllers\Api\NotificationController;

// Auth Routes (Login)
Route::post('/login', [AuthController::class, 'login']);

// ==========================================
// PUBLIC — Version Check (untuk Android auto-update)
// Tidak butuh auth karena dipanggil di splash sebelum login.
// ==========================================
Route::get('/mobile/version',  [AppVersionController::class, 'show']);
Route::get('/mobile/versions', [AppVersionController::class, 'index']);

// Protected Routes (Butuh Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/arsip/scan', [BarcodeController::class, 'processScan']);
    Route::post('/arsip/update-status', [BarcodeController::class, 'updateStatus']);

    // Rute Baru Pengajuan Via Android
    Route::get('/arsip/dashboard', [ArsipApiController::class, 'getDashboard']);
    Route::get('/arsip/master-data', [ArsipApiController::class, 'getMasterData']);
    Route::post('/arsip/store', [ArsipApiController::class, 'storePengajuan']);
    Route::get('/arsip', [ArsipApiController::class, 'index']);
    Route::get('/arsip/outstanding-ba', [ArsipApiController::class, 'getOutstandingBA']);
    Route::get('/arsip/{id}', [ArsipApiController::class, 'show'])->whereNumber('id');

    // ==========================================
    // APPROVAL FLOW (P0 — Android Sprint 1)
    // Trait-based (source of truth = trait HandlesApproval + SignsArsip)
    // ==========================================
    Route::get('/approvals',            [ApprovalApiController::class, 'myApprovals']);
    Route::post('/arsip/{id}/approve',  [ApprovalApiController::class, 'approveArsip'])->whereNumber('id');
    Route::post('/arsip/{id}/reject',   [ApprovalApiController::class, 'rejectArsip'])->whereNumber('id');
    Route::post('/arsip/{id}/sign',     [ApprovalApiController::class, 'signArsip'])->whereNumber('id');

    // ==========================================
    // SUPERADMIN ADMIN TOOLS (guard: superadmin only, cek di controller)
    // ==========================================
    Route::prefix('superadmin')->group(function () {
        // Server Stats
        Route::get('/server-stats',          [ServerStatApiController::class, 'apiSnapshot']);
        Route::get('/server-stats/metrics',  [ServerStatApiController::class, 'apiMetrics']);

        // Activity Logs (audit trail)
        Route::get('/activity-logs',         [ActivityLogApiController::class, 'index']);
        Route::get('/activity-logs/users',   [ActivityLogApiController::class, 'users']);
    });

    // ==========================================
    // Firebase Cloud Messaging (Push Notification)
    // ==========================================
    Route::post('/device-token', [FcmController::class, 'store']);
    Route::delete('/device-token', [FcmController::class, 'destroy']);
    Route::post('/fcm/test', [FcmController::class, 'test']);

    // Notifikasi in-app (untuk daftar di Android)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Rute test token bawaan Laravel
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
