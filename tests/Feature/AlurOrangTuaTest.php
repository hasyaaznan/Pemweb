<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Tagihan;
use App\Models\User;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// L1 beranda, L2 rapor, L17 absensi, L3 tagihan, L4 unggah bukti, L26 bukti terkirim
class AlurOrangTuaTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $rina;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 18)->setTime(9, 0));
        $this->seed(DataDemoSeeder::class);
        $this->rina = User::where('email', 'rina.pratama@gmail.com')->first();
    }

    private function tagihan(string $periode): Tagihan
    {
        return Tagihan::whereDate('periode', $periode)->firstOrFail();
    }

    public function test_beranda_menampilkan_anak_rapor_kehadiran_dan_tagihan(): void
    {
        $this->actingAs($this->rina)->get('/orang-tua')->assertOk()
            ->assertSee('Raka Pratama')
            ->assertSee('Rapor September')
            ->assertSee('85%')
            ->assertSee('Unggah bukti');
    }

    public function test_rapor_lima_kategori_berurutan(): void
    {
        $this->actingAs($this->rina)->get('/orang-tua/rapor')->assertOk()
            ->assertSeeInOrder(['Fundamental', 'Strategi', 'Mental', 'IQ Basket', 'Fisik'])
            ->assertSee('Dribbling:')
            ->assertSee('Absensi 85%');
    }

    public function test_absensi_menampilkan_ringkasan(): void
    {
        $this->actingAs($this->rina)->get('/orang-tua/absensi')->assertOk()->assertSee('85%')->assertSee('Izin')->assertSee('Alpa');
    }

    public function test_tagihan_ditolak_tampil_sebagai_perlu_unggah_ulang(): void
    {
        $this->actingAs($this->rina)->get('/orang-tua/tagihan')->assertOk()
            ->assertSee('Perlu unggah ulang')
            ->assertSee('Nominal transfer kurang Rp 50.000.')
            ->assertSee('Lunas')
            ->assertDontSee('Ditolak');
    }

    public function test_unggah_bukti_mengubah_status_menunggu_verifikasi(): void
    {
        Storage::fake('local');
        $tagihan = $this->tagihan('2026-10-01');

        $this->actingAs($this->rina)->get("/orang-tua/tagihan/{$tagihan->id}/unggah")->assertOk()->assertSee('Rp 250.000');
        $this->post("/orang-tua/tagihan/{$tagihan->id}/unggah", [
            'bukti' => UploadedFile::fake()->image('bukti.jpg')->size(500),
            'tanggal_transfer' => '2026-10-18',
            'nominal_transfer' => 250000,
        ])->assertRedirect(route('ortu.tagihan.terkirim', $tagihan));

        $this->assertSame('menunggu_verifikasi', $tagihan->fresh()->status);
        $bukti = $tagihan->buktiTransfer()->latest('id')->first();
        $this->assertSame('menunggu', $bukti->status);
        Storage::disk('local')->assertExists($bukti->file_path);

        $this->get(route('ortu.tagihan.terkirim', $tagihan))->assertOk()->assertSee('Bukti terkirim');
    }

    public function test_unggah_ulang_untuk_tagihan_ditolak_diizinkan(): void
    {
        Storage::fake('local');
        $tagihan = $this->tagihan('2026-09-01');
        $this->actingAs($this->rina)->post("/orang-tua/tagihan/{$tagihan->id}/unggah", [
            'bukti' => UploadedFile::fake()->create('bukti.pdf', 300, 'application/pdf'),
            'tanggal_transfer' => '2026-10-17', 'nominal_transfer' => 250000,
        ])->assertRedirect();
        $this->assertSame('menunggu_verifikasi', $tagihan->fresh()->status);
        $this->assertSame(2, $tagihan->buktiTransfer()->count());
    }

    public function test_file_lebih_dari_2_mb_ditolak(): void
    {
        Storage::fake('local');
        $tagihan = $this->tagihan('2026-10-01');
        $this->actingAs($this->rina)->from("/orang-tua/tagihan/{$tagihan->id}/unggah")
            ->post("/orang-tua/tagihan/{$tagihan->id}/unggah", [
                'bukti' => UploadedFile::fake()->image('besar.jpg')->size(3000),
                'tanggal_transfer' => '2026-10-18', 'nominal_transfer' => 250000,
            ])->assertSessionHasErrors(['bukti' => 'File harus JPG, PNG, atau PDF maksimal 2 MB.']);
        $this->assertSame('belum_bayar', $tagihan->fresh()->status);
    }

    public function test_tagihan_lunas_tidak_bisa_diunggah_lagi(): void
    {
        $tagihan = $this->tagihan('2026-08-01');
        $this->actingAs($this->rina)->get("/orang-tua/tagihan/{$tagihan->id}/unggah")
            ->assertRedirect(route('ortu.tagihan'))->assertSessionHas('info');
    }

    public function test_data_anak_orang_tua_lain_ditolak(): void
    {
        $lain = User::factory()->create();
        Storage::fake('local');
        $tagihan = $this->tagihan('2026-10-01');

        $this->actingAs($lain)->get("/orang-tua/tagihan/{$tagihan->id}/unggah")->assertForbidden();
        $this->actingAs($lain)->post("/orang-tua/tagihan/{$tagihan->id}/unggah", [
            'bukti' => UploadedFile::fake()->image('bukti.jpg'), 'tanggal_transfer' => '2026-10-18', 'nominal_transfer' => 250000,
        ])->assertForbidden();
        $this->actingAs($lain)->get('/orang-tua/rapor?anak='.Anak::first()->id)->assertForbidden();
    }

    public function test_orang_tua_dua_anak_bisa_ganti_anak(): void
    {
        $kedua = Anak::create([
            'orang_tua_id' => $this->rina->id, 'kelompok_usia_id' => Anak::first()->kelompok_usia_id, 'nama' => 'Alya Pratama',
            'tanggal_lahir' => '2017-05-02', 'jenis_kelamin' => 'P', 'hubungan_orang_tua' => 'Ibu', 'tanggal_masuk' => '2026-01-10',
        ]);

        $this->actingAs($this->rina)->get('/orang-tua')->assertSee('Ganti anak');
        $this->get('/orang-tua?anak='.$kedua->id)->assertOk()->assertSee('Alya Pratama');
        // Pilihan anak diingat di halaman lain
        $this->get('/orang-tua/tagihan')->assertOk()->assertSee('Tagihan Alya Pratama')->assertSee('Belum ada tagihan.');
    }

    public function test_profil_dan_akun_tanpa_anak(): void
    {
        $this->actingAs($this->rina)->get('/orang-tua/profil')->assertOk()->assertSee('rina.pratama@gmail.com');
        $this->actingAs(User::factory()->create())->get('/orang-tua')->assertOk();
    }
}
