<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anak;
use App\Models\KelompokUsia;
use App\Models\Notifikasi;
use App\Models\Rapor;
use App\Models\SesiLatihan;
use App\Models\User;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// L7 pilih latihan, L8 absensi, L23 daftar rapor, L9 isi rapor, L24 jadwal, L25 profil
class AlurCoachTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $dita;

    private KelompokUsia $ku912;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 10, 18)->setTime(9, 0)); // Minggu
        $this->seed(DataDemoSeeder::class);
        $this->dita = User::where('email', 'dita@spiders.test')->first();
        $this->ku912 = KelompokUsia::where('nama', 'KU 9–12')->first();
    }

    private function anak(string $nama): Anak
    {
        return Anak::where('nama', $nama)->firstOrFail();
    }

    public function test_beranda_menampilkan_hari_latihan_dan_kelompok(): void
    {
        $this->actingAs($this->dita)->get('/coach')->assertOk()
            ->assertSee('Halo, Coach Dita')
            ->assertSee('Minggu, 18 Oktober')
            ->assertSee('KU 9–12')
            ->assertSee('7 anak, 08.30–10.00')
            ->assertSee('Belum diabsen')
            ->assertSee('Isi absensi');
    }

    public function test_absensi_bawaan_hadir_dan_tersimpan(): void
    {
        $url = '/coach/absensi/'.$this->ku912->id.'/2026-10-18';
        $this->actingAs($this->dita)->get($url)->assertOk()->assertSee('Simpan absensi (7 anak)')->assertSee('Raka Pratama');

        $status = $this->ku912->anak->mapWithKeys(fn ($a) => [$a->id => 'hadir'])->all();
        $status[$this->anak('Bima Saputra')->id] = 'izin';
        $status[$this->anak('Salsa Aulia')->id] = 'alpa';

        $this->put($url, ['status' => $status])
            ->assertRedirect(route('coach.beranda', ['tanggal' => '2026-10-18']))
            ->assertSessionHas('status', 'Absensi KU 9–12 tersimpan: 5 hadir, 1 izin, 1 alpa.');

        $sesi = SesiLatihan::where('kelompok_usia_id', $this->ku912->id)->whereDate('tanggal', '2026-10-18')->firstOrFail();
        $this->assertNotNull($sesi->jadwal_id);
        $this->assertSame(7, $sesi->absensi()->count());

        // Mengubah absensi tidak membuat baris ganda
        $status[$this->anak('Salsa Aulia')->id] = 'hadir';
        $this->put($url, ['status' => $status])->assertSessionHas('status', 'Absensi KU 9–12 tersimpan: 6 hadir, 1 izin, 0 alpa.');
        $this->assertSame(7, $sesi->absensi()->count());
        $this->assertSame('hadir', Absensi::where('anak_id', $this->anak('Salsa Aulia')->id)->where('sesi_latihan_id', $sesi->id)->value('status'));

        $this->get('/coach?tanggal=2026-10-18')->assertSee('Sudah diabsen, 6 hadir')->assertSee('Ubah absensi');
    }

    public function test_absensi_hanya_untuk_latihan_yang_sudah_tiba_dan_kelompok_sendiri(): void
    {
        $this->actingAs($this->dita);
        $this->get('/coach/absensi/'.$this->ku912->id.'/2026-10-24')->assertForbidden(); // Sabtu depan
        $this->get('/coach/absensi/'.$this->ku912->id.'/2026-10-19')->assertNotFound(); // Senin, tidak ada latihan
        $ku1316 = KelompokUsia::where('nama', 'KU 13–16')->first();
        $this->get('/coach/absensi/'.$ku1316->id.'/2026-10-18')->assertForbidden();

        // Anak dari kelompok lain tidak bisa ikut diabsen
        $lain = $this->anak('Keisha Ramadhani');
        $this->put('/coach/absensi/'.$this->ku912->id.'/2026-10-18', ['status' => [$lain->id => 'hadir']])->assertStatus(422);
        $this->assertSame(0, Absensi::where('anak_id', $lain->id)->count());
    }

    public function test_daftar_rapor_menghitung_yang_selesai(): void
    {
        $this->actingAs($this->dita)->get('/coach/rapor?ku='.$this->ku912->id)->assertOk()
            ->assertSee('Rapor Oktober')
            ->assertSee('2 dari 7 selesai')
            ->assertSee('Batas 31 Okt')
            ->assertSeeInOrder(['Belum selesai', 'Raka Pratama', '3 dari 5 kategori', 'Selesai', 'Aldi Firmansyah']);
    }

    public function test_rapor_terbit_setelah_lima_kategori_dan_orang_tua_mendapat_notifikasi(): void
    {
        $raka = $this->anak('Raka Pratama');
        $sebelum = Notifikasi::where('jenis', 'rapor_terbit')->count();
        $this->actingAs($this->dita)->get('/coach/rapor/'.$raka->id)->assertOk()->assertSee('KU 9–12, Oktober 2026');

        // Baru satu kategori lagi: tetap draf, orang tua belum melihat rapor Oktober
        $this->put('/coach/rapor/'.$raka->id, ['nilai' => [4 => 'B'], 'catatan' => [4 => 'Mulai bisa membaca kapan harus passing.'], 'aksi' => 'simpan'])
            ->assertRedirect(route('coach.rapor', ['ku' => $raka->kelompok_usia_id]))
            ->assertSessionHas('status', 'Rapor Raka disimpan sebagai draf. 1 kategori belum dinilai.');
        $rina = $raka->orangTua;
        $this->actingAs($rina)->get('/orang-tua')->assertSee('Rapor September')->assertDontSee('Rapor Oktober');

        $this->actingAs($this->dita)->put('/coach/rapor/'.$raka->id, ['nilai' => [5 => 'A'], 'catatan' => [5 => 'Lari bolak-balik lapangan 10 kali.'], 'aksi' => 'simpan'])
            ->assertSessionHas('status', 'Rapor Raka lengkap dan sudah bisa dibaca orang tua.');

        $rapor = Rapor::where('anak_id', $raka->id)->whereDate('periode', '2026-10-01')->first();
        $this->assertSame('terbit', $rapor->status);
        $this->assertSame(5, $rapor->nilaiRapor()->count());
        $this->assertSame($sebelum + 1, Notifikasi::where('jenis', 'rapor_terbit')->count());

        $this->actingAs($rina)->get('/orang-tua')->assertSee('Rapor Oktober');
        $this->get('/orang-tua/rapor')->assertSee('Lari bolak-balik lapangan 10 kali.');

        // Mengubah rapor yang sudah terbit tidak mengirim notifikasi lagi
        $this->actingAs($this->dita)->put('/coach/rapor/'.$raka->id, ['nilai' => [5 => 'A+']])
            ->assertSessionHas('status', 'Perubahan rapor Raka tersimpan.');
        $this->assertSame($sebelum + 1, Notifikasi::where('jenis', 'rapor_terbit')->count());
    }

    public function test_simpan_dan_lanjut_membuka_anak_berikutnya(): void
    {
        $raka = $this->anak('Raka Pratama');
        $this->actingAs($this->dita)->put('/coach/rapor/'.$raka->id, ['nilai' => [4 => 'B'], 'aksi' => 'lanjut'])
            ->assertRedirect(route('coach.rapor.isi', $this->anak('Bima Saputra')));
    }

    public function test_validasi_rapor(): void
    {
        $raka = $this->anak('Raka Pratama');
        $this->actingAs($this->dita)->put('/coach/rapor/'.$raka->id, ['catatan' => [4 => 'Catatan tanpa nilai.']])
            ->assertSessionHasErrors(['nilai.4' => 'Pilih nilai IQ Basket dulu sebelum menulis catatan.']);
        $this->put('/coach/rapor/'.$raka->id, ['nilai' => [4 => 'D']])->assertSessionHasErrors('nilai.4');
        $this->put('/coach/rapor/'.$raka->id, ['nilai' => [4 => 'B'], 'catatan' => [4 => str_repeat('a', 301)]])
            ->assertSessionHasErrors(['catatan.4' => 'Catatan IQ Basket maksimal 300 karakter.']);
    }

    public function test_coach_tidak_bisa_membuka_anak_kelompok_lain(): void
    {
        $ku1316 = KelompokUsia::where('nama', 'KU 13–16')->first();
        $lain = Anak::create([
            'orang_tua_id' => User::factory()->create()->id, 'kelompok_usia_id' => $ku1316->id, 'nama' => 'Dimas Arya',
            'tanggal_lahir' => '2012-04-01', 'jenis_kelamin' => 'L', 'hubungan_orang_tua' => 'Ayah', 'tanggal_masuk' => '2025-01-11',
        ]);
        $this->actingAs($this->dita)->get('/coach/rapor/'.$lain->id)->assertForbidden();
        $this->put('/coach/rapor/'.$lain->id, ['nilai' => [1 => 'A']])->assertForbidden();
        $this->get('/coach/rapor?ku='.$ku1316->id)->assertForbidden();
    }

    public function test_jadwal_dan_perubahan_jadwal(): void
    {
        $this->actingAs($this->dita)->get('/coach/jadwal')->assertOk()
            ->assertSee('Minggu, 18 Oktober')->assertSee('KU 9–12')->assertDontSee('mulai 07.00');
        $this->get('/coach/jadwal?tanggal=2026-10-25')->assertOk()
            ->assertSee('KU 9–12 mulai 09.00 (GOR dipakai acara pagi)');
    }

    public function test_profil_coach(): void
    {
        $this->actingAs($this->dita)->get('/coach/profil')->assertOk()
            ->assertSee('Coach Dita')->assertSee('KU 6–8, KU 9–12')->assertSee('10 anak');
    }

    public function test_orang_tua_tidak_bisa_membuka_halaman_coach(): void
    {
        $this->actingAs($this->anak('Raka Pratama')->orangTua)->get('/coach/rapor')->assertForbidden();
    }
}
