<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as AturanSandi;
use Illuminate\View\View;

// Ubah data profil dan kata sandi sendiri, untuk ketiga peran (K2)
class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profil-ubah', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $request->merge(['no_hp' => Format::noHp((string) $request->input('no_hp'))]);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'no_hp' => ['required', 'regex:/^08\d{8,11}$/', Rule::unique('users')->ignore($user->id)],
            'alamat' => [$user->isOrangTua() ? 'required' : 'nullable', 'string', 'max:255'],
        ], self::PESAN);

        $user->update($data);

        return redirect()->route(self::ruteProfil($user))->with('status', 'Profil disimpan.');
    }

    public function kataSandi(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sandi_lama' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', AturanSandi::min(8)],
        ], [
            'sandi_lama.required' => 'Isi kata sandi yang dipakai sekarang.',
            'sandi_lama.current_password' => 'Kata sandi sekarang tidak cocok.',
            'password.required' => 'Isi kata sandi baru.',
            'password.confirmed' => 'Ulangi kata sandi tidak sama.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $request->user()->update(['password' => $data['password']]);

        return redirect()->route(self::ruteProfil($request->user()))->with('status', 'Kata sandi diganti.');
    }

    public const PESAN = [
        'nama.required' => 'Isi nama.',
        'email.required' => 'Isi email.',
        'email.email' => 'Format email belum benar.',
        'email.unique' => 'Email ini sudah dipakai akun lain.',
        'no_hp.required' => 'Isi No. WhatsApp.',
        'no_hp.regex' => 'No. WhatsApp diawali 08 dan berisi 10–13 angka.',
        'no_hp.unique' => 'No. WhatsApp ini sudah dipakai akun lain.',
        'alamat.required' => 'Isi alamat.',
    ];

    public static function ruteProfil(User $user): string
    {
        return match ($user->peran) {
            User::PERAN_ORANG_TUA => 'ortu.profil',
            User::PERAN_COACH => 'coach.profil',
            default => 'manajemen.profil',
        };
    }
}
