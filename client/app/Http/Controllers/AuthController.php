<?php

namespace App\Http\Controllers;

use App\Services\SpidersApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly SpidersApiService $api) {}

    public function create(): View
    {
        return view('auth.masuk');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['login' => ['required', 'string'], 'password' => ['required', 'string']]);
        $result = $this->api->masuk($data['login'], $data['password']);

        if (! $result['success']) {
            return back()->withInput($request->only('login'))->with('error', match ($result['status']) {
                422 => 'Email/No. HP atau kata sandi salah.',
                503 => ApiPesan::TIDAK_TERHUBUNG,
                default => 'API Server mengembalikan error (HTTP '.$result['status'].').',
            });
        }

        $request->session()->regenerate();
        $request->session()->put(['api_token' => $result['data']['token'], 'pengguna' => $result['data']['data']]);

        return redirect()->route('member.index')->with('success', 'Berhasil masuk sebagai '.$result['data']['data']['nama'].'.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->api->keluar((string) $request->session()->get('api_token'));
        $request->session()->invalidate();

        return redirect()->route('masuk')->with('success', 'Berhasil keluar.');
    }
}
