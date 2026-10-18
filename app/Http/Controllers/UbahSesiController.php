<?php

namespace App\Http\Controllers;

use App\Models\KelompokUsia;
use App\Models\Notifikasi;
use App\Models\SesiLatihan;
use App\Support\Format;
use App\Support\JadwalLatihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// Ubah atau batalkan latihan pada satu tanggal, oleh Admin atau coach kelompok itu.
// Orang tua anak di kelompok tersebut mendapat notifikasi jadwal_berubah (K13).
class UbahSesiController extends Controller
{
    public function edit(Request $request, KelompokUsia $kelompok, string $tanggal): View
    {
        $sesi = $this->sesi($request, $kelompok, $tanggal);

        return view('jadwal-sesi', [
            'sesi' => $sesi,
            'adaAbsensi' => $sesi->exists && $sesi->absensi()->exists(),
            'peran' => $request->user()->peran,
        ]);
    }

    public function update(Request $request, KelompokUsia $kelompok, string $tanggal): RedirectResponse
    {
        $sesi = $this->sesi($request, $kelompok, $tanggal);
        $aksi = $request->validate(['aksi' => ['required', 'in:ubah,batal,kembalikan']])['aksi'];

        $data = match ($aksi) {
            'ubah' => $request->validate([
                'jam_mulai' => ['required', 'date_format:H:i'],
                'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
                'lokasi' => ['required', 'string', 'max:100'],
                'alasan' => ['required', 'string', 'max:150'],
            ], $this->pesan()),
            'batal' => $request->validate(['alasan' => ['required', 'string', 'max:150']], $this->pesan()),
            'kembalikan' => [],
        };

        if ($aksi === 'batal' && $sesi->exists && $sesi->absensi()->exists()) {
            return back()->withErrors(['aksi' => 'Absensi latihan ini sudah diisi, jadi latihan tidak bisa dibatalkan.']);
        }

        DB::transaction(function () use ($request, $sesi, $aksi, $data, $kelompok) {
            $sesi = JadwalLatihan::simpan($sesi);
            $asli = JadwalLatihan::dariJadwal($kelompok, $sesi->tanggal);

            $sesi->update(match ($aksi) {
                'ubah' => [
                    'jam_mulai' => $data['jam_mulai'], 'jam_selesai' => $data['jam_selesai'], 'lokasi' => $data['lokasi'],
                    'status' => 'diubah', 'alasan_perubahan' => $data['alasan'], 'diubah_oleh' => $request->user()->id,
                ],
                'batal' => ['status' => 'batal', 'alasan_perubahan' => $data['alasan'], 'diubah_oleh' => $request->user()->id],
                'kembalikan' => [
                    'jam_mulai' => $asli?->jam_mulai ?? $sesi->jam_mulai, 'jam_selesai' => $asli?->jam_selesai ?? $sesi->jam_selesai,
                    'lokasi' => $asli?->lokasi ?? $sesi->lokasi, 'status' => 'terjadwal', 'alasan_perubahan' => null, 'diubah_oleh' => $request->user()->id,
                ],
            });

            $this->beriTahu($sesi->fresh(), $kelompok, $aksi);
        });

        $tgl = Carbon::parse($tanggal)->translatedFormat('l j F');
        $pesan = match ($aksi) {
            'ubah' => "Latihan {$kelompok->nama} {$tgl} diubah.",
            'batal' => "Latihan {$kelompok->nama} {$tgl} dibatalkan.",
            'kembalikan' => "Latihan {$kelompok->nama} {$tgl} kembali sesuai jadwal.",
        };

        return redirect()->route($request->user()->isCoach() ? 'coach.jadwal' : 'manajemen.jadwal', $request->user()->isCoach() ? ['tanggal' => $tanggal] : [])
            ->with('status', $pesan.' Orang tua sudah diberi tahu.');
    }

    // Hanya latihan hari ini atau setelahnya; coach hanya untuk kelompok yang ia pegang
    private function sesi(Request $request, KelompokUsia $kelompok, string $tanggal): SesiLatihan
    {
        if ($request->user()->isCoach()) {
            abort_unless($request->user()->kelompokDipegang()->whereKey($kelompok->id)->exists(), 403, 'Akses ditolak');
        }
        abort_unless(preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal), 404);
        $tgl = Carbon::parse($tanggal)->startOfDay();
        abort_if($tgl->isBefore(today()), 403, 'Latihan yang sudah lewat tidak bisa diubah.');

        $sesi = JadwalLatihan::sesi($kelompok, $tgl);
        abort_unless($sesi, 404);

        return $sesi->setRelation('kelompokUsia', $kelompok);
    }

    private function beriTahu(SesiLatihan $sesi, KelompokUsia $kelompok, string $aksi): void
    {
        $tgl = $sesi->tanggal->translatedFormat('l j F');
        [$judul, $isi] = match ($aksi) {
            'ubah' => ["Jadwal {$kelompok->nama} berubah", "{$tgl}: ".Format::jam($sesi->jam_mulai).'–'.Format::jam($sesi->jam_selesai).", {$sesi->lokasi}. {$sesi->alasan_perubahan}"],
            'batal' => ["Latihan {$kelompok->nama} batal", "{$tgl} tidak ada latihan. {$sesi->alasan_perubahan}"],
            'kembalikan' => ["Latihan {$kelompok->nama} sesuai jadwal", "{$tgl}: ".Format::jam($sesi->jam_mulai).'–'.Format::jam($sesi->jam_selesai).", {$sesi->lokasi}."],
        };

        $orangTua = $kelompok->anak()->where('status', 'aktif')->distinct()->pluck('orang_tua_id');
        foreach ($orangTua as $id) {
            Notifikasi::create(['user_id' => $id, 'jenis' => 'jadwal_berubah', 'judul' => $judul, 'isi' => mb_strimwidth($isi, 0, 255), 'tautan' => route('ortu.beranda', [], false)]);
        }
    }

    private function pesan(): array
    {
        return [
            'jam_mulai.required' => 'Isi jam mulai.',
            'jam_selesai.required' => 'Isi jam selesai.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'lokasi.required' => 'Isi tempat latihan.',
            'alasan.required' => 'Tulis alasannya. Orang tua akan membaca alasan ini.',
            'alasan.max' => 'Alasan maksimal 150 karakter.',
        ];
    }
}
