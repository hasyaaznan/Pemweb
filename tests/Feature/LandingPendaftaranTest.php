<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Pendaftaran;
use App\Models\User;
use Database\Seeders\DataDemoSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// L10 landing, L13/L14/L11/L15 pendaftaran, dan pemeriksaan pendaftar oleh Admin (K1, K7)
class LandingPendaftaranTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->travelTo(now()->setDate(2026, 10, 18)->setTime(9, 0));
    }

    private function isian(array $ubah = []): array
    {
        return array_merge([
            'nama_anak' => 'Alya Kusuma', 'tanggal_lahir' => '2015-05-20', 'jenis_kelamin' => 'P', 'sekolah' => 'SDN 7 Bandung',
            'nama_orang_tua' => 'Dewi Kusuma', 'hubungan' => 'Ibu', 'no_whatsapp' => '0813 2222 3333', 'email' => 'Dewi.Kusuma@gmail.com',
            'alamat' => 'Jl. Riau No. 5, Bandung',
            'foto_3x4' => UploadedFile::fake()->image('foto.jpg'),
            'akte' => UploadedFile::fake()->create('akte.pdf', 300, 'application/pdf'),
            'kk' => UploadedFile::fake()->image('kk.png'),
        ], $ubah);
    }

    public function test_landing_menampilkan_program_jadwal_dan_kontak_dari_database(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Latihan basket untuk anak 6–16 tahun di Bandung.')
            ->assertSee('Rp 200rb')
            ->assertSee('Sab & Min')
            ->assertSeeInOrder(['KU 6–8', 'KU 9–12', 'KU 13–16'])
            ->assertSee('Rp 300rb')
            ->assertSee('07.00–08.30')
            ->assertSee('Kenapa di Spiders')
            ->assertSee('0811 2436 600')
            ->assertSee('https://wa.me/628112436600?text=', false)
            ->assertSee('GOR Rabbani')
            ->assertSee('Jl. A.H. Nasution No.285')
            ->assertSee('Daftar sekarang');
    }

    public function test_landing_menampilkan_isi_halaman_depan_dari_database(): void
    {
        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Program per usia', 'Tentang klub', 'Turnamen dan prestasi', 'Galeri latihan', 'Jadwal latihan', 'Yang sering ditanyakan'])
            ->assertSee('images/klub/sampul.webp') // HP
            ->assertSee('images/klub/sampul-tanpa-latar.webp') // laptop, di atas lingkaran oranye
            ->assertSee('Berdiri sejak 27 Agustus 2023')
            ->assertSeeInOrder(['27 Agustus: Bandung Spiders berdiri', 'Desember: dua tim Spiders'])
            ->assertSee('BSB Basketball Invitational Tournament')
            ->assertSee('20–24 Desember 2023')
            ->assertSee('Lihat video:', false)
            ->assertSee('Bagaimana cara membayar iuran?')
            // Coach baru tampil setelah Admin mencentang "Tampilkan di bagian Coach kami"
            ->assertDontSee('Coach kami');

        User::where('peran', 'coach')->first()->update(['tampil_di_landing' => true, 'jabatan' => 'Kepala pelatih', 'lisensi' => 'Lisensi Perbasi C']);
        $this->get('/')->assertSee('Coach kami')->assertSee('Kepala pelatih')->assertSee('Lisensi Perbasi C');
    }

    public function test_bagian_tanpa_isi_tidak_ditampilkan(): void
    {
        \App\Models\Turnamen::query()->delete();
        \App\Models\Galeri::query()->delete();
        \App\Models\TanyaJawab::query()->delete();

        $this->get('/')->assertOk()
            ->assertDontSee('Turnamen dan prestasi')
            ->assertDontSee('Galeri latihan')
            ->assertDontSee('Yang sering ditanyakan')
            ->assertDontSee('href="#galeri"', false);
    }

    public function test_landing_menyapa_member_yang_sudah_masuk(): void
    {
        $rina = User::where('email', 'rina.pratama@gmail.com')->first();
        $this->actingAs($rina)->get('/')->assertOk()->assertSee(route('beranda'), false);
    }

    public function test_pendaftaran_tersimpan_dengan_tiga_dokumen_dan_kelompok_otomatis(): void
    {
        $this->get('/daftar')->assertOk()->assertSee('Data anak')->assertSee('Kelompok usia (otomatis)');
        $this->post('/daftar', $this->isian())->assertRedirect('/daftar/terkirim');

        $p = Pendaftaran::where('nama_anak', 'Alya Kusuma')->firstOrFail();
        $this->assertSame('KU 9–12', $p->kelompokUsia->nama); // 11 tahun
        $this->assertSame('081322223333', $p->no_whatsapp);
        $this->assertSame('dewi.kusuma@gmail.com', $p->email);
        $this->assertSame('menunggu_verifikasi', $p->status);
        $this->assertSame(['akte', 'foto_3x4', 'kk'], $p->dokumenPendaftaran->pluck('jenis')->sort()->values()->all());
        $p->dokumenPendaftaran->each(fn ($d) => Storage::disk('local')->assertExists($d->file_path));

        $this->get('/daftar/terkirim')->assertOk()->assertSee('Pendaftaran terkirim')->assertSee('BS-2026-'.str_pad((string) $p->id, 4, '0', STR_PAD_LEFT));
    }

    public function test_pendaftaran_menolak_usia_di_luar_program_dan_dokumen_kurang(): void
    {
        $this->post('/daftar', $this->isian(['tanggal_lahir' => '2022-01-01']))->assertSessionHasErrors(['tanggal_lahir' => 'Program kami untuk anak usia 6–16 tahun.']);
        $this->post('/daftar', $this->isian(['kk' => null]))->assertSessionHasErrors(['kk' => 'Dokumen ini belum diunggah.']);
        $this->post('/daftar', $this->isian(['akte' => UploadedFile::fake()->create('akte.pdf', 3000, 'application/pdf')]))->assertSessionHasErrors('akte');
        $this->post('/daftar', $this->isian(['no_whatsapp' => '12345']))->assertSessionHasErrors('no_whatsapp');
        $this->assertSame(0, Pendaftaran::where('nama_anak', 'Alya Kusuma')->count());
    }

    public function test_pendaftaran_ganda_ditolak(): void
    {
        $this->post('/daftar', $this->isian());
        $this->post('/daftar', $this->isian())->assertSessionHasErrors(['nama_anak' => 'Pendaftaran untuk anak ini sudah dikirim dan sedang diperiksa Admin.']);
        $this->post('/daftar', $this->isian(['nama_anak' => 'Raka Pratama', 'tanggal_lahir' => '2016-03-14']))->assertSessionHasErrors('nama_anak');
    }

    public function test_admin_menerima_pendaftar_membuat_akun_dan_anak(): void
    {
        Notification::fake();
        $this->post('/daftar', $this->isian());
        $p = Pendaftaran::where('nama_anak', 'Alya Kusuma')->firstOrFail();
        $sari = User::where('email', 'admin@spiders.test')->first();

        $this->actingAs($sari)->get('/manajemen/pendaftaran')->assertOk()->assertSee('Alya Kusuma');
        $this->get("/manajemen/pendaftaran/{$p->id}")->assertOk()->assertSee('Kartu Keluarga')->assertSee('0813 2222 3333');
        $dok = $p->dokumenPendaftaran->firstWhere('jenis', 'kk');
        $this->get("/manajemen/pendaftaran/dokumen/{$dok->id}")->assertOk();

        $this->post("/manajemen/pendaftaran/{$p->id}/terima")->assertRedirect("/manajemen/pendaftaran/{$p->id}");
        $p->refresh();
        $this->assertSame('diterima', $p->status);
        $ortu = User::where('email', 'dewi.kusuma@gmail.com')->firstOrFail();
        $this->assertTrue($ortu->isOrangTua());
        $this->assertSame($p->anak_id, $ortu->anak()->first()->id);
        $this->assertSame('KU 9–12', $ortu->anak()->first()->kelompokUsia->nama);
        Notification::assertSentTo($ortu, ResetPassword::class);

        $this->get("/manajemen/pendaftaran/{$p->id}")->assertSee('Kabari lewat WhatsApp')->assertSee('https://wa.me/6281322223333?text=', false);
        $this->post("/manajemen/pendaftaran/{$p->id}/terima")->assertStatus(409);
    }

    public function test_adik_dari_orang_tua_member_memakai_akun_yang_sama(): void
    {
        $this->post('/daftar', $this->isian(['nama_orang_tua' => 'Rina Pratama', 'email' => 'rina.pratama@gmail.com', 'no_whatsapp' => '081234567890', 'nama_anak' => 'Alya Pratama']));
        $p = Pendaftaran::where('nama_anak', 'Alya Pratama')->firstOrFail();
        $this->actingAs(User::where('email', 'admin@spiders.test')->first())->post("/manajemen/pendaftaran/{$p->id}/terima");

        $rina = User::where('email', 'rina.pratama@gmail.com')->first();
        $this->assertSame(['Alya Pratama', 'Raka Pratama'], $rina->anak()->orderBy('nama')->pluck('nama')->all());
        $this->assertSame(1, User::where('email', 'rina.pratama@gmail.com')->count());
    }

    public function test_admin_menolak_pendaftar_dengan_alasan(): void
    {
        $this->post('/daftar', $this->isian());
        $p = Pendaftaran::where('nama_anak', 'Alya Kusuma')->firstOrFail();
        $this->actingAs(User::where('email', 'admin@spiders.test')->first());

        $this->post("/manajemen/pendaftaran/{$p->id}/tolak", ['alasan' => ''])->assertSessionHasErrors('alasan');
        $this->post("/manajemen/pendaftaran/{$p->id}/tolak", ['alasan' => 'Foto Kartu Keluarga tidak terbaca.'])->assertRedirect();
        $this->assertSame('ditolak', $p->fresh()->status);
        $this->assertSame(0, Anak::where('nama', 'Alya Kusuma')->count());
        $this->get("/manajemen/pendaftaran/{$p->id}")->assertSee('Foto Kartu Keluarga tidak terbaca.');
    }

    public function test_hanya_admin_yang_membuka_dokumen_pendaftar(): void
    {
        $this->post('/daftar', $this->isian());
        $dok = Pendaftaran::where('nama_anak', 'Alya Kusuma')->firstOrFail()->dokumenPendaftaran->first();
        $this->get("/manajemen/pendaftaran/dokumen/{$dok->id}")->assertRedirect('/masuk');
        $this->actingAs(User::where('email', 'rina.pratama@gmail.com')->first())->get("/manajemen/pendaftaran/dokumen/{$dok->id}")->assertForbidden();
    }

    public function test_beranda_admin_menghitung_pendaftar_dari_data_demo(): void
    {
        $this->seed(DataDemoSeeder::class);
        $this->actingAs(User::where('email', 'admin@spiders.test')->first())->get('/manajemen/member')->assertSee('3 pendaftar baru');
        $this->get('/manajemen/pendaftaran')->assertSeeInOrder(['Kayla Nugraha', 'Daffa Hidayat', 'Gilang Permana']);
    }
}
