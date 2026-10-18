<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\KategoriRapor;
use App\Models\KelompokUsia;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Data awal dari mockup v2. Akun contoh memakai kata sandi "password",
// hanya untuk uji coba lokal; ganti sebelum dipakai klub.
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $ku = collect([
            ['nama' => 'KU 6–8', 'usia_min' => 6, 'usia_max' => 8, 'iuran_bulanan' => 200000],
            ['nama' => 'KU 9–12', 'usia_min' => 9, 'usia_max' => 12, 'iuran_bulanan' => 250000],
            ['nama' => 'KU 13–16', 'usia_min' => 13, 'usia_max' => 16, 'iuran_bulanan' => 300000],
        ])->map(fn ($k) => KelompokUsia::create($k));

        foreach (['Fundamental', 'Strategi', 'Mental', 'IQ Basket', 'Fisik'] as $i => $nama) {
            KategoriRapor::create(['nama' => $nama, 'urutan' => $i + 1]);
        }

        foreach ([
            'rekening_bank' => 'BCA',
            'rekening_nomor' => '1234567890',
            'rekening_atas_nama' => 'Bandung Spiders Basketball',
            'whatsapp_admin' => '081234560000',
            'alamat_gor' => 'GOR Spiders, Jl. Pajajaran No. 88, Bandung',
            'tanggal_jatuh_tempo' => '10',
            'batas_verifikasi_hari_kerja' => '2',
        ] as $kunci => $nilai) {
            Pengaturan::create(['kunci' => $kunci, 'nilai' => $nilai]);
        }

        User::create(['nama' => 'Sari', 'email' => 'admin@spiders.test', 'no_hp' => '081234560000', 'password' => 'password', 'peran' => User::PERAN_MANAJEMEN]);
        $coach = User::create(['nama' => 'Dita', 'email' => 'dita@spiders.test', 'no_hp' => '081200000002', 'password' => 'password', 'peran' => User::PERAN_COACH]);
        $coach->kelompokDipegang()->attach([$ku[0]->id, $ku[1]->id]);
        $rina = User::create(['nama' => 'Rina Pratama', 'email' => 'rina.pratama@gmail.com', 'no_hp' => '081234567890', 'password' => 'password', 'peran' => User::PERAN_ORANG_TUA, 'alamat' => 'Jl. Setiabudi No. 21, Bandung']);

        Anak::create([
            'orang_tua_id' => $rina->id, 'kelompok_usia_id' => $ku[1]->id, 'nama' => 'Raka Pratama',
            'tanggal_lahir' => '2016-03-14', 'jenis_kelamin' => 'L', 'sekolah' => 'SDN 12 Bandung',
            'hubungan_orang_tua' => 'Ibu', 'tanggal_masuk' => '2025-01-11',
        ]);

        // Jadwal Oktober 2026 (hari: 6 = Sabtu, 7 = Minggu)
        foreach ([[0, 6, '07:00', '08:30'], [1, 6, '08:30', '10:00'], [1, 7, '08:30', '10:00'], [2, 6, '15:00', '17:00'], [2, 7, '15:00', '17:00']] as [$k, $hari, $mulai, $selesai]) {
            Jadwal::create([
                'kelompok_usia_id' => $ku[$k]->id, 'hari' => $hari, 'jam_mulai' => $mulai, 'jam_selesai' => $selesai,
                'lokasi' => 'GOR Spiders, Lapangan A', 'berlaku_mulai' => '2026-10-01',
            ]);
        }

        // Data contoh alur orang tua. Test memanggilnya sendiri dengan tanggal yang dibekukan.
        if (! app()->runningUnitTests()) {
            $this->call(DataDemoSeeder::class);
        }
    }
}
