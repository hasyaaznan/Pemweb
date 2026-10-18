<?php

namespace Database\Seeders;

use App\Models\Anak;
use App\Models\KategoriRapor;
use App\Models\Rapor;
use App\Models\SesiLatihan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Data contoh untuk mencoba alur orang tua (L1–L4, L17), dihitung mundur dari hari ini:
// 20 sesi terakhir (17 hadir = 85%), rapor bulan lalu, dan 3 tagihan dengan status berbeda.
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
        $statusKe = [3 => 'izin', 9 => 'izin', 14 => 'alpa'];
        foreach ($sesi as $i => $tgl) {
            $s = SesiLatihan::create([
                'kelompok_usia_id' => $raka->kelompok_usia_id, 'tanggal' => $tgl->toDateString(),
                'jam_mulai' => '08:30', 'jam_selesai' => '10:00', 'lokasi' => 'GOR Spiders, Lapangan A',
            ]);
            $s->absensi()->create(['anak_id' => $raka->id, 'status' => $statusKe[$i] ?? 'hadir', 'dicatat_oleh' => $dita->id]);
        }

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
}
