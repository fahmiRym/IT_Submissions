<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarcodeController;
use App\Http\Controllers\Api\ArsipApiController;

// Auth Routes (Login)
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Butuh Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/arsip/scan', [BarcodeController::class, 'processScan']);
    Route::post('/arsip/update-status', [BarcodeController::class, 'updateStatus']);

    // Rute Baru Pengajuan Via Android
    Route::get('/arsip/dashboard', [ArsipApiController::class, 'getDashboard']);
    Route::get('/arsip/master-data', [ArsipApiController::class, 'getMasterData']);
    Route::post('/arsip/store', [ArsipApiController::class, 'storePengajuan']);

    // Rute test token bawaan Laravel
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
