<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Setiap peran hanya bisa membuka menu miliknya (K2). Contoh pemakaian: ->middleware('peran:orang_tua')
class PastikanPeran
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        abort_unless($request->user() && in_array($request->user()->peran, $peran, true), 403);

        return $next($request);
    }
}
