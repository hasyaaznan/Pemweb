<?php

namespace App\Http\Controllers\Coach;

use App\Support\JadwalLatihan;
use Illuminate\Http\Request;
use Illuminate\View\View;

// L7 Pilih kelompok & tanggal: hari latihan seminggu ke belakang dan ke depan
class BerandaController extends Controller
{
    public function __invoke(Request $request): View
    {
        $kelompok = $this->kelompok($request);
        $sesi = JadwalLatihan::rentang($kelompok, today()->subDays(7), today()->addDays(7));
        $hariLatihan = $sesi->map(fn ($s) => $s->tanggal->toDateString())->unique()->values();

        // Bawaan: hari ini jika ada latihan, kalau tidak hari latihan terakhir yang sudah lewat
        $dipilih = $request->date('tanggal')?->toDateString();
        if (! $hariLatihan->contains($dipilih)) {
            $dipilih = $hariLatihan->contains(today()->toDateString())
                ? today()->toDateString()
                : ($hariLatihan->filter(fn ($t) => $t <= today()->toDateString())->last() ?? $hariLatihan->first());
        }

        $sesiHariIni = $sesi->filter(fn ($s) => $s->tanggal->toDateString() === $dipilih)->values();
        $sesiHariIni->each(function ($s) {
            $s->setAttribute('jumlah_anak', $s->kelompokUsia->anak()->where('status', 'aktif')->count());
            $s->setAttribute('rekap', $s->exists ? $s->absensi()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status') : collect());
        });

        return view('coach.beranda', [
            'hariLatihan' => $hariLatihan,
            'dipilih' => $dipilih,
            'sesi' => $sesiHariIni,
        ]);
    }
}
