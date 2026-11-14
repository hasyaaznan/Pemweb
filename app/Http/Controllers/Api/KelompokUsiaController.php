<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\KelompokUsiaRequest;
use App\Http\Resources\KelompokUsiaResource;
use App\Models\KelompokUsia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

// Data master kelompok usia dan iurannya. Semua peran boleh membaca, hanya Admin yang boleh mengubah.
class KelompokUsiaController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return KelompokUsiaResource::collection(
            KelompokUsia::withCount(['anak' => fn ($q) => $q->where('status', 'aktif')])->orderBy('usia_min')->get()
        );
    }

    public function store(KelompokUsiaRequest $request): JsonResponse
    {
        $ku = KelompokUsia::create($request->validated());

        return (new KelompokUsiaResource($ku))->response()->setStatusCode(201);
    }

    public function show(KelompokUsia $kelompok_usia): KelompokUsiaResource
    {
        return new KelompokUsiaResource($kelompok_usia->loadCount(['anak' => fn ($q) => $q->where('status', 'aktif')]));
    }

    public function update(KelompokUsiaRequest $request, KelompokUsia $kelompok_usia): KelompokUsiaResource
    {
        $kelompok_usia->update($request->validated());

        return new KelompokUsiaResource($kelompok_usia->refresh());
    }

    // Kelompok yang sudah punya anggota, pendaftar, atau jadwal tidak dihapus agar riwayatnya utuh
    public function destroy(KelompokUsia $kelompok_usia): JsonResponse
    {
        if ($kelompok_usia->anak()->exists() || $kelompok_usia->pendaftaran()->exists() || $kelompok_usia->jadwal()->exists() || $kelompok_usia->sesiLatihan()->exists()) {
            return response()->json(['message' => "{$kelompok_usia->nama} sudah punya anggota, pendaftar, atau jadwal, jadi tidak bisa dihapus."], 409);
        }
        $kelompok_usia->coach()->detach();
        $kelompok_usia->delete();

        return response()->json(['message' => "Kelompok usia {$kelompok_usia->nama} berhasil dihapus."]);
    }
}
