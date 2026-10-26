<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\KelompokUsia;
use App\Support\JadwalLatihan;
use App\Support\Klub;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Jadwal latihan: pola mingguan per kelompok usia, dan daftar latihan 4 minggu ke depan untuk diubah per tanggal
class JadwalController extends Controller
{
    public const HARI = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

    public function index(): View
    {
        $kelompok = KelompokUsia::orderBy('usia_min')
            ->with(['jadwal' => fn ($q) => $q->where('aktif', true)->orderBy('hari')->orderBy('jam_mulai')])
            ->get();

        return view('admin.jadwal', [
            'kelompok' => $kelompok,
            'sesi' => JadwalLatihan::rentang($kelompok, today(), today()->addDays(27)),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->form(new Jadwal([
            'kelompok_usia_id' => $request->integer('ku') ?: null, 'hari' => 6,
            'lokasi' => trim(strtok((string) Klub::get('alamat_gor', 'GOR'), ',')), 'berlaku_mulai' => today(),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $jadwal = Jadwal::create($this->validasi($request) + ['aktif' => true]);

        return redirect()->route('manajemen.jadwal')->with('status', 'Jadwal '.$this->label($jadwal).' ditambahkan.');
    }

    public function edit(Jadwal $jadwal): View
    {
        abort_unless($jadwal->aktif, 404);

        return $this->form($jadwal);
    }

    public function update(Request $request, Jadwal $jadwal): RedirectResponse
    {
        abort_unless($jadwal->aktif, 404);
        $jadwal->update($this->validasi($request, $jadwal));

        return redirect()->route('manajemen.jadwal')->with('status', 'Jadwal '.$this->label($jadwal).' diperbarui.');
    }

    // Jadwal tidak dihapus dari database agar riwayat sesi tetap punya asal; cukup dinonaktifkan
    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->update(['aktif' => false]);

        return redirect()->route('manajemen.jadwal')->with('status', 'Jadwal '.$this->label($jadwal).' dihapus.');
    }

    private function form(Jadwal $jadwal): View
    {
        return view('admin.form-jadwal', [
            'jadwal' => $jadwal,
            'kelompok' => KelompokUsia::orderBy('usia_min')->get(),
            'hari' => self::HARI,
        ]);
    }

    private function validasi(Request $request, ?Jadwal $jadwal = null): array
    {
        return $request->validate([
            'kelompok_usia_id' => ['required', 'exists:kelompok_usia,id'],
            // Satu kelompok usia hanya punya satu latihan per hari (sesi_latihan unik per KU dan tanggal)
            'hari' => ['required', 'integer', 'between:1,7', Rule::unique('jadwal')->where(fn ($q) => $q
                ->where('kelompok_usia_id', $request->input('kelompok_usia_id'))->where('aktif', true))->ignore($jadwal?->id)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'lokasi' => ['required', 'string', 'max:100'],
            'berlaku_mulai' => ['required', 'date'],
        ], [
            'kelompok_usia_id.required' => 'Pilih kelompok usia.',
            'hari.unique' => 'Kelompok ini sudah punya latihan di hari tersebut. Ubah jadwal yang ada.',
            'jam_mulai.required' => 'Isi jam mulai.',
            'jam_selesai.required' => 'Isi jam selesai.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'lokasi.required' => 'Isi tempat latihan.',
            'berlaku_mulai.required' => 'Isi tanggal mulai berlaku.',
        ]);
    }

    private function label(Jadwal $jadwal): string
    {
        return $jadwal->kelompokUsia->nama.' hari '.self::HARI[$jadwal->hari];
    }
}
