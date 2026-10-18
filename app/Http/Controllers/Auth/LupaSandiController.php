<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

// L16 Lupa kata sandi: tautan atur ulang dikirim ke email akun, berlaku 30 menit (K2)
class LupaSandiController extends Controller
{
    public function create(): View
    {
        return view('auth.lupa-sandi');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['login' => ['required', 'string', 'max:100']], ['login.required' => 'Isi email atau No. HP yang terdaftar.']);

        $user = str_contains($data['login'], '@')
            ? User::where('email', Str::lower(trim($data['login'])))->first()
            : User::where('no_hp', Format::noHp($data['login']))->first();

        if ($user && $user->aktif) {
            Password::sendResetLink(['email' => $user->email]);
        }

        // Pesan sama untuk akun terdaftar atau tidak, supaya orang lain tidak bisa menebak siapa saja member
        return back()->withInput()->with('terkirim', true);
    }
}
