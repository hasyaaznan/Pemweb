<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PenggunaResource;
use App\Models\User;
use App\Support\Format;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

// Login API memakai token (Laravel Sanctum). Token dikirim di header: Authorization: Bearer <token>
class AuthController extends Controller
{
    public function masuk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string'],
            'perangkat' => ['nullable', 'string', 'max:60'],
        ], [
            'login.required' => 'Isi email atau No. HP.',
            'password.required' => 'Isi kata sandi.',
        ]);

        $user = str_contains($data['login'], '@')
            ? User::where('email', strtolower(trim($data['login'])))->first()
            : User::where('no_hp', Format::noHp($data['login']))->first();

        if (! $user || ! $user->aktif || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['login' => 'Email/No. HP atau kata sandi salah.']);
        }

        $token = $user->createToken($data['perangkat'] ?? 'aplikasi')->plainTextToken;

        return response()->json([
            'message' => 'Berhasil masuk.',
            'token' => $token,
            'token_type' => 'Bearer',
            'data' => new PenggunaResource($user),
        ]);
    }

    public function saya(Request $request): PenggunaResource
    {
        return new PenggunaResource($request->user());
    }

    // Hanya token yang sedang dipakai yang dicabut; perangkat lain tetap masuk
    public function keluar(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar. Token sudah tidak berlaku.']);
    }
}
