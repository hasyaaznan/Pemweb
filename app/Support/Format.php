<?php

namespace App\Support;

use Carbon\CarbonInterface;

// Format tampilan yang dipakai di banyak layar.
class Format
{
    public static function rupiah(int $nominal): string
    {
        return 'Rp '.number_format($nominal, 0, ',', '.');
    }

    // Nilai rapor memakai tanda minus asli (−), sama dengan mockup
    public static function nilai(string $nilai): string
    {
        return str_replace('-', '−', $nilai);
    }

    // "hari ini", "besok", atau "10 Okt"
    public static function jatuhTempo(CarbonInterface $tanggal): string
    {
        if ($tanggal->isToday()) {
            return 'jatuh tempo hari ini';
        }
        if ($tanggal->isPast()) {
            return 'lewat jatuh tempo '.$tanggal->translatedFormat('j M');
        }

        return 'jatuh tempo '.$tanggal->translatedFormat('j M');
    }

    public static function jam(string $jam): string
    {
        return str_replace(':', '.', substr($jam, 0, 5));
    }

    // No. HP disimpan dalam bentuk 08xxxxxxxxxx
    public static function noHp(string $masukan): string
    {
        $angka = preg_replace('/\D/', '', $masukan);

        return str_starts_with($angka, '62') ? '0'.substr($angka, 2) : $angka;
    }

    // 081234560000 → "0812 3456 0000" (seperti di mockup L10)
    public static function tampilHp(string $nomor): string
    {
        return trim(implode(' ', str_split(self::noHp($nomor), 4)));
    }

    // Rp 200.000 → "Rp 200rb" untuk ringkasan singkat di landing
    public static function rupiahSingkat(int $nominal): string
    {
        return $nominal % 1000 === 0 ? 'Rp '.number_format($nominal / 1000, 0, ',', '.').'rb' : self::rupiah($nominal);
    }

    // 1234567890 → "123 456 7890" (kelompok 3-3-sisa, seperti di mockup L3)
    public static function rekening(string $nomor): string
    {
        $angka = preg_replace('/\D/', '', $nomor);

        return trim(substr($angka, 0, 3).' '.substr($angka, 3, 3).' '.substr($angka, 6));
    }
}
