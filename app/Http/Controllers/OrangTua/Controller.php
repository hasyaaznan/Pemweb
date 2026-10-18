<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller as Dasar;
use App\Models\Anak;
use Illuminate\Http\Request;

// Dasar semua halaman orang tua: menentukan anak yang sedang dilihat.
// Orang tua hanya bisa membuka anaknya sendiri; anak lain = akses ditolak (K10).
abstract class Controller extends Dasar
{
    protected function anakAktif(Request $request): ?Anak
    {
        $milik = $request->user()->anak();

        if ($request->filled('anak')) {
            $anak = (clone $milik)->whereKey($request->integer('anak'))->first();
            abort_unless($anak, 403, 'Akses ditolak');
            $request->session()->put('anak_aktif', $anak->id);

            return $anak;
        }

        return (clone $milik)->whereKey($request->session()->get('anak_aktif'))->first()
            ?? $milik->orderBy('nama')->first();
    }
}
