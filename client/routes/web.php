<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MemberController;
use App\Http\Middleware\PunyaToken;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('member.index'));
Route::get('/masuk', [AuthController::class, 'create'])->name('masuk');
Route::post('/masuk', [AuthController::class, 'store']);

Route::middleware(PunyaToken::class)->group(function () {
    Route::post('/keluar', [AuthController::class, 'destroy'])->name('keluar');
    Route::resource('member', MemberController::class)->parameters(['member' => 'id']);
    Route::patch('/member/{id}/status', [MemberController::class, 'status'])->name('member.status');
});
