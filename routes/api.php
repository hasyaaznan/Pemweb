<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoachController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\KelompokUsiaController;
use App\Http\Controllers\Api\MemberController;
use Illuminate\Support\Facades\Route;

// REST API untuk aplikasi mobile dan client lain. Semua alamat diawali /api/v1.
// Panduan dan contoh: docs/api/README.md, koleksi Postman: docs/api/spiders-api.postman_collection.json
Route::prefix('v1')->name('api.')->group(function () {
    Route::post('/masuk', [AuthController::class, 'masuk'])->middleware('throttle:10,1')->name('masuk');

    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
        Route::get('/saya', [AuthController::class, 'saya'])->name('saya');
        Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');

        // Baca data master: semua peran (member dibatasi sesuai peran di controller)
        Route::apiResource('kelompok-usia', KelompokUsiaController::class)->only(['index', 'show'])->parameters(['kelompok-usia' => 'kelompok_usia']);
        Route::apiResource('jadwal', JadwalController::class)->only(['index', 'show']);
        Route::apiResource('member', MemberController::class)->only(['index', 'show']);

        // Tambah, ubah (PUT/PATCH), hapus: hanya Admin
        Route::middleware('peran:manajemen')->group(function () {
            Route::apiResource('kelompok-usia', KelompokUsiaController::class)->except(['index', 'show'])->parameters(['kelompok-usia' => 'kelompok_usia']);
            Route::apiResource('jadwal', JadwalController::class)->except(['index', 'show']);
            Route::apiResource('member', MemberController::class)->except(['index', 'show']);
            Route::apiResource('coach', CoachController::class);
        });
    });
});
