<?php

// Isi tambahan landing page (L10): sejarah klub, coach, siswa berprestasi, galeri, dan tanya jawab.
// SEMUA ISI DI BAWAH MASIH CONTOH. Foto dari Unsplash (lisensi gratis), nama dan cerita karangan.
// Ganti dengan cerita, foto, dan video asli dari klub, lalu ubah 'contoh' menjadi false
// supaya label "Isi contoh" di halaman hilang.
return [
    'contoh' => true,

    'sejarah' => [
        'judul' => 'Dari satu ring di halaman sekolah',
        'cerita' => 'Bandung Spiders mulai dari latihan sore untuk anak-anak di sekitar Dago. Pesertanya delapan anak dan satu ring pinjaman. Sekarang latihan berjalan di GOR untuk tiga kelompok usia, dengan rapor bulanan dan absensi yang bisa dicek orang tua.',
        'foto' => 'images/contoh/sejarah-awal.webp',
        'linimasa' => [
            ['tahun' => '2015', 'isi' => 'Latihan pertama, 8 anak, di lapangan SD di Dago.'],
            ['tahun' => '2017', 'isi' => 'Pindah ke GOR dan membuka kelompok KU 9–12.'],
            ['tahun' => '2019', 'isi' => 'Ikut turnamen antarklub pertama di Bandung.'],
            ['tahun' => '2022', 'isi' => 'Membuka KU 13–16 untuk persiapan turnamen pelajar.'],
            ['tahun' => '2026', 'isi' => 'Rapor, absensi, dan iuran pindah ke website ini.'],
        ],
    ],

    'coach' => [
        ['nama' => 'Coach Dita', 'peran' => 'Kepala pelatih', 'kelompok' => 'KU 6–8 dan KU 9–12', 'lisensi' => 'Lisensi Perbasi C'],
        ['nama' => 'Coach Bima', 'peran' => 'Pelatih', 'kelompok' => 'KU 13–16', 'lisensi' => 'Lisensi Perbasi C'],
        ['nama' => 'Coach Laras', 'peran' => 'Pelatih fisik', 'kelompok' => 'Semua kelompok', 'lisensi' => 'Sertifikat pelatih fisik'],
    ],

    'prestasi' => [
        ['nama' => 'Naufal R.', 'kelompok' => 'KU 13–16', 'prestasi' => 'Pemain terbaik Liga Pelajar Bandung 2025', 'foto' => 'images/contoh/prestasi-1.webp'],
        ['nama' => 'Alya P.', 'kelompok' => 'KU 9–12', 'prestasi' => 'Terpilih tim Kota Bandung U-12 2025', 'foto' => 'images/contoh/prestasi-3.webp'],
        ['nama' => 'Rafi A.', 'kelompok' => 'KU 9–12', 'prestasi' => 'Juara 3-point contest Spiders Cup 2025', 'foto' => 'images/contoh/prestasi-2.webp'],
        ['nama' => 'Tim KU 13–16', 'kelompok' => 'KU 13–16', 'prestasi' => 'Juara 2 turnamen antarklub Bandung 2024', 'foto' => 'images/contoh/prestasi-4.webp'],
    ],

    // jenis: foto atau video. Video boleh file mp4 di public/ ('src', ditambah 'webm' bila ada) atau YouTube ('youtube' berisi ID video).
    'galeri' => [
        ['jenis' => 'video', 'src' => 'video/contoh-latihan.mp4', 'webm' => 'video/contoh-latihan.webm', 'poster' => 'images/contoh/video-poster.webp', 'keterangan' => 'Cuplikan latihan Sabtu pagi'],
        ['jenis' => 'foto', 'src' => 'images/contoh/galeri-2.webp', 'keterangan' => 'Selebrasi KU 9–12 setelah pertandingan'],
        ['jenis' => 'foto', 'src' => 'images/contoh/galeri-1.webp', 'keterangan' => 'Sebelum latihan dimulai'],
        ['jenis' => 'foto', 'src' => 'images/contoh/galeri-3.webp', 'keterangan' => 'Tos dengan coach setelah sesi'],
        ['jenis' => 'foto', 'src' => 'images/contoh/galeri-5.webp', 'keterangan' => 'Latihan KU 6–8 di GOR'],
        ['jenis' => 'foto', 'src' => 'images/contoh/galeri-7.webp', 'keterangan' => 'Latihan dribbling'],
        ['jenis' => 'foto', 'src' => 'images/contoh/galeri-6.webp', 'keterangan' => 'Briefing sebelum pertandingan'],
        ['jenis' => 'foto', 'src' => 'images/contoh/galeri-8.webp', 'keterangan' => 'Sparring KU 13–16'],
        ['jenis' => 'foto', 'src' => 'images/contoh/galeri-9.webp', 'keterangan' => 'Arahan coach saat jeda'],
    ],

    'tanya_jawab' => [
        ['t' => 'Anak saya belum pernah main basket. Boleh ikut?', 'j' => 'Boleh. KU 6–8 mulai dari gerak dasar lewat permainan, dan coach menyesuaikan latihan dengan kemampuan anak.'],
        ['t' => 'Bagaimana cara membayar iuran?', 'j' => 'Transfer ke rekening klub, lalu unggah foto bukti transfer di menu Tagihan. Admin memeriksa bukti paling lambat 2 hari kerja.'],
        ['t' => 'Kalau anak tidak bisa datang latihan?', 'j' => 'Kabari coach atau Admin lewat WhatsApp sebelum latihan. Coach mencatatnya sebagai Izin di absensi.'],
        ['t' => 'Apa isi rapor bulanan?', 'j' => 'Nilai A+ sampai C- untuk 5 kategori: Fundamental, Strategi, Mental, IQ Basket, dan Fisik, ditambah catatan coach untuk tiap kategori.'],
        ['t' => 'Dokumen apa yang perlu disiapkan saat mendaftar?', 'j' => 'Foto 3x4, akte kelahiran, dan Kartu Keluarga. Bisa berupa foto atau PDF, masing-masing maksimal 2 MB.'],
    ],
];
