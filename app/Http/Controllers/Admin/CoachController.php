<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProfilController;
use App\Models\KelompokUsia;
use App\Models\User;
use App\Support\Berkas;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Akun coach: data diri, kelompok yang dipegang (K3, K10), dan tampilan di halaman depan.
// Coach tidak dihapus karena namanya tercatat di rapor; cukup dinonaktifkan supaya tidak bisa masuk.
class CoachController extends Controller
{
    public function index(): View
    {
        return view('admin.coach', [
            'coach' => User::where('peran', User::PERAN_COACH)->with('kelompokDipegang')->orderByDesc('aktif')->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return $this->form(new User(['aktif' => true, 'tampil_di_landing' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);
        $coach = DB::transaction(function () use ($request, $data) {
            $coach = User::create($this->isian($request, $data) + ['peran' => User::PERAN_COACH, 'password' => Str::password(32), 'aktif' => true]);
            $coach->kelompokDipegang()->sync($data['kelompok'] ?? []);

            return $coach;
        });

        // Coach membuat kata sandi sendiri lewat tautan di email, sama seperti orang tua baru
        try {
            Password::sendResetLink(['email' => $coach->email]);
        } catch (\Throwable $e) {
            Log::warning('Email tautan kata sandi coach gagal dikirim', ['coach' => $coach->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('manajemen.coach')->with('status', 'Coach '.$coach->nama.' ditambahkan. Tautan untuk membuat kata sandi dikirim ke '.$coach->email.'.');
    }

    public function edit(User $coach): View
    {
        abort_unless($coach->isCoach(), 404);

        return $this->form($coach);
    }

    public function update(Request $request, User $coach): RedirectResponse
    {
        abort_unless($coach->isCoach(), 404);
        $data = $this->validasi($request, $coach);

        DB::transaction(function () use ($request, $data, $coach) {
            $lama = $coach->foto;
            $coach->update($this->isian($request, $data, $coach) + ['aktif' => $request->boolean('aktif')]);
            $coach->kelompokDipegang()->sync($data['kelompok'] ?? []);
            if ($lama !== $coach->foto) {
                Berkas::hapus($lama);
            }
        });

        return redirect()->route('manajemen.coach')->with('status', 'Data coach '.$coach->nama.' disimpan.');
    }

    private function form(User $coach): View
    {
        return view('admin.form-coach', ['coach' => $coach, 'kelompok' => KelompokUsia::orderBy('usia_min')->get()]);
    }

    private function validasi(Request $request, ?User $coach = null): array
    {
        $request->merge(['no_hp' => Format::noHp((string) $request->input('no_hp'))]);

        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users')->ignore($coach?->id)],
            'no_hp' => ['required', 'regex:/^08\d{8,11}$/', Rule::unique('users')->ignore($coach?->id)],
            'jabatan' => ['nullable', 'string', 'max:60'],
            'lisensi' => ['nullable', 'string', 'max:100'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'kelompok' => ['array'],
            'kelompok.*' => ['exists:kelompok_usia,id'],
        ], ProfilController::PESAN + [
            'foto.image' => 'Foto harus berupa gambar JPG, PNG, atau WebP.',
            'foto.mimes' => 'Foto harus berupa gambar JPG, PNG, atau WebP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);
    }

    private function isian(Request $request, array $data, ?User $coach = null): array
    {
        $isian = collect($data)->only(['nama', 'email', 'no_hp', 'jabatan', 'lisensi'])->all() + ['tampil_di_landing' => $request->boolean('tampil_di_landing')];
        if ($request->hasFile('foto')) {
            $isian['foto'] = Berkas::simpan($request->file('foto'), 'coach');
        } elseif ($coach && $request->boolean('hapus_foto')) {
            $isian['foto'] = null;
        }

        return $isian;
    }
}
