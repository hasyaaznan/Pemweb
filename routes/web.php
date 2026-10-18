<?php

use App\Http\Controllers\Auth\AturUlangSandiController;
use App\Http\Controllers\Auth\LupaSandiController;
use App\Http\Controllers\Auth\MasukController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\Coach;
use App\Http\Controllers\OrangTua;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [MasukController::class, 'create'])->name('login');
    Route::post('/masuk', [MasukController::class, 'store'])->middleware('throttle:20,1');
    Route::get('/lupa-kata-sandi', [LupaSandiController::class, 'create'])->name('password.request');
    Route::post('/lupa-kata-sandi', [LupaSandiController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/atur-ulang-kata-sandi/{token}', [AturUlangSandiController::class, 'create'])->name('password.reset');
    Route::post('/atur-ulang-kata-sandi', [AturUlangSandiController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/keluar', [MasukController::class, 'destroy'])->name('logout');
    Route::get('/beranda', BerandaController::class)->name('beranda');

    Route::middleware('peran:orang_tua')->prefix('orang-tua')->name('ortu.')->group(function () {
        Route::get('/', OrangTua\BerandaController::class)->name('beranda');
        Route::get('/rapor', [OrangTua\RaporController::class, 'rapor'])->name('rapor');
        Route::get('/absensi', [OrangTua\RaporController::class, 'absensi'])->name('absensi');
        Route::get('/tagihan', [OrangTua\TagihanController::class, 'index'])->name('tagihan');
        Route::get('/tagihan/{tagihan}/unggah', [OrangTua\TagihanController::class, 'unggah'])->name('tagihan.unggah');
        Route::post('/tagihan/{tagihan}/unggah', [OrangTua\TagihanController::class, 'simpan'])->name('tagihan.simpan');
        Route::get('/tagihan/{tagihan}/terkirim', [OrangTua\TagihanController::class, 'terkirim'])->name('tagihan.terkirim');
        Route::get('/profil', OrangTua\ProfilController::class)->name('profil');
    });

    Route::middleware('peran:coach')->prefix('coach')->name('coach.')->group(function () {
        Route::get('/', Coach\BerandaController::class)->name('beranda');
        Route::get('/absensi/{kelompok}/{tanggal}', [Coach\AbsensiController::class, 'edit'])->name('absensi');
        Route::put('/absensi/{kelompok}/{tanggal}', [Coach\AbsensiController::class, 'update'])->name('absensi.simpan');
        Route::get('/rapor', [Coach\RaporController::class, 'index'])->name('rapor');
        Route::get('/rapor/{anak}', [Coach\RaporController::class, 'edit'])->name('rapor.isi');
        Route::put('/rapor/{anak}', [Coach\RaporController::class, 'update'])->name('rapor.simpan');
        Route::get('/jadwal', Coach\JadwalController::class)->name('jadwal');
        Route::get('/profil', Coach\ProfilController::class)->name('profil');
    });

    // Halaman manajemen dibuat di tahap berikutnya
    Route::view('/manajemen', 'segera', ['peran' => 'manajemen'])->middleware('peran:manajemen')->name('manajemen.beranda');
});
