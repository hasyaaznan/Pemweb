<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\KategoriRapor;
use App\Models\KelompokUsia;
use App\Models\Rapor;
use App\Models\SesiLatihan;
use App\Models\Tagihan;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Data contoh untuk mencoba alur orang tua (L1–L4, L17) dan coach (L7–L9, L23, L24), dihitung dari hari ini:
// 20 sesi terakhir (Raka 17 hadir = 85%), rapor bulan lalu, 3 tagihan dengan status berbeda,
// teman sekelompok Raka, rapor bulan ini yang sebagian sudah diisi, dan satu perubahan jadwal.
class DataDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $raka = Anak::where('nama', 'Raka Pratama')->firstOrFail();
        $dita = User::where('email', 'dita@spiders.test')->firstOrFail();
        $sari = User::where('email', 'admin@spiders.test')->firstOrFail();
        $rina = $raka->orangTua;

        // 20 sesi Sabtu/Minggu terakhir sebelum hari ini
        $tanggal = now()->subDay()->startOfDay();
        $sesi = collect();
        while ($sesi->count() < 20) {
            if ($tanggal->isWeekend()) {
                $sesi->push($tanggal->copy());
            }
            $tanggal->subDay();
        }
        $teman = $this->temanLatihan($raka);
        $statusKe = [3 => 'izin', 9 => 'izin', 14 => 'alpa'];
        foreach ($sesi as $i => $tgl) {
            $s = SesiLatihan::create([
                'kelompok_usia_id' => $raka->kelompok_usia_id, 'tanggal' => $tgl->toDateString(),
                'jam_mulai' => '08:30', 'jam_selesai' => '10:00', 'lokasi' => 'GOR Spiders, Lapangan A',
            ]);
            $s->absensi()->create(['anak_id' => $raka->id, 'status' => $statusKe[$i] ?? 'hadir', 'dicatat_oleh' => $dita->id]);
            foreach ($teman as $j => $t) {
                $s->absensi()->create(['anak_id' => $t->id, 'status' => ($i + $j) % 9 === 4 ? 'izin' : 'hadir', 'dicatat_oleh' => $dita->id]);
            }
        }

        // Minggu depan KU 9–12 mulai lebih siang (L24)
        $minggu = now()->next(CarbonInterface::SUNDAY);
        SesiLatihan::create([
            'kelompok_usia_id' => $raka->kelompok_usia_id, 'tanggal' => $minggu->toDateString(),
            'jam_mulai' => '09:00', 'jam_selesai' => '10:30', 'lokasi' => 'GOR Spiders, Lapangan A',
            'status' => 'diubah', 'alasan_perubahan' => 'GOR dipakai acara pagi', 'diubah_oleh' => $sari->id,
        ]);

        // Rapor bulan lalu, terbit tanggal 3 bulan ini
        $bulanIni = now()->startOfMonth();
        $rapor = Rapor::create([
            'anak_id' => $raka->id, 'coach_id' => $dita->id, 'periode' => $bulanIni->copy()->subMonth()->toDateString(),
            'status' => 'terbit', 'terbit_pada' => $bulanIni->copy()->addDays(2)->setTime(19, 0),
        ]);
        $nilai = [
            'Fundamental' => ['B+', "Dribbling: sudah lancar dengan tangan kanan, tangan kiri masih sering lihat bola.\nPassing: chest pass akurat, bounce pass perlu latihan.\nShooting: form bagus, jarak dekat 6 dari 10 masuk."],
            'Strategi' => ['B', 'Sudah paham posisi saat menyerang, masih telat turun saat bertahan.'],
            'Mental' => ['A-', 'Tetap semangat walau tertinggal skor, mau menyemangati teman.'],
            'IQ Basket' => ['B', 'Mulai bisa membaca kapan harus passing, masih sering memaksa tembakan.'],
            'Fisik' => ['A', 'Lari bolak-balik lapangan 10 kali tanpa berhenti.'],
        ];
        foreach (KategoriRapor::orderBy('urutan')->get() as $k) {
            [$huruf, $catatan] = $nilai[$k->nama];
            $rapor->nilaiRapor()->create(['kategori_rapor_id' => $k->id, 'nilai' => $huruf, 'catatan' => $catatan]);
        }

        $this->raporBulanIni($raka, $teman, $dita);

        $nominal = $raka->kelompokUsia->iuran_bulanan;
        $buat = fn ($periode, $status) => Tagihan::create([
            'anak_id' => $raka->id, 'periode' => $periode->toDateString(), 'nominal' => $nominal,
            'jatuh_tempo' => $periode->copy()->day(10)->toDateString(), 'status' => $status,
        ]);
        $bukti = fn ($periode, $kurang = 0) => [
            'diunggah_oleh' => $rina->id, 'file_path' => 'bukti/contoh.jpg', 'file_tipe' => 'jpg', 'ukuran_kb' => 640,
            'tanggal_transfer' => $periode->copy()->day(5)->toDateString(), 'nominal_transfer' => $nominal - $kurang,
        ];

        // Dua bulan lalu: lunas, diverifikasi admin tanggal 7
        $periode = $bulanIni->copy()->subMonths(2);
        $buat($periode, 'lunas')->buktiTransfer()->create($bukti($periode) + [
            'status' => 'disetujui', 'diverifikasi_oleh' => $sari->id, 'diverifikasi_pada' => $periode->copy()->day(7)->setTime(10, 0),
        ]);

        // Bulan lalu: bukti ditolak, perlu unggah ulang
        $periode = $bulanIni->copy()->subMonth();
        $buat($periode, 'ditolak')->buktiTransfer()->create($bukti($periode, 50000) + [
            'status' => 'ditolak', 'alasan_tolak' => 'Nominal transfer kurang Rp 50.000.',
            'diverifikasi_oleh' => $sari->id, 'diverifikasi_pada' => $periode->copy()->day(6)->setTime(10, 0),
        ]);

        // Bulan ini: belum bayar
        $buat($bulanIni, 'belum_bayar');
    }

    // Teman sekelompok Raka dan anak KU 6–8, masing-masing dengan akun orang tua sendiri
    private function temanLatihan(Anak $raka)
    {
        $ku68 = KelompokUsia::where('usia_min', 6)->first();
        $daftar = [
            ['Nadia Putri', 'P', '2015-08-02', $raka->kelompok_usia_id, 'Ibu Wulan', 'Ibu'],
            ['Fikri Ramadhan', 'L', '2016-01-19', $raka->kelompok_usia_id, 'Pak Hendra', 'Ayah'],
            ['Salsa Aulia', 'P', '2015-11-05', $raka->kelompok_usia_id, 'Ibu Dewi', 'Ibu'],
            ['Bima Saputra', 'L', '2014-06-27', $raka->kelompok_usia_id, 'Pak Yusuf', 'Ayah'],
            ['Aldi Firmansyah', 'L', '2015-03-12', $raka->kelompok_usia_id, 'Ibu Lina', 'Ibu'],
            ['Nanda Kurnia', 'P', '2016-09-30', $raka->kelompok_usia_id, 'Ibu Ratna', 'Ibu'],
            ['Keisha Ramadhani', 'P', '2019-02-14', $ku68->id, 'Ibu Maya', 'Ibu'],
            ['Arka Wijaya', 'L', '2018-07-08', $ku68->id, 'Pak Dimas', 'Ayah'],
            ['Rafa Pratama', 'L', '2019-10-21', $ku68->id, 'Ibu Sinta', 'Ibu'],
        ];
        $hasil = collect();
        foreach ($daftar as $i => [$nama, $jk, $lahir, $ku, $ortu, $hubungan]) {
            $user = User::create([
                'nama' => $ortu, 'email' => 'ortu'.($i + 1).'@contoh.test', 'no_hp' => '0812000010'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'password' => 'password', 'peran' => User::PERAN_ORANG_TUA,
            ]);
            $anak = Anak::create([
                'orang_tua_id' => $user->id, 'kelompok_usia_id' => $ku, 'nama' => $nama, 'tanggal_lahir' => $lahir,
                'jenis_kelamin' => $jk, 'hubungan_orang_tua' => $hubungan, 'tanggal_masuk' => '2025-07-12',
            ]);
            if ($ku === $raka->kelompok_usia_id) {
                $hasil->push($anak);
            }
        }

        return $hasil;
    }

    // Rapor bulan berjalan (L23): Nanda dan Aldi selesai, Raka baru 3 dari 5 kategori
    private function raporBulanIni(Anak $raka, $teman, User $dita): void
    {
        $kategori = KategoriRapor::orderBy('urutan')->get();
        $isi = [
            'Nanda Kurnia' => ['B+', 'A-', 'B', 'B', 'A'],
            'Aldi Firmansyah' => ['A', 'A-', 'B+', 'A', 'A'],
            'Raka Pratama' => ['B+', 'B', 'A-'],
        ];
        foreach ($teman->push($raka) as $anak) {
            if (! isset($isi[$anak->nama])) {
                continue;
            }
            $lengkap = count($isi[$anak->nama]) === $kategori->count();
            $rapor = Rapor::create([
                'anak_id' => $anak->id, 'coach_id' => $dita->id, 'periode' => now()->startOfMonth()->toDateString(),
                'status' => $lengkap ? 'terbit' : 'draf', 'terbit_pada' => $lengkap ? now()->subDay() : null,
            ]);
            foreach ($isi[$anak->nama] as $i => $huruf) {
                $rapor->nilaiRapor()->create(['kategori_rapor_id' => $kategori[$i]->id, 'nilai' => $huruf]);
            }
        }
    }
}
