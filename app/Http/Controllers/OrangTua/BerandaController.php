<?php

namespace App\Http\Controllers\OrangTua;

use App\Models\Tagihan;
use App\Support\Kehadiran;
use Illuminate\Http\Request;
use Illuminate\View\View;

// L1 Beranda orang tua: rapor terbaru, kehadiran 3 bulan, tagihan bulan ini
class BerandaController extends Controller
{
    public function __invoke(Request $request): View
    {
        $anak = $this->anakAktif($request);
        if (! $anak) {
            return view('ortu.tanpa-anak');
        }

        $anak->load('kelompokUsia.coach');
        $rapor = $anak->rapor()->where('status', 'terbit')->latest('periode')
            ->with(['nilaiRapor.kategoriRapor', 'coach'])->first();
        $kehadiran = Kehadiran::ringkas(Kehadiran::riwayat($anak, Kehadiran::awalTigaBulan()));

        // Tagihan yang perlu perhatian: yang belum lunas paling lama, kalau tidak ada tampilkan bulan ini
        $tagihan = $anak->tagihan()->whereIn('status', ['belum_bayar', 'ditolak', 'menunggu_verifikasi'])->oldest('periode')->first()
            ?? $anak->tagihan()->latest('periode')->first();

        return view('ortu.beranda', [
            'anak' => $anak,
            'semuaAnak' => $request->user()->anak()->orderBy('nama')->get(),
            'rapor' => $rapor,
            'kehadiran' => $kehadiran,
            'tagihan' => $tagihan,
        ]);
    }
}
