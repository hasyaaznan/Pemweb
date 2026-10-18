<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\DokumenPendaftaran;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Pemeriksaan pendaftar baru oleh Admin (K1): cek dokumen, lalu Terima (akun orang tua + data anak dibuat) atau Tolak
class PendaftaranController extends Controller
{
    public function index(): View
    {
        return view('admin.pendaftaran', [
            'menunggu' => Pendaftaran::where('status', 'menunggu_verifikasi')->with('kelompokUsia')->oldest()->get(),
            'selesai' => Pendaftaran::where('status', '!=', 'menunggu_verifikasi')->with('kelompokUsia')->latest('diverifikasi_pada')->limit(10)->get(),
        ]);
    }

    public function show(Pendaftaran $pendaftaran): View
    {
        return view('admin.detail-pendaftaran', [
            'p' => $pendaftaran->load(['kelompokUsia', 'dokumenPendaftaran', 'diverifikasiOleh', 'anak']),
        ]);
    }

    public function dokumen(DokumenPendaftaran $dokumen): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($dokumen->file_path), 404);

        return Storage::disk('local')->response($dokumen->file_path, $dokumen->jenis.'-'.$dokumen->pendaftaran_id.'.'.$dokumen->file_tipe);
    }

    public function terima(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $akunBaru = false;
        DB::transaction(function () use ($request, $pendaftaran, &$akunBaru) {
            $p = $this->kunci($pendaftaran);

            // Orang tua yang sudah punya akun (misalnya kakak sudah member) memakai akun yang sama
            $ortu = User::where('email', $p->email)->orWhere('no_hp', $p->no_whatsapp)->first();
            if ($ortu && ! $ortu->isOrangTua()) {
                throw ValidationException::withMessages(['terima' => 'Email atau No. WhatsApp ini sudah dipakai akun coach atau Admin. Hubungi pendaftar untuk memakai kontak lain.']);
            }
            if (! $ortu) {
                $ortu = User::create([
                    'nama' => $p->nama_orang_tua, 'email' => $p->email, 'no_hp' => $p->no_whatsapp, 'alamat' => $p->alamat,
                    'password' => Str::password(32), 'peran' => User::PERAN_ORANG_TUA,
                ]);
                $akunBaru = true;
            }

            $anak = Anak::create([
                'orang_tua_id' => $ortu->id, 'kelompok_usia_id' => $p->kelompok_usia_id, 'nama' => $p->nama_anak,
                'tanggal_lahir' => $p->tanggal_lahir, 'jenis_kelamin' => $p->jenis_kelamin, 'sekolah' => $p->sekolah,
                'hubungan_orang_tua' => $p->hubungan, 'tanggal_masuk' => today(),
            ]);
            $p->update(['status' => 'diterima', 'anak_id' => $anak->id, 'diverifikasi_oleh' => $request->user()->id, 'diverifikasi_pada' => now()]);
        });

        // Orang tua membuat kata sandi sendiri lewat tautan di email (sama dengan alur Lupa kata sandi)
        if ($akunBaru) {
            try {
                Password::sendResetLink(['email' => $pendaftaran->email]);
            } catch (\Throwable $e) {
                Log::warning('Email tautan kata sandi gagal dikirim', ['pendaftaran' => $pendaftaran->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->route('manajemen.pendaftaran.detail', $pendaftaran)
            ->with('status', $pendaftaran->nama_anak.' diterima sebagai member. Kabari orang tuanya lewat WhatsApp.');
    }

    public function tolak(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'max:200']], [
            'alasan.required' => 'Tulis alasan agar pendaftar tahu apa yang perlu diperbaiki.',
            'alasan.max' => 'Alasan maksimal 200 karakter.',
        ]);

        DB::transaction(function () use ($request, $pendaftaran, $data) {
            $this->kunci($pendaftaran)->update([
                'status' => 'ditolak', 'alasan_tolak' => $data['alasan'], 'diverifikasi_oleh' => $request->user()->id, 'diverifikasi_pada' => now(),
            ]);
        });

        return redirect()->route('manajemen.pendaftaran.detail', $pendaftaran)
            ->with('status', 'Pendaftaran '.$pendaftaran->nama_anak.' ditolak. Kabari pendaftar lewat WhatsApp.');
    }

    private function kunci(Pendaftaran $pendaftaran): Pendaftaran
    {
        $p = Pendaftaran::whereKey($pendaftaran->id)->lockForUpdate()->first();
        abort_unless($p->status === 'menunggu_verifikasi', 409, 'Pendaftaran ini sudah diperiksa.');

        return $p;
    }
}
