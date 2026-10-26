<?php

use App\Models\Linimasa;
use App\Models\Pengaturan;
use Database\Seeders\KontenLandingSeeder;
use Illuminate\Database\Migrations\Migration;

// Database yang sudah ada sebelum PR #8 (dijalankan dengan `php artisan migrate` tanpa --seed) belum punya
// foto, video, sejarah, dan kontak asli klub. Migration ini mengisinya tanpa menghapus data lain.
return new class extends Migration
{
    public function up(): void
    {
        // Database baru: tabel pengaturan masih kosong, isinya nanti dibuat DatabaseSeeder
        if (! Pengaturan::exists()) {
            return;
        }

        (new KontenLandingSeeder)->run();

        // Teks dan kontak contoh lama diganti yang asli; nilai yang sudah diubah Admin dibiarkan
        $ganti = [
            'whatsapp_admin' => ['081234560000', '08112436600'],
            'alamat_gor' => ['GOR Spiders, Jl. Pajajaran No. 88, Bandung', 'GOR Rabbani, Sportainment Rabbani, Jl. A.H. Nasution No.285, Pasanggrahan, Ujung Berung, Bandung 40614'],
            'tentang_judul' => ['Latihan di GOR Rabbani, Ujung Berung', KontenLandingSeeder::TENTANG_JUDUL],
        ];
        foreach ($ganti as $kunci => [$lama, $baru]) {
            Pengaturan::where('kunci', $kunci)->where('nilai', $lama)->update(['nilai' => $baru]);
        }
        if (Pengaturan::where('kunci', 'tentang_judul')->value('nilai') === KontenLandingSeeder::TENTANG_JUDUL) {
            Pengaturan::where('kunci', 'tentang_cerita')->update(['nilai' => KontenLandingSeeder::TENTANG_CERITA]);
        }
        if (! Linimasa::where('isi', 'like', '27 Agustus%')->exists()) {
            // Linimasa diurutkan per tahun lalu urutan dibuat, jadi tonggak 2023 lain dibuat ulang setelah tanggal berdiri
            $lain = Linimasa::where('tahun', '2023')->orderBy('id')->get();
            Linimasa::whereKey($lain->modelKeys())->delete();
            Linimasa::create(['tahun' => '2023', 'isi' => '27 Agustus: Bandung Spiders berdiri. Sekitar 20 atlet berlatih di Lapangan Beebucks.']);
            foreach ($lain as $l) {
                Linimasa::create(['tahun' => $l->tahun, 'isi' => str_starts_with($l->isi, 'Dua tim') ? 'Desember: d'.substr($l->isi, 1) : $l->isi]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
