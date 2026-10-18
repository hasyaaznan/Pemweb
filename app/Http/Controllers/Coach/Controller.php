<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller as Dasar;
use App\Models\Anak;
use App\Models\KelompokUsia;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// Dasar semua halaman coach: coach hanya membuka kelompok usia yang ia pegang (K10).
abstract class Controller extends Dasar
{
    /** @return Collection<int, KelompokUsia> */
    protected function kelompok(Request $request): Collection
    {
        return $request->user()->kelompokDipegang()->orderBy('usia_min')->get();
    }

    protected function pastikanPegang(Request $request, KelompokUsia $ku): void
    {
        abort_unless($request->user()->kelompokDipegang()->whereKey($ku->id)->exists(), 403, 'Akses ditolak');
    }

    protected function pastikanAnakDipegang(Request $request, Anak $anak): void
    {
        abort_unless($request->user()->kelompokDipegang()->whereKey($anak->kelompok_usia_id)->exists(), 403, 'Akses ditolak');
    }
}
