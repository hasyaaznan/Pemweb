<?php

namespace App\Http\Controllers\OrangTua;

use Illuminate\Http\Request;
use Illuminate\View\View;

// L19 Profil orang tua
class ProfilController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('ortu.profil', [
            'user' => $request->user(),
            'semuaAnak' => $request->user()->anak()->with('kelompokUsia')->orderBy('nama')->get(),
        ]);
    }
}
