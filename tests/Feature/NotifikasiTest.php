<?php

namespace Tests\Feature;

use App\Models\BuktiTransfer;
use App\Models\Notifikasi;
use App\Models\User;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// L18 Notifikasi orang tua
class NotifikasiTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $rina;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->travelTo(now()->setDate(2026, 10, 18)->setTime(9, 0));
        $this->seed(DataDemoSeeder::class);
        $this->rina = User::where('email', 'rina.pratama@gmail.com')->first();
    }

    public function test_daftar_notifikasi_dan_lonceng(): void
    {
        $this->actingAs($this->rina)->get('/orang-tua')->assertSee('Notifikasi, 2 belum dibaca');
        $this->get('/orang-tua/notifikasi')->assertOk()
            ->assertSeeInOrder(['Hari ini', 'Bukti transfer perlu diunggah ulang', 'Rapor September sudah terbit', 'Sebelumnya', 'Jadwal KU 9–12 berubah'])
            ->assertSee('Tandai semua sudah dibaca');
    }

    public function test_membuka_notifikasi_menandai_dibaca_dan_menuju_halaman(): void
    {
        $n = Notifikasi::where('user_id', $this->rina->id)->where('jenis', 'bukti_ditolak')->first();
        $this->actingAs($this->rina)->get("/orang-tua/notifikasi/{$n->id}")->assertRedirect('/orang-tua/tagihan');
        $this->assertNotNull($n->fresh()->dibaca_pada);
        $this->get('/orang-tua')->assertSee('Notifikasi, 1 belum dibaca');
    }

    public function test_tandai_semua_dibaca(): void
    {
        $this->actingAs($this->rina)->post('/orang-tua/notifikasi/baca-semua')->assertRedirect('/orang-tua/notifikasi');
        $this->assertSame(0, $this->rina->notifikasi()->whereNull('dibaca_pada')->count());
        $this->get('/orang-tua/tagihan')->assertSee('aria-label="Notifikasi"', false);
    }

    public function test_notifikasi_orang_lain_tidak_bisa_dibuka(): void
    {
        $lain = User::where('email', 'ortu1@contoh.test')->first();
        $n = Notifikasi::where('user_id', $this->rina->id)->whereNull('dibaca_pada')->first();
        $this->actingAs($lain)->get("/orang-tua/notifikasi/{$n->id}")->assertForbidden();
        $this->assertNull($n->fresh()->dibaca_pada);
    }

    public function test_notifikasi_dari_admin_muncul_untuk_orang_tua(): void
    {
        $sari = User::where('email', 'admin@spiders.test')->first();
        $bukti = BuktiTransfer::where('status', 'menunggu')->whereHas('tagihan.anak', fn ($q) => $q->where('nama', 'Nadia Putri'))->first();
        $this->actingAs($sari)->post("/manajemen/verifikasi/{$bukti->id}/setujui");

        $ortu = $bukti->tagihan->anak->orangTua;
        $this->actingAs($ortu)->get('/orang-tua/notifikasi')->assertSee('Iuran Oktober lunas')->assertSee('Pembayaran Rp 250.000 untuk Nadia Putri sudah diterima.');
    }
}
