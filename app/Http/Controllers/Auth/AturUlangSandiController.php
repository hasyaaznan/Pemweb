<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as AturanSandi;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

// Halaman dari tautan email lupa kata sandi
class AturUlangSandiController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.atur-ulang-sandi', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', AturanSandi::min(8)],
        ], [
            'password.required' => 'Isi kata sandi baru.',
            'password.confirmed' => 'Ulangi kata sandi tidak sama.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['password' => 'Tautan sudah tidak berlaku. Minta tautan baru dari halaman lupa kata sandi.']);
        }

        return redirect()->route('login')->with('status', 'Kata sandi sudah diganti. Silakan masuk.');
    }
}
