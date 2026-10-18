<?php

namespace App\Support;

use Carbon\CarbonInterface;

// Batas verifikasi bukti dihitung dalam hari kerja (Senin–Jumat), sesuai Dokumen Kebutuhan v2
class HariKerja
{
    /** Hari kerja yang sudah lewat sejak $dari sampai hari ini, tidak termasuk hari $dari. */
    public static function sejak(CarbonInterface $dari): int
    {
        $hari = 0;
        for ($t = $dari->copy()->startOfDay()->addDay(); $t->lte(today()); $t->addDay()) {
            $hari += $t->isWeekday() ? 1 : 0;
        }

        return $hari;
    }

    public static function lewatBatas(CarbonInterface $diunggah): bool
    {
        return self::sejak($diunggah) > (int) Klub::get('batas_verifikasi_hari_kerja', '2');
    }
}
