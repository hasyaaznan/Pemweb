<?php

namespace App\Http\Controllers\Coach;

use Illuminate\Http\Request;
use Illuminate\View\View;

// L25 Profil coach
class ProfilController extends Controller
{
    public function __invoke(Request $request): View
    {
        $kelompok = $this->kelompok($request)->loadCount(['anak' => fn ($q) => $q->where('status', 'aktif')]);

        return view('coach.profil', ['user' => $request->user(), 'kelompok' => $kelompok]);
    }
}
