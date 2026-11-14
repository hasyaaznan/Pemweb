<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CoachRequest;
use App\Http\Resources\CoachResource;
use App\Models\Absensi;
use App\Models\Rapor;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

// Data master coach (akun dengan peran coach). Hanya Admin.
class CoachController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CoachResource::collection(User::where('peran', User::PERAN_COACH)->with('kelompokDipegang')->orderBy('nama')->get());
    }

    public function store(CoachRequest $request): JsonResponse
    {
        $data = $request->validated();
        $coach = DB::transaction(function () use ($data) {
            $coach = User::create(collect($data)->only(['nama', 'email', 'no_hp', 'jabatan', 'lisensi', 'tampil_di_landing', 'aktif'])->all() + [
                'peran' => User::PERAN_COACH,
                'password' => $data['password'] ?? Str::password(32),
                'aktif' => $data['aktif'] ?? true,
            ]);
            $coach->kelompokDipegang()->sync($data['kelompok_usia_ids'] ?? []);

            return $coach;
        });

        // Tanpa kata sandi awal, coach membuatnya sendiri lewat tautan email (sama seperti di website)
        if (empty($data['password'])) {
            try {
                Password::sendResetLink(['email' => $coach->email]);
            } catch (\Throwable $e) {
                Log::warning('Email tautan kata sandi coach gagal dikirim', ['coach' => $coach->id, 'error' => $e->getMessage()]);
            }
        }

        return (new CoachResource($coach->load('kelompokDipegang')))->response()->setStatusCode(201);
    }

    public function show(User $coach): CoachResource
    {
        abort_unless($coach->isCoach(), 404);

        return new CoachResource($coach->load('kelompokDipegang'));
    }

    public function update(CoachRequest $request, User $coach): CoachResource
    {
        abort_unless($coach->isCoach(), 404);
        $data = $request->validated();

        DB::transaction(function () use ($coach, $data) {
            $coach->update(collect($data)->only(['nama', 'email', 'no_hp', 'jabatan', 'lisensi', 'tampil_di_landing', 'aktif', 'password'])->filter(fn ($v, $k) => $k !== 'password' || $v)->all());
            if (array_key_exists('kelompok_usia_ids', $data)) {
                $coach->kelompokDipegang()->sync($data['kelompok_usia_ids']);
            }
            // Coach yang dinonaktifkan langsung keluar dari semua perangkat
            if (! $coach->aktif) {
                $coach->tokens()->delete();
            }
        });

        return new CoachResource($coach->refresh()->load('kelompokDipegang'));
    }

    // Coach yang sudah menulis rapor atau absensi tidak dihapus karena namanya tercatat di sana; nonaktifkan saja
    public function destroy(User $coach): JsonResponse
    {
        abort_unless($coach->isCoach(), 404);
        if (Rapor::where('coach_id', $coach->id)->exists() || Absensi::where('dicatat_oleh', $coach->id)->exists()) {
            return response()->json(['message' => "Coach {$coach->nama} sudah menulis rapor atau absensi, jadi tidak dihapus. Nonaktifkan dengan PATCH {\"aktif\": false}."], 409);
        }
        DB::transaction(function () use ($coach) {
            $coach->tokens()->delete();
            $coach->kelompokDipegang()->detach();
            $coach->delete();
        });

        return response()->json(['message' => "Coach {$coach->nama} berhasil dihapus."]);
    }
}
