<?php

namespace App\Http\Controllers\OrangTua;

use App\Models\Tagihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

// L3 Tagihan iuran, L4 Unggah bukti transfer, L26 Bukti terkirim (K5, K11, US2, UC1)
class TagihanController extends Controller
{
    public function index(Request $request): View
    {
        $anak = $this->anakAktif($request);
        abort_unless($anak, 404);

        $tagihan = $anak->tagihan()->latest('periode')
            ->with(['buktiTransfer' => fn ($q) => $q->latest('id')])
            ->get();

        return view('ortu.tagihan', ['anak' => $anak, 'tagihan' => $tagihan]);
    }

    public function unggah(Request $request, Tagihan $tagihan): View|RedirectResponse
    {
        $this->pastikanMilik($request, $tagihan);
        if (! $this->bisaUnggah($tagihan)) {
            return $this->sudahDikirim();
        }

        return view('ortu.unggah-bukti', ['tagihan' => $tagihan->load('anak.kelompokUsia')]);
    }

    public function simpan(Request $request, Tagihan $tagihan): RedirectResponse
    {
        $this->pastikanMilik($request, $tagihan);
        if (! $this->bisaUnggah($tagihan)) {
            return $this->sudahDikirim();
        }

        $data = $request->validate([
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'tanggal_transfer' => ['required', 'date', 'before_or_equal:today'],
            'nominal_transfer' => ['required', 'integer', 'min:1'],
        ], [
            'bukti.required' => 'Pilih file bukti transfer.',
            'bukti.mimes' => 'File harus JPG, PNG, atau PDF maksimal 2 MB.',
            'bukti.max' => 'File harus JPG, PNG, atau PDF maksimal 2 MB.',
            'bukti.uploaded' => 'File harus JPG, PNG, atau PDF maksimal 2 MB.',
            'tanggal_transfer.required' => 'Isi tanggal transfer.',
            'tanggal_transfer.before_or_equal' => 'Tanggal transfer tidak boleh setelah hari ini.',
            'nominal_transfer.required' => 'Isi nominal yang ditransfer.',
            'nominal_transfer.integer' => 'Nominal hanya angka, tanpa titik.',
        ]);

        $file = $request->file('bukti');
        $tipe = str_replace('jpeg', 'jpg', strtolower($file->getClientOriginalExtension() ?: $file->extension()));
        $path = $file->storeAs('bukti/'.$tagihan->id, Str::uuid().'.'.$tipe, 'local');

        // Bukti dan status tagihan berubah bersamaan; satu tagihan hanya punya satu bukti aktif
        DB::transaction(function () use ($request, $tagihan, $data, $file, $tipe, $path) {
            $terkunci = Tagihan::whereKey($tagihan->id)->lockForUpdate()->first();
            abort_unless($this->bisaUnggah($terkunci), 409, 'Bukti untuk tagihan ini sudah dikirim.');

            $terkunci->buktiTransfer()->create([
                'diunggah_oleh' => $request->user()->id,
                'file_path' => $path,
                'file_tipe' => $tipe,
                'ukuran_kb' => (int) ceil($file->getSize() / 1024),
                'tanggal_transfer' => $data['tanggal_transfer'],
                'nominal_transfer' => $data['nominal_transfer'],
            ]);
            $terkunci->update(['status' => 'menunggu_verifikasi']);
        });

        return redirect()->route('ortu.tagihan.terkirim', $tagihan);
    }

    public function terkirim(Request $request, Tagihan $tagihan): View
    {
        $this->pastikanMilik($request, $tagihan);
        $bukti = $tagihan->buktiTransfer()->latest('id')->firstOrFail();

        return view('ortu.bukti-terkirim', ['tagihan' => $tagihan, 'bukti' => $bukti]);
    }

    private function pastikanMilik(Request $request, Tagihan $tagihan): void
    {
        abort_unless($tagihan->anak()->where('orang_tua_id', $request->user()->id)->exists(), 403, 'Akses ditolak');
    }

    // Contoh: orang tua menekan tombol kembali setelah bukti terkirim, atau tagihan sudah lunas
    private function sudahDikirim(): RedirectResponse
    {
        return redirect()->route('ortu.tagihan')->with('info', 'Tagihan ini tidak menunggu bukti transfer.');
    }

    private function bisaUnggah(Tagihan $tagihan): bool
    {
        return in_array($tagihan->status, ['belum_bayar', 'ditolak'], true);
    }
}
