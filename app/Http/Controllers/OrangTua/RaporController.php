<?php

namespace App\Http\Controllers\OrangTua;

use App\Support\Kehadiran;
use Illuminate\Http\Request;
use Illuminate\View\View;

// L2 Detail rapor dan L17 Absensi anak (K4)
class RaporController extends Controller
{
    public function rapor(Request $request): View
    {
        $anak = $this->anakAktif($request);
        abort_unless($anak, 404);

        $daftar = $anak->rapor()->where('status', 'terbit')->latest('periode')->get(['id', 'periode']);
        $rapor = $request->filled('periode')
            ? $anak->rapor()->where('status', 'terbit')->whereDate('periode', $request->date('periode'))->first()
            : $anak->rapor()->where('status', 'terbit')->latest('periode')->first();
        $rapor?->load(['nilaiRapor' => fn ($q) => $q->join('kategori_rapor', 'kategori_rapor.id', '=', 'nilai_rapor.kategori_rapor_id')
            ->orderBy('kategori_rapor.urutan')->select('nilai_rapor.*', 'kategori_rapor.nama as kategori'), 'coach']);

        return view('ortu.rapor', [
            'anak' => $anak,
            'rapor' => $rapor,
            'daftar' => $daftar,
            'kehadiran' => Kehadiran::ringkas(Kehadiran::riwayat($anak, Kehadiran::awalTigaBulan())),
        ]);
    }

    public function absensi(Request $request): View
    {
        $anak = $this->anakAktif($request);
        abort_unless($anak, 404);
        $dari = Kehadiran::awalTigaBulan();
        $riwayat = Kehadiran::riwayat($anak, $dari);

        return view('ortu.absensi', [
            'anak' => $anak->load('kelompokUsia'),
            'riwayat' => $riwayat,
            'kehadiran' => Kehadiran::ringkas($riwayat),
            'periode' => $dari->translatedFormat('M').'–'.now()->translatedFormat('M Y'),
        ]);
    }
}
