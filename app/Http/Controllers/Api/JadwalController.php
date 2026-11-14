<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\JadwalRequest;
use App\Http\Resources\JadwalResource;
use App\Models\Jadwal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Jadwal latihan mingguan. Semua peran boleh membaca, hanya Admin yang boleh mengubah.
// Filter: ?kelompok_usia_id=2, ?aktif=semua (bawaan hanya jadwal aktif)
class JadwalController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return JadwalResource::collection(Jadwal::with('kelompokUsia')
            ->when($request->query('aktif') !== 'semua', fn ($q) => $q->where('aktif', true))
            ->when($request->integer('kelompok_usia_id'), fn ($q, $id) => $q->where('kelompok_usia_id', $id))
            ->orderBy('hari')->orderBy('jam_mulai')->get());
    }

    public function store(JadwalRequest $request): JsonResponse
    {
        $jadwal = Jadwal::create($request->validated() + ['aktif' => true]);

        return (new JadwalResource($jadwal->load('kelompokUsia')))->response()->setStatusCode(201);
    }

    public function show(Jadwal $jadwal): JadwalResource
    {
        return new JadwalResource($jadwal->load('kelompokUsia'));
    }

    public function update(JadwalRequest $request, Jadwal $jadwal): JadwalResource
    {
        $jadwal->update($request->validated());

        return new JadwalResource($jadwal->refresh()->load('kelompokUsia'));
    }

    // Jadwal yang sudah pernah dipakai latihan tidak dihapus agar riwayat sesi tetap punya asal; nonaktifkan dengan PATCH {"aktif": false}
    public function destroy(Jadwal $jadwal): JsonResponse
    {
        if ($jadwal->sesiLatihan()->exists()) {
            return response()->json(['message' => 'Jadwal ini sudah punya riwayat latihan, jadi tidak dihapus. Nonaktifkan dengan PATCH {"aktif": false}.'], 409);
        }
        $jadwal->delete();

        return response()->json(['message' => 'Jadwal berhasil dihapus.']);
    }
}
