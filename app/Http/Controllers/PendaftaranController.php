<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\KelompokUsia;
use App\Models\Pendaftaran;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

// L13 Data anak, L14 Orang tua, L11 Dokumen, L15 Terkirim (K1). Satu formulir, tiga langkah di layar.
class PendaftaranController extends Controller
{
    public const DOKUMEN = ['foto_3x4' => 'Foto 3x4', 'akte' => 'Akte kelahiran', 'kk' => 'Kartu Keluarga'];

    public const HUBUNGAN = ['Ibu', 'Ayah', 'Wali'];

    public function create(): View
    {
        return view('pendaftaran.formulir', [
            'kelompok' => KelompokUsia::orderBy('usia_min')->get(['id', 'nama', 'usia_min', 'usia_max', 'iuran_bulanan']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $berkas = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'];
        $request->merge(['no_whatsapp' => Format::noHp((string) $request->input('no_whatsapp')), 'email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'nama_anak' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'sekolah' => ['required', 'string', 'max:100'],
            'nama_orang_tua' => ['required', 'string', 'max:100'],
            'hubungan' => ['required', 'in:'.implode(',', self::HUBUNGAN)],
            'no_whatsapp' => ['required', 'regex:/^08\d{8,12}$/'],
            'email' => ['required', 'email', 'max:100'],
            'alamat' => ['required', 'string', 'max:255'],
            'foto_3x4' => $berkas,
            'akte' => $berkas,
            'kk' => $berkas,
        ], [
            'nama_anak.required' => 'Isi nama lengkap anak.',
            'tanggal_lahir.required' => 'Isi tanggal lahir anak.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'jenis_kelamin.required' => 'Pilih jenis kelamin.',
            'sekolah.required' => 'Isi nama sekolah.',
            'nama_orang_tua.required' => 'Isi nama orang tua atau wali.',
            'hubungan.required' => 'Pilih hubungan dengan anak.',
            'no_whatsapp.required' => 'Isi nomor WhatsApp.',
            'no_whatsapp.regex' => 'Nomor WhatsApp diawali 08 dan berisi 10–14 angka.',
            'email.required' => 'Isi email untuk login.',
            'email.email' => 'Format email belum benar, contoh: nama@gmail.com.',
            '*.required' => 'Dokumen ini belum diunggah.',
            '*.mimes' => 'File harus JPG, PNG, atau PDF maksimal 2 MB.',
            '*.max' => 'File harus JPG, PNG, atau PDF maksimal 2 MB.',
            '*.uploaded' => 'File harus JPG, PNG, atau PDF maksimal 2 MB.',
        ]);

        $ku = self::kelompokUntuk(Carbon::parse($data['tanggal_lahir']));
        if (! $ku) {
            throw ValidationException::withMessages(['tanggal_lahir' => 'Program kami untuk anak usia 6–16 tahun.']);
        }
        $sama = fn ($q) => $q->where('nama', $data['nama_anak'])->whereDate('tanggal_lahir', $data['tanggal_lahir']);
        if (Anak::where($sama)->exists()) {
            throw ValidationException::withMessages(['nama_anak' => 'Anak ini sudah terdaftar sebagai member. Hubungi Admin jika ada perubahan data.']);
        }
        if (Pendaftaran::where('status', 'menunggu_verifikasi')->where('nama_anak', $data['nama_anak'])->whereDate('tanggal_lahir', $data['tanggal_lahir'])->exists()) {
            throw ValidationException::withMessages(['nama_anak' => 'Pendaftaran untuk anak ini sudah dikirim dan sedang diperiksa Admin.']);
        }

        $pendaftaran = DB::transaction(function () use ($request, $data, $ku) {
            $p = Pendaftaran::create(collect($data)->except(array_keys(self::DOKUMEN))->all() + ['kelompok_usia_id' => $ku->id]);
            foreach (array_keys(self::DOKUMEN) as $jenis) {
                $file = $request->file($jenis);
                $tipe = str_replace('jpeg', 'jpg', strtolower($file->getClientOriginalExtension() ?: $file->extension()));
                $p->dokumenPendaftaran()->create([
                    'jenis' => $jenis,
                    'file_path' => $file->storeAs('pendaftaran/'.$p->id, $jenis.'-'.Str::uuid().'.'.$tipe, 'local'),
                    'file_tipe' => $tipe,
                    'ukuran_kb' => (int) ceil($file->getSize() / 1024),
                ]);
            }

            return $p;
        });

        return redirect()->route('daftar.terkirim')->with('pendaftaran_id', $pendaftaran->id);
    }

    public function terkirim(Request $request): View|RedirectResponse
    {
        $pendaftaran = Pendaftaran::with('kelompokUsia')->find($request->session()->get('pendaftaran_id'));

        return $pendaftaran ? view('pendaftaran.terkirim', ['pendaftaran' => $pendaftaran]) : redirect()->route('landing');
    }

    // Kelompok usia ditentukan dari usia anak (tahun penuh) pada hari mendaftar
    public static function kelompokUntuk(Carbon $lahir): ?KelompokUsia
    {
        $usia = $lahir->age;

        return KelompokUsia::where('usia_min', '<=', $usia)->where('usia_max', '>=', $usia)->first();
    }

    public static function nomor(Pendaftaran $p): string
    {
        return 'BS-'.$p->created_at->format('Y').'-'.str_pad((string) $p->id, 4, '0', STR_PAD_LEFT);
    }
}
