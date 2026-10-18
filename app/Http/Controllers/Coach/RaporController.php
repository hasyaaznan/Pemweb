<?php

namespace App\Http\Controllers\Coach;

use App\Models\Anak;
use App\Models\KategoriRapor;
use App\Models\KelompokUsia;
use App\Models\Notifikasi;
use App\Models\Rapor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

// L23 Daftar rapor dan L9 Input rapor: rapor bulan berjalan, 5 kategori, catatan maks. 300 karakter (K3).
// Rapor boleh disimpan sebagai draf; orang tua baru melihatnya setelah kelima kategori dinilai (US3).
class RaporController extends Controller
{
    public const NILAI = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-'];

    public function index(Request $request): View
    {
        $kelompok = $this->kelompok($request);
        $ku = $request->filled('ku') ? $kelompok->firstWhere('id', $request->integer('ku')) : $kelompok->first();
        abort_unless($ku, 403, 'Akses ditolak');

        $anak = $this->anakDenganRapor($ku);
        [$selesai, $belum] = $anak->partition(fn ($a) => $a->rapor_ini?->nilai_rapor_count === 5);

        return view('coach.rapor', [
            'kelompok' => $kelompok,
            'ku' => $ku,
            'periode' => $this->periode(),
            'selesai' => $selesai->values(),
            // Yang sudah mulai diisi tampil paling atas
            'belum' => $belum->sortByDesc(fn ($a) => $a->rapor_ini?->nilai_rapor_count ?? 0)->values(),
        ]);
    }

    public function edit(Request $request, Anak $anak): View
    {
        $this->pastikanAnakDipegang($request, $anak);
        $rapor = $anak->rapor()->whereDate('periode', $this->periode())->with('nilaiRapor')->first();

        return view('coach.isi-rapor', [
            'anak' => $anak->load('kelompokUsia'),
            'periode' => $this->periode(),
            'kategori' => KategoriRapor::orderBy('urutan')->get(),
            'nilai' => $rapor ? $rapor->nilaiRapor->keyBy('kategori_rapor_id') : collect(),
        ]);
    }

    public function update(Request $request, Anak $anak): RedirectResponse
    {
        $this->pastikanAnakDipegang($request, $anak);
        $kategori = KategoriRapor::orderBy('urutan')->get();

        $aturan = ['aksi' => ['nullable', 'in:simpan,lanjut']];
        $pesan = [];
        foreach ($kategori as $k) {
            $aturan["nilai.{$k->id}"] = ['nullable', 'in:'.implode(',', self::NILAI), "required_with:catatan.{$k->id}"];
            $aturan["catatan.{$k->id}"] = ['nullable', 'string', 'max:300'];
            $pesan["nilai.{$k->id}.required_with"] = "Pilih nilai {$k->nama} dulu sebelum menulis catatan.";
            $pesan["catatan.{$k->id}.max"] = "Catatan {$k->nama} maksimal 300 karakter.";
        }
        $data = $request->validate($aturan, $pesan);

        $terbitSekarang = DB::transaction(function () use ($request, $anak, $kategori, $data) {
            $rapor = $anak->rapor()->whereDate('periode', $this->periode())->lockForUpdate()->first()
                ?? Rapor::create(['anak_id' => $anak->id, 'periode' => $this->periode(), 'coach_id' => $request->user()->id]);
            foreach ($kategori as $k) {
                if ($nilai = $data['nilai'][$k->id] ?? null) {
                    $rapor->nilaiRapor()->updateOrCreate(
                        ['kategori_rapor_id' => $k->id],
                        ['nilai' => $nilai, 'catatan' => trim((string) ($data['catatan'][$k->id] ?? '')) ?: null],
                    );
                }
            }

            // Terbit otomatis saat kelima kategori sudah dinilai; notifikasi dikirim sekali saja (K13)
            if ($rapor->status === 'draf' && $rapor->nilaiRapor()->count() === $kategori->count()) {
                $rapor->update(['status' => 'terbit', 'terbit_pada' => now(), 'coach_id' => $request->user()->id]);
                $bulan = $rapor->periode->translatedFormat('F');
                Notifikasi::create([
                    'user_id' => $anak->orang_tua_id, 'jenis' => 'rapor_terbit',
                    'judul' => "Rapor {$bulan} sudah terbit",
                    'isi' => "Coach {$request->user()->nama} sudah menulis rapor {$anak->nama} bulan {$bulan}.",
                    'tautan' => route('ortu.rapor', ['anak' => $anak->id, 'periode' => $rapor->periode->toDateString()], false),
                ]);

                return true;
            }

            return false;
        });

        $kurang = $kategori->count() - $anak->rapor()->whereDate('periode', $this->periode())->first()->nilaiRapor()->count();
        $namaDepan = strtok($anak->nama, ' ');
        $pesanStatus = match (true) {
            $terbitSekarang => "Rapor {$namaDepan} lengkap dan sudah bisa dibaca orang tua.",
            $kurang > 0 => "Rapor {$namaDepan} disimpan sebagai draf. {$kurang} kategori belum dinilai.",
            default => "Perubahan rapor {$namaDepan} tersimpan.",
        };

        if (($data['aksi'] ?? null) === 'lanjut') {
            $berikut = $this->anakDenganRapor($anak->kelompokUsia)
                ->first(fn ($a) => $a->id !== $anak->id && ($a->rapor_ini?->nilai_rapor_count ?? 0) < 5);
            if ($berikut) {
                return redirect()->route('coach.rapor.isi', $berikut)->with('status', $pesanStatus);
            }
        }

        return redirect()->route('coach.rapor', ['ku' => $anak->kelompok_usia_id])->with('status', $pesanStatus);
    }

    // Rapor yang sedang diisi adalah rapor bulan berjalan
    private function periode()
    {
        return today()->startOfMonth();
    }

    private function anakDenganRapor(KelompokUsia $ku)
    {
        $anak = $ku->anak()->where('status', 'aktif')->orderBy('nama')->get();
        $rapor = Rapor::whereIn('anak_id', $anak->pluck('id'))->whereDate('periode', $this->periode())
            ->with(['nilaiRapor.kategoriRapor'])->withCount('nilaiRapor')->get()->keyBy('anak_id');

        return $anak->each(fn ($a) => $a->setAttribute('rapor_ini', $rapor->get($a->id)));
    }
}
