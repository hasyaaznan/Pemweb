<?php

namespace App\Support;

use App\Models\Absensi;
use App\Models\Anak;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

// Ringkasan kehadiran anak (K4): persen = jumlah hadir ÷ jumlah sesi yang tidak dibatalkan
class Kehadiran
{
    public static function riwayat(Anak $anak, CarbonInterface $dari): Collection
    {
        return Absensi::query()
            ->with('sesiLatihan')
            ->where('anak_id', $anak->id)
            ->whereHas('sesiLatihan', fn ($q) => $q->where('status', '<>', 'batal')
                ->whereDate('tanggal', '>=', $dari)
                ->whereDate('tanggal', '<=', now()))
            ->get()
            ->sortByDesc(fn ($a) => $a->sesiLatihan->tanggal)
            ->values();
    }

    /** @return array{hadir:int, izin:int, alpa:int, total:int, persen:int} */
    public static function ringkas(Collection $riwayat): array
    {
        $hitung = $riwayat->countBy('status');
        $total = $riwayat->count();
        $hadir = $hitung->get('hadir', 0);

        return [
            'hadir' => $hadir,
            'izin' => $hitung->get('izin', 0),
            'alpa' => $hitung->get('alpa', 0),
            'total' => $total,
            'persen' => $total ? (int) round(100 * $hadir / $total) : 0,
        ];
    }

    // Awal periode 3 bulan terakhir: Agu–Okt jika sekarang Oktober
    public static function awalTigaBulan(): CarbonInterface
    {
        return now()->startOfMonth()->subMonths(2);
    }
}
