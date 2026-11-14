<?php

namespace App\Http\Controllers;

use App\Services\SpidersApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Kelola member lewat API: GET daftar/detail, POST tambah, PUT ubah, PATCH ubah status, DELETE hapus
class MemberController extends Controller
{
    public function __construct(private readonly SpidersApiService $api) {}

    private function token(Request $request): string
    {
        return (string) $request->session()->get('api_token');
    }

    public function index(Request $request): View|RedirectResponse
    {
        $result = $this->api->getAllMember($this->token($request), array_filter([
            'cari' => $request->query('cari'), 'status' => $request->query('status'), 'page' => $request->query('page'),
        ]));
        if ($result['status'] === 401) {
            return $this->sesiHabis($request);
        }

        if (! $result['success']) {
            session()->now('error', ApiPesan::untuk($result['status']));
        }

        return view('member.index', [
            'members' => $result['data']['data'] ?? [],
            'meta' => $result['data']['meta'] ?? null,
        ]);
    }

    public function show(Request $request, int $id): View|RedirectResponse
    {
        $result = $this->api->getMember($this->token($request), $id);
        if (! $result['success']) {
            return $result['status'] === 401 ? $this->sesiHabis($request) : redirect()->route('member.index')->with('error', ApiPesan::untuk($result['status']));
        }

        return view('member.show', ['member' => $result['data']['data']]);
    }

    public function create(Request $request): View
    {
        return view('member.form', ['member' => null, 'kelompok' => $this->kelompok($request)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $result = $this->api->createMember($this->token($request), $this->validasi($request, true));

        return $this->setelahSimpan($request, $result, 'Member berhasil ditambahkan.');
    }

    public function edit(Request $request, int $id): View|RedirectResponse
    {
        $result = $this->api->getMember($this->token($request), $id);
        if (! $result['success']) {
            return $result['status'] === 401 ? $this->sesiHabis($request) : redirect()->route('member.index')->with('error', ApiPesan::untuk($result['status']));
        }

        return view('member.form', ['member' => $result['data']['data'], 'kelompok' => $this->kelompok($request)]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $result = $this->api->updateMember($this->token($request), $id, $this->validasi($request, false));

        return $this->setelahSimpan($request, $result, 'Member berhasil diubah.');
    }

    // PATCH: hanya mengirim status, kolom lain tidak berubah
    public function status(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:aktif,nonaktif']]);
        $result = $this->api->patchMember($this->token($request), $id, $data);

        return $this->setelahSimpan($request, $result, 'Status member diubah menjadi '.$data['status'].'.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $result = $this->api->deleteMember($this->token($request), $id);
        if ($result['success']) {
            return redirect()->route('member.index')->with('success', $result['data']['message'] ?? 'Member berhasil dihapus.');
        }
        if ($result['status'] === 401) {
            return $this->sesiHabis($request);
        }

        // 409: member punya riwayat, pesan dari API menjelaskan alasannya
        return redirect()->route('member.index')->with('error', $result['status'] === 409 ? $result['data']['message'] : ApiPesan::untuk($result['status']));
    }

    private function validasi(Request $request, bool $baru): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'sekolah' => ['nullable', 'string', 'max:100'],
            'kelompok_usia_id' => ['required', 'integer'],
            'hubungan_orang_tua' => ['required', 'in:Ibu,Ayah,Wali'],
            'status' => [$baru ? 'nullable' : 'required', 'in:aktif,nonaktif'],
            'orang_tua.nama' => ['required', 'string', 'max:100'],
            'orang_tua.email' => ['required', 'email'],
            'orang_tua.no_hp' => ['required', 'string'],
            'orang_tua.alamat' => ['nullable', 'string', 'max:255'],
        ], ['required' => 'Kolom ini wajib diisi.']);

        return array_filter($data, fn ($v) => $v !== null);
    }

    private function setelahSimpan(Request $request, array $result, string $berhasil): RedirectResponse
    {
        if ($result['success']) {
            return redirect()->route('member.show', $result['data']['data']['id'])->with('success', $berhasil);
        }
        if ($result['status'] === 401) {
            return $this->sesiHabis($request);
        }
        if ($result['status'] === 422) {
            return back()->withErrors($result['data']['errors'] ?? [])->withInput();
        }

        return back()->with('error', ApiPesan::untuk($result['status']))->withInput();
    }

    private function kelompok(Request $request): array
    {
        return $this->api->getKelompokUsia($this->token($request))['data']['data'] ?? [];
    }

    private function sesiHabis(Request $request): RedirectResponse
    {
        $request->session()->forget(['api_token', 'pengguna']);

        return redirect()->route('masuk')->with('error', 'Sesi habis. Silakan masuk lagi.');
    }
}
