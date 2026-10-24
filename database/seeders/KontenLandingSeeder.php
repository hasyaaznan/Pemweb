<?php

namespace Database\Seeders;

use App\Models\Galeri;
use App\Models\Linimasa;
use App\Models\Pengaturan;
use App\Models\TanyaJawab;
use App\Models\Turnamen;
use Illuminate\Database\Seeder;

// Isi awal halaman depan dari foto, video, dan data asli klub (dikirim Reza 24 Okt 2026).
// Semuanya bisa diubah Admin di menu Profil → Kelola halaman depan.
class KontenLandingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'foto_sampul' => 'images/klub/sampul.webp',
            'tentang_judul' => 'Latihan di GOR Rabbani, Ujung Berung',
            'tentang_cerita' => 'Bandung Spiders melatih anak usia 6–16 tahun di Sportainment Rabbani (GOR Rabbani), Ujung Berung. Anak berlatih bersama teman seusianya di tiga kelompok usia, dan setiap bulan coach menilai perkembangan mereka lewat rapor. Klub juga ikut turnamen antarklub di Bandung.',
            'tentang_foto' => 'images/klub/tentang-rabbani.webp',
        ] as $kunci => $nilai) {
            Pengaturan::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
        }

        Linimasa::create(['tahun' => '2023', 'isi' => 'Dua tim Spiders meraih juara 3 dan juara 4 di BSB Basketball Invitational Tournament, Rabbani Sportainment.']);
        Linimasa::create(['tahun' => '2026', 'isi' => 'Rapor, absensi, dan iuran mulai dicatat di website ini.']);

        Turnamen::create([
            'nama' => 'BSB Basketball Invitational Tournament', 'tanggal_mulai' => '2023-12-20', 'tanggal_selesai' => '2023-12-24',
            'lokasi' => 'Rabbani Sportainment, Bandung', 'hasil' => 'Juara 3 dan juara 4 (dua tim Spiders)', 'foto' => 'images/klub/turnamen-bsb-2023.webp',
        ]);

        Galeri::create(['jenis' => 'video', 'file' => 'video/latihan.mp4', 'poster' => 'images/klub/video-latihan-poster.webp', 'keterangan' => 'Suasana latihan Bandung Spiders', 'urutan' => 1]);
        foreach ([
            ['galeri-coach.webp', 'Coach memberi arahan sebelum latihan'],
            ['galeri-tim-lapangan-kayu.webp', 'Foto tim sebelum pertandingan'],
            ['galeri-tim-dan-orang-tua.webp', 'Pemain dan orang tua di tribun'],
            ['galeri-istirahat.webp', 'Istirahat di sela pertandingan'],
            ['galeri-bsb-2023.webp', 'BSB Basketball Invitational Tournament 2023'],
            ['galeri-latihan-arena.webp', 'Latihan bersama di arena indoor'],
            ['galeri-peregangan.webp', 'Peregangan setelah latihan'],
            ['galeri-turnamen-putih-hitam.webp', 'Tim Spiders di turnamen antarklub'],
        ] as $i => [$file, $keterangan]) {
            Galeri::create(['jenis' => 'foto', 'file' => 'images/klub/'.$file, 'keterangan' => $keterangan, 'urutan' => $i + 2]);
        }

        foreach ([
            ['Anak saya belum pernah main basket. Boleh ikut?', 'Boleh. KU 6–8 mulai dari gerak dasar lewat permainan, dan coach menyesuaikan latihan dengan kemampuan anak.'],
            ['Bagaimana cara membayar iuran?', 'Transfer ke rekening klub, lalu unggah foto bukti transfer di menu Tagihan. Admin memeriksa bukti paling lambat 2 hari kerja.'],
            ['Kalau anak tidak bisa datang latihan?', 'Kabari coach atau Admin lewat WhatsApp sebelum latihan. Coach mencatatnya sebagai Izin di absensi.'],
            ['Apa isi rapor bulanan?', 'Nilai A+ sampai C- untuk 5 kategori: Fundamental, Strategi, Mental, IQ Basket, dan Fisik, ditambah catatan coach untuk tiap kategori.'],
            ['Dokumen apa yang perlu disiapkan saat mendaftar?', 'Foto 3x4, akte kelahiran, dan Kartu Keluarga. Bisa berupa foto atau PDF, masing-masing maksimal 2 MB.'],
        ] as $i => [$t, $j]) {
            TanyaJawab::create(['pertanyaan' => $t, 'jawaban' => $j, 'urutan' => $i + 1]);
        }
    }
}
