<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\KelompokUsia;
use App\Models\Pendaftaran;
use App\Http\Controllers\PendaftaranController;
use App\Support\Format;
use App\Support\Kehadiran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

        $nonaktif = $request->query('status') === 'nonaktif';

        $anak = Anak::where('status', $nonaktif ? 'nonaktif' : 'aktif')
            ->with(['kelompokUsia', 'tagihan' => fn ($q) => $q->whereDate('periode', $bulanIni)])
            ->when($ku, fn ($q) => $q->where('kelompok_usia_id', $ku->id))
            ->when($belumBayar, fn ($q) => $q->whereHas('tagihan', fn ($t) => $t->whereDate('periode', $bulanIni)->whereIn('status', ['belum_bayar', 'ditolak'])))
            ->orderBy('nama')
            ->get();

        return view('admin.member', [
            'kelompok' => $kelompok,
            'ku' => $ku,
            'belumBayar' => $belumBayar,
            'nonaktif' => $nonaktif,
            'jumlahNonaktif' => Anak::where('status', 'nonaktif')->count(),
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

    // Ubah data anak dan kontak orang tuanya. Anak yang berhenti dinonaktifkan, tidak dihapus,
    // supaya rapor, absensi, dan riwayat iurannya tetap tersimpan.
    public function edit(Anak $anak): View
    {
        return view('admin.form-member', ['anak' => $anak->load('orangTua'), 'kelompok' => KelompokUsia::orderBy('usia_min')->get()]);
    }

    public function update(Request $request, Anak $anak): RedirectResponse
    {
        $ortu = $anak->orangTua;
        $request->merge(['ortu_no_hp' => Format::noHp((string) $request->input('ortu_no_hp'))]);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'sekolah' => ['nullable', 'string', 'max:100'],
            'kelompok_usia_id' => ['required', 'exists:kelompok_usia,id'],
            'hubungan_orang_tua' => ['required', 'in:'.implode(',', PendaftaranController::HUBUNGAN)],
            'status' => ['required', 'in:aktif,nonaktif'],
            'ortu_nama' => ['required', 'string', 'max:100'],
            'ortu_no_hp' => ['required', 'regex:/^08\d{8,11}$/', Rule::unique('users', 'no_hp')->ignore($ortu->id)],
            'ortu_email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($ortu->id)],
            'ortu_alamat' => ['nullable', 'string', 'max:255'],
        ], [
            'required' => 'Kolom ini wajib diisi.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'ortu_no_hp.regex' => 'No. WhatsApp diawali 08 dan berisi 10–13 angka.',
            'ortu_no_hp.unique' => 'No. WhatsApp ini sudah dipakai akun lain.',
            'ortu_email.email' => 'Format email belum benar.',
            'ortu_email.unique' => 'Email ini sudah dipakai akun lain.',
        ]);

        DB::transaction(function () use ($anak, $ortu, $data) {
            $anak->update(collect($data)->only(['nama', 'tanggal_lahir', 'jenis_kelamin', 'sekolah', 'kelompok_usia_id', 'hubungan_orang_tua', 'status'])->all());
            $ortu->update(['nama' => $data['ortu_nama'], 'no_hp' => $data['ortu_no_hp'], 'email' => $data['ortu_email'], 'alamat' => $data['ortu_alamat']]);
        });

        return redirect()->route('manajemen.member.detail', $anak)->with('status', 'Data '.$anak->nama.' disimpan.');
    }
}
