<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\BuktiTransfer;
use App\Models\Jadwal;
use App\Models\KelompokUsia;
use App\Models\Notifikasi;
use App\Models\SesiLatihan;
use App\Models\Tagihan;
use App\Models\User;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// L20 beranda, L5/L6/L27 verifikasi, tagihan bulanan, L21 member, L22 profil, jadwal latihan
class AlurAdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $sari;

    private KelompokUsia $ku912;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->travelTo(now()->setDate(2026, 10, 18)->setTime(9, 0)); // Minggu
        $this->seed(DataDemoSeeder::class);
        $this->sari = User::where('email', 'admin@spiders.test')->first();
        $this->ku912 = KelompokUsia::where('nama', 'KU 9–12')->first();
    }

    private function buktiMenunggu(string $nama): BuktiTransfer
    {
        return BuktiTransfer::where('status', 'menunggu')
            ->whereHas('tagihan.anak', fn ($q) => $q->where('nama', $nama))->firstOrFail();
    }

    public function test_beranda_menampilkan_ringkasan_dan_tugas(): void
    {
        $this->actingAs($this->sari)->get('/manajemen')->assertOk()
            ->assertSee('Halo, Sari')
            ->assertSee('Member aktif')
            ->assertSee('80%')
            ->assertSee('Iuran September lunas')
            ->assertSee('Bukti iuran menunggu')
            ->assertSee('Belum bayar Oktober')
            ->assertSee('Lewat jatuh tempo 10 Okt')
            ->assertDontSee('belum punya tagihan');
    }

    public function test_hanya_admin_yang_bisa_membuka(): void
    {
        $bukti = $this->buktiMenunggu('Nadia Putri');
        $rina = User::where('email', 'rina.pratama@gmail.com')->first();
        $dita = User::where('email', 'dita@spiders.test')->first();

        $this->actingAs($rina)->get('/manajemen/verifikasi')->assertForbidden();
        $this->actingAs($dita)->get("/manajemen/verifikasi/{$bukti->id}/file")->assertForbidden();
        $this->actingAs($dita)->post("/manajemen/verifikasi/{$bukti->id}/setujui")->assertForbidden();
        $this->assertSame('menunggu', $bukti->fresh()->status);
    }

    public function test_daftar_verifikasi_urut_dari_yang_paling_lama(): void
    {
        $this->actingAs($this->sari)->get('/manajemen/verifikasi')->assertOk()
            ->assertSeeInOrder(['Fikri Ramadhan', 'Nadia Putri', 'Salsa Aulia', 'Keisha Ramadhani'])
            ->assertSee('Lewat batas 2 hari kerja');
    }

    public function test_detail_menandai_nominal_kurang_dan_file_hanya_lewat_admin(): void
    {
        $bukti = $this->buktiMenunggu('Salsa Aulia');
        $this->actingAs($this->sari)->get("/manajemen/verifikasi/{$bukti->id}")->assertOk()
            ->assertSee('Kurang Rp 50.000 dari tagihan.')
            ->assertSee('Tolak bukti ini?');
        $this->get("/manajemen/verifikasi/{$bukti->id}/file")->assertOk()->assertHeader('content-type', 'image/jpeg');
    }

    public function test_setujui_membuat_tagihan_lunas_dan_memberi_tahu_orang_tua(): void
    {
        $bukti = $this->buktiMenunggu('Nadia Putri');
        $this->actingAs($this->sari)->post("/manajemen/verifikasi/{$bukti->id}/setujui")
            ->assertRedirect('/manajemen/verifikasi')->assertSessionHas('status', 'Iuran Oktober Nadia Putri lunas.');

        $bukti->refresh();
        $this->assertSame('disetujui', $bukti->status);
        $this->assertSame($this->sari->id, $bukti->diverifikasi_oleh);
        $this->assertSame('lunas', $bukti->tagihan->status);
        $this->assertDatabaseHas('notifikasi', ['user_id' => $bukti->tagihan->anak->orang_tua_id, 'jenis' => 'iuran_lunas']);

        // Ketukan kedua (misalnya dua Admin bersamaan) tidak memproses ulang
        $this->post("/manajemen/verifikasi/{$bukti->id}/setujui")->assertStatus(409);
    }

    public function test_tolak_wajib_alasan_lalu_orang_tua_bisa_unggah_ulang(): void
    {
        $bukti = $this->buktiMenunggu('Salsa Aulia');
        $this->actingAs($this->sari)->post("/manajemen/verifikasi/{$bukti->id}/tolak", ['alasan' => ''])->assertSessionHasErrors('alasan');
        $this->assertSame('menunggu', $bukti->fresh()->status);

        $this->post("/manajemen/verifikasi/{$bukti->id}/tolak", ['alasan' => 'Nominal transfer kurang Rp 50.000.'])
            ->assertRedirect('/manajemen/verifikasi');
        $bukti->refresh();
        $this->assertSame('ditolak', $bukti->status);
        $this->assertSame('ditolak', $bukti->tagihan->status);
        $notif = Notifikasi::where('jenis', 'bukti_ditolak')->latest('id')->first();
        $this->assertSame('Nominal transfer kurang Rp 50.000.', $notif->isi);

        $ortu = $bukti->tagihan->anak->orangTua;
        $this->actingAs($ortu)->get('/orang-tua/tagihan')->assertSee('Perlu unggah ulang');
        $this->get("/orang-tua/tagihan/{$bukti->tagihan_id}/unggah")->assertOk();
    }

    public function test_tagihan_bulanan_dibuat_sekali_untuk_anak_aktif(): void
    {
        Anak::where('nama', 'Rafa Pratama')->first()->update(['status' => 'nonaktif']);

        $sebelum = Notifikasi::where('jenis', 'tagihan_terbit')->count();
        $this->travelTo(now()->setDate(2026, 11, 1)->setTime(6, 0));
        $this->artisan('tagihan:buat')->expectsOutput('9 tagihan November 2026 dibuat.')->assertSuccessful();
        $this->artisan('tagihan:buat')->expectsOutput('0 tagihan November 2026 dibuat.')->assertSuccessful();

        $tagihan = Tagihan::whereDate('periode', '2026-11-01')->with('anak.kelompokUsia')->get();
        $this->assertCount(9, $tagihan);
        $keisha = $tagihan->first(fn ($t) => $t->anak->nama === 'Keisha Ramadhani');
        $this->assertSame(200000, $keisha->nominal);
        $this->assertSame('2026-11-10', $keisha->jatuh_tempo->toDateString());
        $this->assertSame($sebelum + 9, Notifikasi::where('jenis', 'tagihan_terbit')->count());
    }

    public function test_beranda_bisa_membuat_tagihan_jika_cron_belum_jalan(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 2)->setTime(8, 0));
        $this->actingAs($this->sari)->get('/manajemen')->assertSee('10 anak aktif belum punya tagihan November.');
        $this->post('/manajemen/tagihan')->assertSessionHas('status', '10 tagihan November dibuat dan orang tua sudah diberi tahu.');
        $this->get('/manajemen')->assertDontSee('belum punya tagihan');
    }

    public function test_daftar_member_dan_saring_belum_bayar(): void
    {
        $this->actingAs($this->sari)->get('/manajemen/member')->assertOk()->assertSee('Semua 10')->assertSee('Raka Pratama')->assertSee('Keisha Ramadhani');
        $this->get('/manajemen/member?ku='.$this->ku912->id)->assertSee('Raka Pratama')->assertDontSee('Keisha Ramadhani');
        $this->get('/manajemen/member?iuran=belum')->assertSee('Raka Pratama')->assertSee('Nanda Kurnia')->assertSee('Rafa Pratama')->assertDontSee('Nadia Putri');
    }

    public function test_detail_member_berisi_pengingat_whatsapp(): void
    {
        $raka = Anak::where('nama', 'Raka Pratama')->first();
        $this->actingAs($this->sari)->get("/manajemen/member/{$raka->id}")->assertOk()
            ->assertSee('85% (17 dari 20)')
            ->assertSee('https://wa.me/6281234567890?text=', false)
            ->assertSee('Kirim pengingat lewat WhatsApp');
    }

    public function test_profil_admin(): void
    {
        $this->actingAs($this->sari)->get('/manajemen/profil')->assertOk()->assertSee('admin@spiders.test')->assertSee('Keluar');
    }

    public function test_tambah_jadwal_dan_tolak_hari_ganda(): void
    {
        $ku68 = KelompokUsia::where('nama', 'KU 6–8')->first();
        $data = ['kelompok_usia_id' => $ku68->id, 'hari' => 3, 'jam_mulai' => '16:00', 'jam_selesai' => '17:30', 'lokasi' => 'GOR Spiders, Lapangan B', 'berlaku_mulai' => '2026-10-19'];

        $this->actingAs($this->sari)->post('/manajemen/jadwal', $data)->assertRedirect('/manajemen/jadwal');
        $this->get('/manajemen/jadwal')->assertSee('Rabu, 16.00–17.30')->assertSee('Rabu, 21 Oktober');

        $this->post('/manajemen/jadwal', $data)->assertSessionHasErrors('hari');
        $this->post('/manajemen/jadwal', ['hari' => 4, 'jam_mulai' => '17:30', 'jam_selesai' => '16:00'] + $data)->assertSessionHasErrors('jam_selesai');
    }

    public function test_hapus_jadwal_menonaktifkan(): void
    {
        $jadwal = Jadwal::where('kelompok_usia_id', $this->ku912->id)->where('hari', 7)->first();
        $this->actingAs($this->sari)->delete("/manajemen/jadwal/{$jadwal->id}")->assertRedirect('/manajemen/jadwal');
        $this->assertFalse($jadwal->fresh()->aktif);
        $this->get("/manajemen/jadwal/{$jadwal->id}/ubah")->assertNotFound();
    }

    public function test_admin_membatalkan_latihan_dan_orang_tua_diberi_tahu(): void
    {
        $url = '/manajemen/jadwal/'.$this->ku912->id.'/2026-10-24';
        $sebelum = Notifikasi::where('jenis', 'jadwal_berubah')->count();
        $this->actingAs($this->sari)->get($url)->assertOk()->assertSee('Sabtu, 24 Oktober 2026');
        $this->put($url, ['aksi' => 'batal', 'alasan' => ''])->assertSessionHasErrors('alasan');
        $this->put($url, ['aksi' => 'batal', 'alasan' => 'GOR dipakai turnamen'])
            ->assertRedirect('/manajemen/jadwal')->assertSessionHas('status', 'Latihan KU 9–12 Sabtu 24 Oktober dibatalkan. Orang tua sudah diberi tahu.');

        $sesi = SesiLatihan::where('kelompok_usia_id', $this->ku912->id)->whereDate('tanggal', '2026-10-24')->first();
        $this->assertSame('batal', $sesi->status);
        $this->assertSame($sebelum + 7, Notifikasi::where('jenis', 'jadwal_berubah')->count());

        $this->put($url, ['aksi' => 'kembalikan'])->assertRedirect();
        $this->assertSame('terjadwal', $sesi->fresh()->status);
        $this->assertSame('08:30', substr($sesi->fresh()->jam_mulai, 0, 5));
    }

    public function test_latihan_yang_sudah_lewat_tidak_bisa_diubah(): void
    {
        $this->actingAs($this->sari)->get('/manajemen/jadwal/'.$this->ku912->id.'/2026-10-17')->assertForbidden();
        $this->get('/manajemen/jadwal/'.$this->ku912->id.'/2026-10-21')->assertNotFound(); // Rabu, tidak ada latihan
    }

    public function test_coach_mengubah_jam_latihan_kelompoknya_saja(): void
    {
        $dita = User::where('email', 'dita@spiders.test')->first();
        $ku1316 = KelompokUsia::where('nama', 'KU 13–16')->first();

        $this->actingAs($dita)->get('/coach/jadwal?tanggal=2026-10-24')->assertSee('/coach/jadwal/'.$this->ku912->id.'/2026-10-24', false);
        $this->put('/coach/jadwal/'.$this->ku912->id.'/2026-10-24', ['aksi' => 'ubah', 'jam_mulai' => '09:00', 'jam_selesai' => '10:30', 'lokasi' => 'GOR Spiders, Lapangan B', 'alasan' => 'Lapangan A diperbaiki'])
            ->assertRedirect('/coach/jadwal?tanggal=2026-10-24');
        $sesi = SesiLatihan::where('kelompok_usia_id', $this->ku912->id)->whereDate('tanggal', '2026-10-24')->first();
        $this->assertSame('diubah', $sesi->status);
        $this->assertSame($dita->id, $sesi->diubah_oleh);

        $this->get('/coach/jadwal/'.$ku1316->id.'/2026-10-24')->assertForbidden();
    }

    public function test_latihan_yang_sudah_diabsen_tidak_bisa_dibatalkan(): void
    {
        $dita = User::where('email', 'dita@spiders.test')->first();
        $url = '/coach/absensi/'.$this->ku912->id.'/2026-10-18';
        $this->actingAs($dita)->put($url, ['status' => $this->ku912->anak->mapWithKeys(fn ($a) => [$a->id => 'hadir'])->all()]);
        $this->assertTrue(SesiLatihan::where('kelompok_usia_id', $this->ku912->id)->whereDate('tanggal', '2026-10-18')->firstOrFail()->absensi()->exists());

        $this->actingAs($this->sari)->put('/manajemen/jadwal/'.$this->ku912->id.'/2026-10-18', ['aksi' => 'batal', 'alasan' => 'Hujan'])
            ->assertSessionHasErrors('aksi');
    }
}
