<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelompokUsia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

// Kelompok usia dan iuran bulanannya (K5, K7). Rentang usia tidak boleh tumpang tindih,
// karena kelompok calon member dipilih otomatis dari tanggal lahir (K1).
class KelompokUsiaController extends Controller
{
    public function index(): View
    {
        return view('admin.kelompok-usia', [
            'kelompok' => KelompokUsia::orderBy('usia_min')->withCount(['anak' => fn ($q) => $q->where('status', 'aktif'), 'jadwal' => fn ($q) => $q->where('aktif', true)])->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.form-kelompok-usia', ['ku' => new KelompokUsia]);
    }

    public function store(Request $request): RedirectResponse
    {
        $ku = KelompokUsia::create($this->validasi($request));

        return redirect()->route('manajemen.kelompok')->with('status', $ku->nama.' ditambahkan.');
    }

    public function edit(KelompokUsia $kelompok): View
    {
        return view('admin.form-kelompok-usia', ['ku' => $kelompok]);
    }

    public function update(Request $request, KelompokUsia $kelompok): RedirectResponse
    {
        $kelompok->update($this->validasi($request, $kelompok));

        return redirect()->route('manajemen.kelompok')->with('status', $kelompok->nama.' diperbarui.');
    }

    // Hanya kelompok yang belum pernah dipakai yang bisa dihapus, supaya riwayat anak, jadwal, dan tagihan tetap utuh
    public function destroy(KelompokUsia $kelompok): RedirectResponse
    {
        if ($kelompok->anak()->exists() || $kelompok->pendaftaran()->exists() || $kelompok->jadwal()->exists() || $kelompok->sesiLatihan()->exists()) {
            return back()->withErrors(['hapus' => $kelompok->nama.' sudah punya anggota, pendaftar, atau jadwal, jadi tidak bisa dihapus. Pindahkan anggotanya dulu, atau ubah saja nama dan rentang usianya.']);
        }
        $kelompok->coach()->detach();
        $kelompok->delete();

        return redirect()->route('manajemen.kelompok')->with('status', $kelompok->nama.' dihapus.');
    }

    private function validasi(Request $request, ?KelompokUsia $ku = null): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:20', Rule::unique('kelompok_usia')->ignore($ku?->id)],
            'usia_min' => ['required', 'integer', 'between:3,20'],
            'usia_max' => ['required', 'integer', 'between:3,20', 'gte:usia_min'],
            'iuran_bulanan' => ['required', 'integer', 'min:0', 'max:10000000'],
            'program' => ['nullable', 'string', 'max:100'],
        ], [
            'nama.required' => 'Isi nama kelompok, misalnya KU 6–8.',
            'nama.unique' => 'Nama kelompok ini sudah ada.',
            'usia_min.required' => 'Isi usia termuda.',
            'usia_max.required' => 'Isi usia tertua.',
            'usia_min.between' => 'Usia antara 3 dan 20 tahun.',
            'usia_max.between' => 'Usia antara 3 dan 20 tahun.',
            'usia_max.gte' => 'Usia tertua tidak boleh lebih kecil dari usia termuda.',
            'iuran_bulanan.required' => 'Isi iuran per bulan.',
            'iuran_bulanan.integer' => 'Iuran ditulis angka saja, tanpa titik. Contoh: 200000.',
        ]);

        $bentrok = KelompokUsia::where('usia_min', '<=', $data['usia_max'])->where('usia_max', '>=', $data['usia_min'])
            ->when($ku, fn ($q) => $q->whereKeyNot($ku->id))->first();
        if ($bentrok) {
            throw ValidationException::withMessages(['usia_min' => 'Rentang usia bertabrakan dengan '.$bentrok->nama.' ('.$bentrok->usia_min.'–'.$bentrok->usia_max.' tahun).']);
        }

        return $data;
    }
}
