<?php

namespace App\Http\Controllers;

use App\Models\Galeri;
use App\Models\KelompokUsia;
use App\Models\Linimasa;
use App\Models\TanyaJawab;
use App\Models\Turnamen;
use App\Models\User;
use App\Support\Klub;
use Illuminate\View\View;

// L10 Landing page dan L12 menu: informasi untuk calon member tanpa login (K7, K8).
// Semua isinya dari database dan dikelola Admin.
class LandingController extends Controller
{
    public function __invoke(): View
    {
        $kelompok = KelompokUsia::orderBy('usia_min')
            ->with(['jadwal' => fn ($q) => $q->where('aktif', true)->whereDate('berlaku_mulai', '<=', today())->orderBy('hari')])
            ->get();

        $alamat = Klub::get('alamat_gor', 'GOR, Bandung');
        $wa = preg_replace('/^0/', '62', (string) Klub::get('whatsapp_admin'));

        return view('landing', [
            'kelompok' => $kelompok,
            'hari' => $kelompok->pluck('jadwal')->flatten()->pluck('hari')->unique()->sort()->values(),
            'iuranTermurah' => $kelompok->min('iuran_bulanan') ?? 0,
            'usiaMin' => $kelompok->min('usia_min'),
            'usiaMax' => $kelompok->max('usia_max'),
            'alamat' => $alamat,
            'gor' => trim(strtok($alamat, ',')),
            'waAdmin' => Klub::get('whatsapp_admin'),
            'tautanWa' => 'https://wa.me/'.$wa.'?text='.rawurlencode('Halo Admin Bandung Spiders, saya ingin bertanya tentang latihan basket untuk anak.'),
            'tautanMaps' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($alamat),
            'petaEmbed' => 'https://maps.google.com/maps?q='.rawurlencode($alamat).'&output=embed',
            'sampul' => Klub::get('foto_sampul'),
            'tentang' => ['judul' => Klub::get('tentang_judul'), 'cerita' => Klub::get('tentang_cerita'), 'foto' => Klub::get('tentang_foto')],
            'linimasa' => Linimasa::orderBy('tahun')->get(),
            'coach' => User::where('peran', User::PERAN_COACH)->where('aktif', true)->where('tampil_di_landing', true)->with('kelompokDipegang')->orderBy('nama')->get(),
            'turnamen' => Turnamen::orderByDesc('tanggal_mulai')->get(),
            'galeri' => Galeri::orderBy('urutan')->orderBy('id')->get(),
            'tanyaJawab' => TanyaJawab::orderBy('urutan')->orderBy('id')->get(),
        ]);
    }
}
