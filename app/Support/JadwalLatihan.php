<?php

namespace App\Support;

use App\Models\Jadwal;
use App\Models\KelompokUsia;
use App\Models\SesiLatihan;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Latihan pada satu tanggal untuk satu kelompok usia.
// Jadwal menyimpan pola mingguan; sesi_latihan baru dibuat saat ada perubahan atau saat absensi diisi.
class JadwalLatihan
{
    /** Sesi yang sudah tersimpan, atau sesi baru (belum disimpan) dari jadwal mingguan. */
    public static function sesi(KelompokUsia $ku, CarbonInterface $tanggal): ?SesiLatihan
    {
        $sesi = SesiLatihan::where('kelompok_usia_id', $ku->id)->whereDate('tanggal', $tanggal)->first();
        if ($sesi) {
            return $sesi;
        }

        $jadwal = self::jadwalHari($ku->id, $tanggal);

        return $jadwal ? new SesiLatihan([
            'kelompok_usia_id' => $ku->id, 'jadwal_id' => $jadwal->id, 'tanggal' => $tanggal->toDateString(),
            'jam_mulai' => $jadwal->jam_mulai, 'jam_selesai' => $jadwal->jam_selesai, 'lokasi' => $jadwal->lokasi,
            'status' => 'terjadwal',
        ]) : null;
    }

    /** Simpan sesi dari jadwal jika belum ada, dengan kunci agar tidak tercipta dua kali. */
    public static function simpan(SesiLatihan $sesi): SesiLatihan
    {
        if ($sesi->exists) {
            return $sesi;
        }

        return DB::transaction(fn () => SesiLatihan::where('kelompok_usia_id', $sesi->kelompok_usia_id)
            ->whereDate('tanggal', $sesi->tanggal)->lockForUpdate()->first() ?? tap($sesi)->save());
    }

    /**
     * Semua sesi untuk beberapa kelompok usia dalam rentang tanggal, urut tanggal lalu jam.
     *
     * @param  Collection<int, KelompokUsia>  $kelompok
     * @return Collection<int, SesiLatihan>
     */
    public static function rentang(Collection $kelompok, CarbonInterface $dari, CarbonInterface $sampai): Collection
    {
        $hasil = collect();
        foreach (CarbonPeriod::create($dari->copy()->startOfDay(), $sampai->copy()->startOfDay()) as $tanggal) {
            foreach ($kelompok as $ku) {
                if ($sesi = self::sesi($ku, $tanggal)) {
                    $hasil->push($sesi->setRelation('kelompokUsia', $ku));
                }
            }
        }

        return $hasil->sortBy(fn ($s) => $s->tanggal->toDateString().' '.$s->jam_mulai)->values();
    }

    private static function jadwalHari(int $kuId, CarbonInterface $tanggal): ?Jadwal
    {
        return Jadwal::where('kelompok_usia_id', $kuId)
            ->where('hari', $tanggal->dayOfWeekIso)
            ->where('aktif', true)
            ->whereDate('berlaku_mulai', '<=', $tanggal)
            ->orderByDesc('berlaku_mulai')
            ->first();
    }
}
