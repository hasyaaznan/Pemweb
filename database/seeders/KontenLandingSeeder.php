<?php

namespace Database\Seeders;

use App\Models\Galeri;
use App\Models\Linimasa;
use App\Models\Pengaturan;
use App\Models\TanyaJawab;
use App\Models\Turnamen;
use Illuminate\Database\Seeder;

// Isi awal halaman depan dari foto, video, dan data asli klub (dikirim Reza 24 dan 26 Okt 2026).
// Semuanya bisa diubah Admin di menu Profil → Kelola data klub dan halaman depan.
// Dipanggil juga oleh migration 2026_10_26_000001 untuk database yang sudah ada, jadi isinya tidak ditambah dua kali.
class KontenLandingSeeder extends Seeder
{
    public const TENTANG_JUDUL = 'Berdiri sejak 27 Agustus 2023';

    public const TENTANG_CERITA = 'Bandung Spiders Basketball didirikan oleh Fahmi Dyah Puspitasari, SST. pada 27 Agustus 2023. Nama Spiders diambil dari spider trick, gerakan dribel freestyle yang butuh tangan cepat, fokus, dan konsisten.

Awalnya sekitar 20 atlet berlatih di Lapangan Beebucks bersama Direktur Teknik Bambang Yudi dan tim pelatih. Sekarang home base kami di GOR Rabbani, Ujung Berung. Sejak awal tujuannya membina atlet usia dini, terutama membentuk karakter anak dan memberi mereka kegiatan yang positif.';

    public function run(): void
    {
        if (Linimasa::exists() || Turnamen::exists() || Galeri::exists() || TanyaJawab::exists()) {
            return;
        }

        foreach ([
            'foto_sampul' => 'images/klub/sampul.webp',
            'tentang_judul' => self::TENTANG_JUDUL,
            'tentang_cerita' => self::TENTANG_CERITA,
            'tentang_foto' => 'images/klub/tentang-rabbani.webp',
        ] as $kunci => $nilai) {
            Pengaturan::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
        }

        Linimasa::create(['tahun' => '2023', 'isi' => '27 Agustus: Bandung Spiders berdiri. Sekitar 20 atlet berlatih di Lapangan Beebucks.']);
        Linimasa::create(['tahun' => '2023', 'isi' => 'Desember: dua tim Spiders meraih juara 3 dan juara 4 di BSB Basketball Invitational Tournament, Rabbani Sportainment.']);
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
