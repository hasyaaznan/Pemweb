<?php

namespace App\Support;

use App\Models\Anak;
use App\Models\Notifikasi;
use App\Models\Tagihan;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

// Tagihan iuran dibuat setiap tanggal 1 untuk semua anak aktif (K5).
// Aman dijalankan berulang: anak yang sudah punya tagihan periode itu dilewati.
class TagihanBulanan
{
    /** Jumlah tagihan baru yang dibuat untuk bulan dari $tanggal. */
    public static function buat(CarbonInterface $tanggal): int
    {
        $periode = $tanggal->copy()->startOfMonth();
        $hariTempo = min(28, max(1, (int) Klub::get('tanggal_jatuh_tempo', '10')));
        $dibuat = 0;

        foreach (self::belumDitagih($periode)->with('kelompokUsia')->get() as $anak) {
            $dibuat += DB::transaction(function () use ($anak, $periode, $hariTempo) {
                // Kunci baris anak agar dua proses bersamaan tidak membuat tagihan ganda
                Anak::whereKey($anak->id)->lockForUpdate()->first();
                if ($anak->tagihan()->whereDate('periode', $periode)->exists()) {
                    return 0;
                }

                $tagihan = $anak->tagihan()->create([
                    'periode' => $periode->toDateString(),
                    'nominal' => $anak->kelompokUsia->iuran_bulanan,
                    'jatuh_tempo' => $periode->copy()->day($hariTempo)->toDateString(),
                    'status' => 'belum_bayar',
                ]);
                Notifikasi::create([
                    'user_id' => $anak->orang_tua_id,
                    'jenis' => 'tagihan_terbit',
                    'judul' => 'Tagihan '.$periode->translatedFormat('F Y'),
                    'isi' => 'Iuran '.$anak->nama.' '.Format::rupiah($tagihan->nominal).', jatuh tempo '.$tagihan->jatuh_tempo->translatedFormat('j F').'.',
                    'tautan' => route('ortu.tagihan', ['anak' => $anak->id], false),
                ]);

                return 1;
            });
        }

        return $dibuat;
    }

    /** Anak aktif yang sudah masuk sebelum akhir bulan tetapi belum punya tagihan bulan itu. */
    public static function belumDitagih(CarbonInterface $periode)
    {
        return Anak::where('status', 'aktif')
            ->whereDate('tanggal_masuk', '<=', $periode->copy()->endOfMonth())
            ->whereDoesntHave('tagihan', fn ($q) => $q->whereDate('periode', $periode->copy()->startOfMonth()));
    }
}
