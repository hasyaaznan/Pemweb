<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Support\Berkas;
use App\Support\Format;
use App\Support\Klub;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Pengaturan klub (rekening, WhatsApp Admin, alamat GOR, jatuh tempo) dan teks utama halaman depan
class PengaturanController extends Controller
{
    // kunci => [label, aturan validasi]
    public const KLUB = [
        'rekening_bank' => ['Nama bank', ['required', 'string', 'max:30']],
        'rekening_nomor' => ['Nomor rekening', ['required', 'regex:/^[\d\s-]{6,25}$/']],
        'rekening_atas_nama' => ['Atas nama', ['required', 'string', 'max:100']],
        'whatsapp_admin' => ['No. WhatsApp Admin', ['required', 'regex:/^08\d{8,11}$/']],
        'alamat_gor' => ['Alamat tempat latihan', ['required', 'string', 'max:200']],
        'tanggal_jatuh_tempo' => ['Tanggal jatuh tempo iuran', ['required', 'integer', 'between:1,28']],
        'batas_verifikasi_hari_kerja' => ['Batas verifikasi bukti (hari kerja)', ['required', 'integer', 'between:1,14']],
    ];

    public const FOTO = ['foto_sampul' => 'Foto sampul halaman depan', 'tentang_foto' => 'Foto bagian Tentang klub'];

    public function edit(): View
    {
        return view('admin.pengaturan', ['nilai' => Pengaturan::pluck('nilai', 'kunci')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge(['whatsapp_admin' => Format::noHp((string) $request->input('whatsapp_admin'))]);
        $data = $request->validate(
            collect(self::KLUB)->map(fn ($k) => $k[1])->all() + [
                'tentang_judul' => ['required', 'string', 'max:80'],
                'tentang_cerita' => ['required', 'string', 'max:600'],
                'foto_sampul' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                'tentang_foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            ],
            [
                'required' => 'Kolom ini wajib diisi.',
                'rekening_nomor.regex' => 'Nomor rekening berisi angka, 6–25 digit.',
                'whatsapp_admin.regex' => 'No. WhatsApp diawali 08 dan berisi 10–13 angka.',
                'tanggal_jatuh_tempo.between' => 'Pilih tanggal 1 sampai 28 supaya ada di setiap bulan.',
                'batas_verifikasi_hari_kerja.between' => 'Isi 1 sampai 14 hari kerja.',
                'image' => 'Foto harus berupa gambar JPG, PNG, atau WebP.',
                'mimes' => 'Foto harus berupa gambar JPG, PNG, atau WebP.',
                'max' => 'Isian terlalu panjang atau berkas terlalu besar (foto maksimal 4 MB).',
            ],
        );

        foreach (collect($data)->except(array_keys(self::FOTO)) as $kunci => $nilai) {
            Pengaturan::updateOrCreate(['kunci' => $kunci], ['nilai' => (string) $nilai]);
        }
        foreach (array_keys(self::FOTO) as $kunci) {
            if ($request->hasFile($kunci)) {
                Berkas::hapus(Klub::get($kunci));
                Pengaturan::updateOrCreate(['kunci' => $kunci], ['nilai' => Berkas::simpan($request->file($kunci), 'halaman-depan')]);
            }
        }
        Klub::lupakan();

        return redirect()->route('manajemen.pengaturan')->with('status', 'Pengaturan disimpan.');
    }
}
