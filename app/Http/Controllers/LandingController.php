<?php

namespace App\Http\Controllers;

use App\Models\KelompokUsia;
use App\Support\Klub;
use Illuminate\View\View;

// L10 Landing page dan L12 menu: informasi untuk calon member tanpa login (K7)
class LandingController extends Controller
{
    // Isi program per kelompok usia (mockup L10), dicocokkan dengan usia_min
    private const PROGRAM = [
        6 => 'Gerak dasar lewat permainan',
        9 => 'Dribbling, passing, kerja tim',
        13 => 'Taktik tim dan persiapan turnamen',
    ];

    public function __invoke(): View
    {
        $kelompok = KelompokUsia::orderBy('usia_min')
            ->with(['jadwal' => fn ($q) => $q->where('aktif', true)->whereDate('berlaku_mulai', '<=', today())->orderBy('hari')])
            ->get()
            ->each(fn ($k) => $k->setAttribute('program', self::PROGRAM[$k->usia_min] ?? ''));

        $alamat = Klub::get('alamat_gor', 'GOR Spiders, Bandung');
        $wa = preg_replace('/^0/', '62', (string) Klub::get('whatsapp_admin'));

        return view('landing', [
            'kelompok' => $kelompok,
            'hari' => $kelompok->pluck('jadwal')->flatten()->pluck('hari')->unique()->sort()->values(),
            'iuranTermurah' => $kelompok->min('iuran_bulanan'),
            'usiaMin' => $kelompok->min('usia_min'),
            'usiaMax' => $kelompok->max('usia_max'),
            'alamat' => $alamat,
            'gor' => trim(strtok($alamat, ',')),
            'waAdmin' => Klub::get('whatsapp_admin'),
            'tautanWa' => 'https://wa.me/'.$wa.'?text='.rawurlencode('Halo Admin Bandung Spiders, saya ingin bertanya tentang latihan basket untuk anak.'),
            'tautanMaps' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($alamat),
            'petaEmbed' => 'https://maps.google.com/maps?q='.rawurlencode($alamat).'&output=embed',
        ]);
    }
}
