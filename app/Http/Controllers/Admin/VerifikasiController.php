<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuktiTransfer;
use App\Models\Notifikasi;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

// L5 Daftar verifikasi, L6 Detail bukti, L27 Tolak bukti (US2, UC1)
class VerifikasiController extends Controller
{
    private const TAB = ['menunggu', 'disetujui', 'ditolak'];

    public function index(Request $request): View
    {
        $tab = in_array($request->query('status'), self::TAB, true) ? $request->query('status') : 'menunggu';
        $awalBulan = now()->startOfMonth();

        // Menunggu: semua, yang paling lama di atas. Lunas/Ditolak: yang diperiksa bulan ini.
        $dasar = fn ($status) => BuktiTransfer::where('status', $status)
            ->when($status !== 'menunggu', fn ($q) => $q->where('diverifikasi_pada', '>=', $awalBulan));

        $bukti = $dasar($tab)->with('tagihan.anak.kelompokUsia')
            ->when($tab === 'menunggu', fn ($q) => $q->oldest(), fn ($q) => $q->latest('diverifikasi_pada'))
            ->get();

        return view('admin.verifikasi', [
            'tab' => $tab,
            'jumlah' => collect(self::TAB)->mapWithKeys(fn ($s) => [$s => $dasar($s)->count()]),
            'bukti' => $bukti,
        ]);
    }

    public function show(BuktiTransfer $bukti): View
    {
        $bukti->load(['tagihan.anak.kelompokUsia', 'tagihan.anak.orangTua', 'diverifikasiOleh']);
        $sebelumnya = $bukti->tagihan->buktiTransfer()->where('id', '<', $bukti->id)->latest('id')->get();

        return view('admin.detail-bukti', ['bukti' => $bukti, 'sebelumnya' => $sebelumnya]);
    }

    // File bukti disimpan di disk privat; hanya bisa dibuka lewat rute Admin ini
    public function file(BuktiTransfer $bukti): StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($bukti->file_path), 404);

        return Storage::disk('local')->response($bukti->file_path, 'bukti-'.$bukti->id.'.'.$bukti->file_tipe);
    }

    public function setujui(Request $request, BuktiTransfer $bukti): RedirectResponse
    {
        $this->periksa($bukti, function (BuktiTransfer $b) use ($request) {
            $b->update(['status' => 'disetujui', 'diverifikasi_oleh' => $request->user()->id, 'diverifikasi_pada' => now()]);
            $b->tagihan->update(['status' => 'lunas']);
            $this->beriTahu($b, 'iuran_lunas', 'Iuran '.$b->tagihan->periode->translatedFormat('F').' lunas',
                'Pembayaran '.Format::rupiah($b->tagihan->nominal).' untuk '.$b->tagihan->anak->nama.' sudah diterima.');
        });

        return redirect()->route('manajemen.verifikasi')
            ->with('status', 'Iuran '.$bukti->tagihan->periode->translatedFormat('F').' '.$bukti->tagihan->anak->nama.' lunas.');
    }

    public function tolak(Request $request, BuktiTransfer $bukti): RedirectResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'max:200']], [
            'alasan.required' => 'Tulis alasan agar orang tua tahu apa yang perlu diperbaiki.',
            'alasan.max' => 'Alasan maksimal 200 karakter.',
        ]);

        $this->periksa($bukti, function (BuktiTransfer $b) use ($request, $data) {
            $b->update(['status' => 'ditolak', 'alasan_tolak' => $data['alasan'], 'diverifikasi_oleh' => $request->user()->id, 'diverifikasi_pada' => now()]);
            $b->tagihan->update(['status' => 'ditolak']);
            $this->beriTahu($b, 'bukti_ditolak', 'Bukti transfer perlu diunggah ulang', $data['alasan']);
        });

        return redirect()->route('manajemen.verifikasi')
            ->with('status', 'Bukti '.$bukti->tagihan->anak->nama.' ditolak. Orang tua bisa mengunggah ulang.');
    }

    // Bukti dan tagihan berubah bersamaan; bukti yang sudah diperiksa Admin lain tidak diproses lagi
    private function periksa(BuktiTransfer $bukti, callable $ubah): void
    {
        DB::transaction(function () use ($bukti, $ubah) {
            $terkunci = BuktiTransfer::whereKey($bukti->id)->lockForUpdate()->first();
            abort_unless($terkunci->status === 'menunggu', 409, 'Bukti ini sudah diperiksa.');
            $ubah($terkunci->load('tagihan.anak'));
        });
    }

    private function beriTahu(BuktiTransfer $b, string $jenis, string $judul, string $isi): void
    {
        Notifikasi::create([
            'user_id' => $b->tagihan->anak->orang_tua_id, 'jenis' => $jenis, 'judul' => $judul, 'isi' => $isi,
            'tautan' => route('ortu.tagihan', ['anak' => $b->tagihan->anak_id], false),
        ]);
    }
}
