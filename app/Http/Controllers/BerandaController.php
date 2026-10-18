<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Setelah login, setiap peran diarahkan ke berandanya sendiri (K2)
class BerandaController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return match ($request->user()->peran) {
            'orang_tua' => redirect()->route('ortu.beranda'),
            'coach' => redirect()->route('coach.beranda'),
            'manajemen' => redirect()->route('manajemen.beranda'),
        };
    }
}
