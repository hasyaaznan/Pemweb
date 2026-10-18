<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

// L0 Login: masuk dengan email atau No. HP (K2)
class MasukController extends Controller
{
    public function create(): View
    {
        return view('auth.masuk');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Isi email atau No. HP.',
            'password.required' => 'Isi kata sandi.',
        ]);

        $kunci = Str::lower($data['login']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages([
                'login' => 'Terlalu banyak percobaan. Coba lagi dalam '.RateLimiter::availableIn($kunci).' detik.',
            ]);
        }

        $kredensial = str_contains($data['login'], '@')
            ? ['email' => Str::lower(trim($data['login']))]
            : ['no_hp' => Format::noHp($data['login'])];

        if (! Auth::attempt($kredensial + ['password' => $data['password'], 'aktif' => true], $request->boolean('ingat'))) {
            RateLimiter::hit($kunci);
            throw ValidationException::withMessages([
                'password' => 'Email atau kata sandi salah. Coba lagi atau atur ulang.',
            ]);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();

        return redirect()->intended(route('beranda'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
