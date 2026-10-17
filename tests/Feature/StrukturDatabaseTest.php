<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anak;
use App\Models\BuktiTransfer;
use App\Models\KategoriRapor;
use App\Models\Rapor;
use App\Models\SesiLatihan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Memastikan migration, relasi, dan aturan UNIQUE sesuai Desain Database v1.
class StrukturDatabaseTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_data_awal_sesuai_mockup(): void
    {
        $this->assertDatabaseCount('kelompok_usia', 3);
        $this->assertDatabaseCount('kategori_rapor', 5);
        $this->assertDatabaseCount('jadwal', 5);

        $raka = Anak::where('nama', 'Raka Pratama')->firstOrFail();
        $this->assertSame('KU 9–12', $raka->kelompokUsia->nama);
        $this->assertSame(250000, $raka->kelompokUsia->iuran_bulanan);
        $this->assertTrue($raka->orangTua->isOrangTua());

        $dita = User::where('email', 'dita@spiders.test')->firstOrFail();
        $this->assertEqualsCanonicalizing(['KU 6–8', 'KU 9–12'], $dita->kelompokDipegang->pluck('nama')->all());
    }

    public function test_persentase_kehadiran_dan_absensi_ganda_ditolak(): void
    {
        $raka = Anak::firstOrFail();
        $coach = User::where('peran', User::PERAN_COACH)->firstOrFail();

        foreach (range(1, 20) as $i) {
            $sesi = SesiLatihan::create([
                'kelompok_usia_id' => $raka->kelompok_usia_id, 'tanggal' => now()->subDays($i)->toDateString(),
                'jam_mulai' => '08:30', 'jam_selesai' => '10:00', 'lokasi' => 'GOR Spiders',
            ]);
            Absensi::create(['sesi_latihan_id' => $sesi->id, 'anak_id' => $raka->id, 'status' => $i <= 3 ? 'izin' : 'hadir', 'dicatat_oleh' => $coach->id]);
        }

        $hadir = $raka->absensi()->where('status', 'hadir')->count();
        $this->assertSame(85, (int) round(100 * $hadir / $raka->absensi()->count()));

        $this->expectException(QueryException::class);
        Absensi::create(['sesi_latihan_id' => $sesi->id, 'anak_id' => $raka->id, 'dicatat_oleh' => $coach->id]);
    }

    public function test_rapor_berisi_lima_kategori_dan_satu_rapor_per_bulan(): void
    {
        $raka = Anak::firstOrFail();
        $coach = User::where('peran', User::PERAN_COACH)->firstOrFail();
        $rapor = Rapor::create(['anak_id' => $raka->id, 'coach_id' => $coach->id, 'periode' => '2026-09-01']);

        foreach (KategoriRapor::orderBy('urutan')->get() as $k) {
            $rapor->nilaiRapor()->create(['kategori_rapor_id' => $k->id, 'nilai' => 'B+', 'catatan' => 'Contoh catatan.']);
        }
        $this->assertSame(5, $rapor->nilaiRapor()->count());

        $this->expectException(QueryException::class);
        Rapor::create(['anak_id' => $raka->id, 'coach_id' => $coach->id, 'periode' => '2026-09-01']);
    }

    public function test_bukti_ditolak_tetap_tersimpan_sebagai_riwayat(): void
    {
        $raka = Anak::firstOrFail();
        $tagihan = Tagihan::create(['anak_id' => $raka->id, 'periode' => '2026-09-01', 'nominal' => 250000, 'jatuh_tempo' => '2026-09-10']);
        $data = ['diunggah_oleh' => $raka->orang_tua_id, 'file_path' => 'bukti/a.jpg', 'file_tipe' => 'jpg', 'ukuran_kb' => 800, 'tanggal_transfer' => '2026-09-08'];

        $tagihan->buktiTransfer()->create($data + ['nominal_transfer' => 200000, 'status' => 'ditolak', 'alasan_tolak' => 'Nominal transfer kurang Rp 50.000.']);
        $tagihan->buktiTransfer()->create($data + ['nominal_transfer' => 250000]);

        $this->assertSame(2, $tagihan->buktiTransfer()->count());
        $this->assertSame('menunggu', BuktiTransfer::latest('id')->first()->status);
    }
}
