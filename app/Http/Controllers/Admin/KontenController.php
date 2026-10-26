<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use App\Models\Linimasa;
use App\Models\TanyaJawab;
use App\Models\Turnamen;
use App\Support\Berkas;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

// Isi halaman depan (K7, K8): linimasa, turnamen, galeri, dan tanya jawab.
// Keempatnya memakai alur tambah, ubah, hapus yang sama, jadi diatur dari satu daftar JENIS:
// model, judul, kolom formulir (nama => [label, tipe, aturan validasi]), dan urutan tampil.
class KontenController extends Controller
{
    public const JENIS = [
        'linimasa' => [
            'model' => Linimasa::class, 'judul' => 'Linimasa klub', 'satuan' => 'tonggak',
            'keterangan' => 'Tahun-tahun penting klub, tampil di bagian Tentang klub.',
            'urut' => ['tahun', 'asc'], 'utama' => 'tahun', 'kedua' => 'isi',
            'kolom' => [
                'tahun' => ['Tahun', 'text', ['required', 'regex:/^\d{4}$/']],
                'isi' => ['Peristiwa', 'textarea', ['required', 'string', 'max:200']],
            ],
        ],
        'turnamen' => [
            'model' => Turnamen::class, 'judul' => 'Turnamen dan prestasi', 'satuan' => 'turnamen',
            'keterangan' => 'Turnamen atau event yang diikuti klub. Isi hasil setelah acara selesai.',
            'urut' => ['tanggal_mulai', 'desc'], 'utama' => 'nama', 'kedua' => 'hasil',
            'kolom' => [
                'nama' => ['Nama turnamen', 'text', ['required', 'string', 'max:120']],
                'tanggal_mulai' => ['Tanggal mulai', 'date', ['required', 'date']],
                'tanggal_selesai' => ['Tanggal selesai', 'date', ['nullable', 'date', 'after_or_equal:tanggal_mulai']],
                'lokasi' => ['Lokasi', 'text', ['required', 'string', 'max:150']],
                'hasil' => ['Hasil atau prestasi', 'text', ['nullable', 'string', 'max:200']],
                'foto' => ['Foto', 'foto', ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']],
            ],
        ],
        'galeri' => [
            'model' => Galeri::class, 'judul' => 'Galeri foto dan video', 'satuan' => 'foto atau video',
            'keterangan' => 'Urutan kecil tampil lebih dulu. Item pertama tampil besar.',
            'urut' => ['urutan', 'asc'], 'utama' => 'keterangan', 'kedua' => 'jenis',
            'kolom' => [
                'jenis' => ['Jenis', 'select', ['required', 'in:foto,video']],
                'keterangan' => ['Keterangan', 'text', ['required', 'string', 'max:150']],
                'file' => ['File foto atau video', 'berkas', ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,webm', 'max:20480']],
                'youtube' => ['Atau link video YouTube', 'text', ['nullable', 'string', 'max:200']],
                'poster' => ['Gambar sampul video', 'foto', ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']],
                'urutan' => ['Urutan', 'number', ['required', 'integer', 'min:0', 'max:999']],
            ],
        ],
        'tanya-jawab' => [
            'model' => TanyaJawab::class, 'judul' => 'Tanya jawab', 'satuan' => 'pertanyaan',
            'keterangan' => 'Pertanyaan yang sering ditanyakan calon member.',
            'urut' => ['urutan', 'asc'], 'utama' => 'pertanyaan', 'kedua' => 'jawaban',
            'kolom' => [
                'pertanyaan' => ['Pertanyaan', 'text', ['required', 'string', 'max:200']],
                'jawaban' => ['Jawaban', 'textarea', ['required', 'string', 'max:500']],
                'urutan' => ['Urutan', 'number', ['required', 'integer', 'min:0', 'max:999']],
            ],
        ],
    ];

    public function index(string $jenis): View
    {
        $j = $this->jenis($jenis);

        return view('admin.konten', [
            'jenis' => $jenis, 'j' => $j,
            'daftar' => $j['model']::orderBy(...$j['urut'])->orderBy('id')->get(),
        ]);
    }

    public function create(string $jenis): View
    {
        $j = $this->jenis($jenis);
        $baru = new $j['model'];
        if (isset($j['kolom']['urutan'])) {
            $baru->urutan = (int) $j['model']::max('urutan') + 1;
        }

        return view('admin.form-konten', ['jenis' => $jenis, 'j' => $j, 'item' => $baru]);
    }

    public function store(Request $request, string $jenis): RedirectResponse
    {
        $j = $this->jenis($jenis);
        $item = new $j['model'];
        $item->fill($this->isian($request, $jenis, $item))->save();

        return redirect()->route('manajemen.konten', $jenis)->with('status', ucfirst($j['satuan']).' ditambahkan.');
    }

    public function edit(string $jenis, int $id): View
    {
        $j = $this->jenis($jenis);

        return view('admin.form-konten', ['jenis' => $jenis, 'j' => $j, 'item' => $j['model']::findOrFail($id)]);
    }

    public function update(Request $request, string $jenis, int $id): RedirectResponse
    {
        $j = $this->jenis($jenis);
        $item = $j['model']::findOrFail($id);
        $item->fill($this->isian($request, $jenis, $item))->save();

        return redirect()->route('manajemen.konten', $jenis)->with('status', ucfirst($j['satuan']).' diperbarui.');
    }

    public function destroy(string $jenis, int $id): RedirectResponse
    {
        $j = $this->jenis($jenis);
        $item = $j['model']::findOrFail($id);
        foreach (['foto', 'file', 'poster'] as $kolom) {
            Berkas::hapus($item->getAttribute($kolom));
        }
        $item->delete();

        return redirect()->route('manajemen.konten', $jenis)->with('status', ucfirst($j['satuan']).' dihapus.');
    }

    private function jenis(string $jenis): array
    {
        abort_unless(isset(self::JENIS[$jenis]), 404);

        return self::JENIS[$jenis];
    }

    // Validasi lalu ubah isian formulir menjadi kolom tabel; berkas baru disimpan, berkas lama dihapus
    private function isian(Request $request, string $jenis, Model $item): array
    {
        $kolom = self::JENIS[$jenis]['kolom'];
        $data = $request->validate(collect($kolom)->map(fn ($k) => $k[2])->all(), [
            'required' => ':attribute wajib diisi.',
            'tahun.regex' => 'Tahun ditulis 4 angka, misalnya 2023.',
            'after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'image' => ':attribute harus berupa gambar JPG, PNG, atau WebP.',
            'mimes' => ':attribute harus JPG, PNG, WebP, MP4, atau WebM.',
            'file.max' => 'Ukuran file maksimal 20 MB.',
            'max' => ':attribute terlalu panjang atau terlalu besar.',
        ], collect($kolom)->map(fn ($k) => $k[0])->all());

        if ($jenis === 'galeri') {
            $this->periksaGaleri($request, $data, $item);
            $data['youtube'] = $data['jenis'] === 'video' ? $this->idYoutube($data['youtube'] ?? null) : null;
        }

        foreach ($kolom as $nama => [, $tipe]) {
            if (! in_array($tipe, ['foto', 'berkas'], true)) {
                continue;
            }
            unset($data[$nama]);
            if ($request->hasFile($nama)) {
                Berkas::hapus($item->getAttribute($nama));
                $data[$nama] = Berkas::simpan($request->file($nama), $jenis);
            }
        }

        // Video dari link YouTube menggantikan file video lama
        if ($jenis === 'galeri' && $data['youtube'] && ! $request->hasFile('file') && $item->file) {
            Berkas::hapus($item->file);
            $data['file'] = null;
        }

        return $data;
    }

    // Foto wajib punya file gambar; video wajib punya file video atau link YouTube
    private function periksaGaleri(Request $request, array $data, Model $item): void
    {
        $file = $request->file('file');
        $mime = (string) $file?->getMimeType();
        if ($data['jenis'] === 'foto') {
            if ($file && ! str_starts_with($mime, 'image/')) {
                throw ValidationException::withMessages(['file' => 'Untuk jenis foto, pilih file gambar JPG, PNG, atau WebP.']);
            }
            if (! $file && ! ($item->file && $item->jenis === 'foto')) {
                throw ValidationException::withMessages(['file' => 'Pilih foto yang akan ditampilkan.']);
            }

            return;
        }
        if ($file && ! str_starts_with($mime, 'video/')) {
            throw ValidationException::withMessages(['file' => 'Untuk jenis video, pilih file MP4 atau WebM, atau isi link YouTube.']);
        }
        if (! $file && ! ($item->file && $item->jenis === 'video') && empty($data['youtube'])) {
            throw ValidationException::withMessages(['youtube' => 'Pilih file video atau isi link YouTube.']);
        }
    }

    // Menerima link youtube.com/watch?v=, youtu.be/, /shorts/, atau ID saja
    private function idYoutube(?string $masukan): ?string
    {
        if (! $masukan) {
            return null;
        }
        if (preg_match('~(?:v=|youtu\.be/|shorts/|embed/)([\w-]{11})~', $masukan, $m) || preg_match('~^([\w-]{11})$~', trim($masukan), $m)) {
            return $m[1];
        }
        throw ValidationException::withMessages(['youtube' => 'Link YouTube belum dikenali. Salin link dari tombol Bagikan di YouTube.']);
    }
}
