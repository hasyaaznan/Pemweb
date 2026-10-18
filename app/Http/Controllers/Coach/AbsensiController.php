<?php

namespace App\Http\Controllers\Coach;

use App\Models\Absensi;
use App\Models\KelompokUsia;
use App\Models\SesiLatihan;
use App\Support\JadwalLatihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// L8 Input absensi: semua anak bawaan Hadir, coach cukup mengubah yang tidak hadir (K3, K9)
class AbsensiController extends Controller
{
    public function edit(Request $request, KelompokUsia $kelompok, string $tanggal): View
    {
        $sesi = $this->sesiBisaDiabsen($request, $kelompok, $tanggal);
        $tercatat = $sesi->exists ? $sesi->absensi()->pluck('status', 'anak_id') : collect();

        return view('coach.absensi', [
            'kelompok' => $kelompok,
            'sesi' => $sesi,
            'anak' => $kelompok->anak()->where('status', 'aktif')->orderBy('nama')->get(),
            'tercatat' => $tercatat,
        ]);
    }

    public function update(Request $request, KelompokUsia $kelompok, string $tanggal): RedirectResponse
    {
        $sesi = $this->sesiBisaDiabsen($request, $kelompok, $tanggal);
        $idAnak = $kelompok->anak()->where('status', 'aktif')->pluck('id');

        $data = $request->validate([
            'status' => ['required', 'array'],
            'status.*' => ['required', Rule::in(['hadir', 'izin', 'alpa'])],
        ]);
        // Hanya anak aktif di kelompok ini yang boleh diabsen
        $status = collect($data['status'])->only($idAnak->all());
        abort_if($status->count() !== count($data['status']), 422, 'Ada anak yang bukan anggota kelompok ini.');

        DB::transaction(function () use ($request, $sesi, $status) {
            $sesi = JadwalLatihan::simpan($sesi);
            foreach ($status as $anakId => $s) {
                Absensi::updateOrCreate(
                    ['sesi_latihan_id' => $sesi->id, 'anak_id' => $anakId],
                    ['status' => $s, 'dicatat_oleh' => $request->user()->id],
                );
            }
        });

        $hitung = $status->countBy();

        return redirect()->route('coach.beranda', ['tanggal' => $tanggal])->with('status', sprintf(
            'Absensi %s tersimpan: %d hadir, %d izin, %d alpa.',
            $kelompok->nama, $hitung->get('hadir', 0), $hitung->get('izin', 0), $hitung->get('alpa', 0),
        ));
    }

    // Absensi hanya untuk latihan yang ada, tidak batal, dan tanggalnya sudah tiba
    private function sesiBisaDiabsen(Request $request, KelompokUsia $kelompok, string $tanggal): SesiLatihan
    {
        $this->pastikanPegang($request, $kelompok);
        abort_unless(preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal), 404);
        $tgl = Carbon::parse($tanggal);

        $sesi = JadwalLatihan::sesi($kelompok, $tgl);
        abort_unless($sesi, 404, 'Tidak ada latihan pada tanggal ini.');
        abort_if($sesi->status === 'batal', 404, 'Latihan ini dibatalkan.');
        abort_if($tgl->isAfter(today()), 403, 'Absensi baru bisa diisi pada hari latihan.');

        return $sesi;
    }
}
