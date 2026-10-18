<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\BuktiTransfer;
use App\Models\KelompokUsia;
use App\Models\Pendaftaran;
use App\Models\Tagihan;
use App\Support\JadwalLatihan;
use App\Support\TagihanBulanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// L20 Beranda Admin: ringkasan member dan iuran, lalu daftar hal yang perlu ditindaklanjuti (K5, K11)
class BerandaController extends Controller
{
    public function __invoke(): View
    {
        $bulanIni = now()->startOfMonth();
        $bulanLalu = $bulanIni->copy()->subMonth();
        $tagihanLalu = Tagihan::whereDate('periode', $bulanLalu)->get();

        $sesiMingguIni = JadwalLatihan::rentang(KelompokUsia::orderBy('usia_min')->get(), today(), today()->addDays(6));

        return view('admin.beranda', [
            'memberAktif' => Anak::where('status', 'aktif')->count(),
            'bulanLalu' => $bulanLalu,
            'persenLunas' => $tagihanLalu->count() ? (int) round(100 * $tagihanLalu->where('status', 'lunas')->count() / $tagihanLalu->count()) : null,
            'buktiMenunggu' => BuktiTransfer::where('status', 'menunggu')->count(),
            'bulanIni' => $bulanIni,
            'belumBayar' => Tagihan::whereDate('periode', $bulanIni)->whereIn('status', ['belum_bayar', 'ditolak'])->count(),
            'jatuhTempo' => Tagihan::whereDate('periode', $bulanIni)->min('jatuh_tempo'),
            'pendaftarBaru' => Pendaftaran::where('status', 'menunggu_verifikasi')->count(),
            'belumDitagih' => TagihanBulanan::belumDitagih($bulanIni)->count(),
            'sesiMingguIni' => $sesiMingguIni,
        ]);
    }

    // Cadangan jika cron tanggal 1 belum jalan di server: Admin membuat tagihan bulan ini dari beranda
    public function buatTagihan(): RedirectResponse
    {
        $jumlah = TagihanBulanan::buat(now());

        return back()->with('status', $jumlah.' tagihan '.now()->translatedFormat('F').' dibuat dan orang tua sudah diberi tahu.');
    }
}
