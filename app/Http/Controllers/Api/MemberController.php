<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Anak;
use App\Models\User;
use App\Support\AksesData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

// Data master member (anak + orang tua).
// Baca: Admin semua member, coach member di kelompoknya, orang tua anaknya sendiri.
// Tambah, ubah, hapus: hanya Admin.
// Filter daftar: ?status=aktif|nonaktif, ?kelompok_usia_id=2, ?cari=raka, ?per_page=20
class MemberController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $member = AksesData::anak($request->user())
            ->with(['kelompokUsia', 'orangTua'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->integer('kelompok_usia_id'), fn ($q, $id) => $q->where('kelompok_usia_id', $id))
            ->when($request->query('cari'), fn ($q, $cari) => $q->where('nama', 'like', '%'.$cari.'%'))
            ->orderBy('nama')
            ->paginate(min(max($request->integer('per_page', 20), 1), 100))
            ->withQueryString();

        return MemberResource::collection($member);
    }

    public function store(MemberRequest $request): JsonResponse
    {
        $data = $request->validated();
        $ortuBaru = false;

        $anak = DB::transaction(function () use ($request, $data, &$ortuBaru) {
            $ortu = $request->orangTuaLama();
            if (! $ortu) {
                $ortuBaru = true;
                $ortu = User::create([
                    'nama' => $data['orang_tua']['nama'], 'email' => strtolower($data['orang_tua']['email']),
                    'no_hp' => $data['orang_tua']['no_hp'], 'alamat' => $data['orang_tua']['alamat'] ?? null,
                    'password' => $data['orang_tua']['password'] ?? Str::password(32), 'peran' => User::PERAN_ORANG_TUA,
                ]);
            }

            return Anak::create(collect($data)->except('orang_tua')->all() + [
                'orang_tua_id' => $ortu->id, 'status' => 'aktif', 'tanggal_masuk' => today(),
            ]);
        });

        // Orang tua baru tanpa kata sandi awal membuatnya sendiri lewat tautan email
        if ($ortuBaru && empty($data['orang_tua']['password'])) {
            try {
                Password::sendResetLink(['email' => $anak->orangTua->email]);
            } catch (\Throwable $e) {
                Log::warning('Email tautan kata sandi orang tua gagal dikirim', ['anak' => $anak->id, 'error' => $e->getMessage()]);
            }
        }

        return (new MemberResource($anak->load(['kelompokUsia', 'orangTua'])))->response()->setStatusCode(201);
    }

    public function show(Request $request, Anak $member): MemberResource
    {
        abort_unless(AksesData::bolehLihatAnak($request->user(), $member->id), 404);

        return new MemberResource($member->load(['kelompokUsia', 'orangTua']));
    }

    public function update(MemberRequest $request, Anak $member): MemberResource
    {
        $data = $request->validated();

        DB::transaction(function () use ($member, $data) {
            $member->update(collect($data)->except('orang_tua')->all());
            $ortu = collect($data['orang_tua'])->only(['nama', 'email', 'no_hp', 'alamat'])->all();
            if (! empty($data['orang_tua']['password'])) {
                $ortu['password'] = $data['orang_tua']['password'];
            }
            $member->orangTua->update($ortu);
        });

        return new MemberResource($member->refresh()->load(['kelompokUsia', 'orangTua']));
    }

    // Member yang sudah punya absensi, rapor, tagihan, atau berkas pendaftaran tidak dihapus,
    // supaya riwayatnya tetap tersimpan. Untuk anak yang berhenti, pakai PATCH {"status": "nonaktif"}.
    public function destroy(Anak $member): JsonResponse
    {
        if ($member->absensi()->exists() || $member->rapor()->exists() || $member->tagihan()->exists() || $member->pendaftaran()->exists()) {
            return response()->json(['message' => "{$member->nama} sudah punya riwayat (absensi, rapor, iuran, atau pendaftaran), jadi tidak dihapus. Nonaktifkan dengan PATCH {\"status\": \"nonaktif\"}."], 409);
        }
        $member->delete();

        return response()->json(['message' => "Member {$member->nama} berhasil dihapus."]);
    }
}
