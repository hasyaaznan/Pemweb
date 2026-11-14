<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Halaman member hanya bisa dibuka setelah masuk dan token API tersimpan di session
class PunyaToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('api_token')) {
            return redirect()->route('masuk');
        }

        return $next($request);
    }
}
