<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\KelompokUsia;
use App\Models\Pendaftaran;
use App\Support\Kehadiran;
use Illuminate\Http\Request;
use Illuminate\View\View;

// L21 Daftar member dan detail satu anak (K1, K11)
class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $kelompok = KelompokUsia::orderBy('usia_min')->get();
        $ku = $kelompok->firstWhere('id', $request->integer('ku'));
        $belumBayar = $request->query('iuran') === 'belum';
        $bulanIni = now()->startOfMonth();

        $anak = Anak::where('status', 'aktif')
            ->with(['kelompokUsia', 'tagihan' => fn ($q) => $q->whereDate('periode', $bulanIni)])
            ->when($ku, fn ($q) => $q->where('kelompok_usia_id', $ku->id))
            ->when($belumBayar, fn ($q) => $q->whereHas('tagihan', fn ($t) => $t->whereDate('periode', $bulanIni)->whereIn('status', ['belum_bayar', 'ditolak'])))
            ->orderBy('nama')
            ->get();

        return view('admin.member', [
            'kelompok' => $kelompok,
            'ku' => $ku,
            'belumBayar' => $belumBayar,
            'anak' => $anak,
            'jumlahSemua' => Anak::where('status', 'aktif')->count(),
            'pendaftarBaru' => Pendaftaran::where('status', 'menunggu_verifikasi')->count(),
        ]);
    }

    public function show(Anak $anak): View
    {
        $anak->load(['kelompokUsia', 'orangTua', 'tagihan' => fn ($q) => $q->latest('periode')->limit(6)]);

        return view('admin.detail-member', [
            'anak' => $anak,
            'kehadiran' => Kehadiran::ringkas(Kehadiran::riwayat($anak, Kehadiran::awalTigaBulan())),
        ]);
    }
}
