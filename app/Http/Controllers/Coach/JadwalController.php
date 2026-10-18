<?php

namespace App\Http\Controllers\Coach;

use App\Support\JadwalLatihan;
use Illuminate\Http\Request;
use Illuminate\View\View;

// L24 Jadwal coach: satu minggu (Senin–Minggu), latihan untuk kelompok yang dipegang
class JadwalController extends Controller
{
    public function __invoke(Request $request): View
    {
        $dipilih = ($request->date('tanggal') ?? today())->startOfDay();
        $senin = $dipilih->copy()->startOfWeek();
        $sesi = JadwalLatihan::rentang($this->kelompok($request), $senin, $senin->copy()->endOfWeek());
        $sesi->each(fn ($s) => $s->setAttribute('jumlah_anak', $s->kelompokUsia->anak()->where('status', 'aktif')->count()));

        return view('coach.jadwal', [
            'senin' => $senin,
            'dipilih' => $dipilih,
            'sesi' => $sesi,
        ]);
    }
}
