<?php

use App\Http\Middleware\PaksaJson;
use App\Http\Middleware\PastikanPeran;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['peran' => PastikanPeran::class]);
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : route('login'));
        $middleware->redirectUsersTo(fn () => route('beranda'));
        // Semua jawaban API berupa JSON, walau klien lupa mengirim header Accept: application/json
        $middleware->api(prepend: [PaksaJson::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $api = fn (Request $request) => $request->is('api/*');
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $api($request) || $request->expectsJson());

        // Pesan error API dalam bahasa Indonesia, tanpa membocorkan nama model atau detail server
        $exceptions->render(fn (AuthenticationException $e, Request $request) => $api($request)
            ? response()->json(['message' => 'Belum masuk atau token tidak berlaku. Masuk dulu lewat POST /api/v1/masuk.'], 401) : null);
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($api) {
            if (! $api($request)) {
                return null;
            }
            $status = $e->getStatusCode();
            $pesan = match ($status) {
                403 => 'Akses ditolak. Peran akun ini tidak boleh membuka data tersebut.',
                404 => $request->route() ? 'Data tidak ditemukan.' : 'Alamat API tidak ditemukan.',
                405 => 'Metode '.$request->method().' tidak tersedia untuk alamat ini.',
                429 => 'Terlalu banyak permintaan. Coba lagi sebentar lagi.',
                default => $e->getMessage() ?: 'Terjadi kesalahan.',
            };

            return response()->json(['message' => $pesan], $status, $e->getHeaders());
        });
    })->create();
