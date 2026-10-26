<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Galeri;
use App\Models\KelompokUsia;
use App\Models\Linimasa;
use App\Models\TanyaJawab;
use App\Models\Turnamen;
use App\Models\User;
use App\Support\Klub;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// CRUD data klub oleh Admin (kelompok usia, coach, pengaturan, member, isi halaman depan) dan ubah profil (K2, K7, K8)
class KelolaDataTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('unggahan');
        $this->admin = User::where('email', 'admin@spiders.test')->first();
    }

    public function test_halaman_kelola_hanya_untuk_admin(): void
    {
        $this->actingAs($this->admin)->get('/manajemen/kelola')->assertOk()
            ->assertSee('Kelompok usia dan iuran')->assertSee('Pengaturan klub')->assertSee('Galeri foto dan video');
        $this->actingAs(User::where('peran', 'coach')->first())->get('/manajemen/kelola')->assertForbidden();
    }

    public function test_jadwal_latihan_ada_di_kelola_data_dan_kembali_ke_halaman_asal(): void
    {
        $this->actingAs($this->admin);
        $this->get('/manajemen/profil')->assertSee('Kelola data klub')->assertDontSee('href="'.route('manajemen.jadwal').'"', false);
        $this->get('/manajemen/kelola')->assertSee('href="'.route('manajemen.jadwal').'"', false);
        $this->get('/manajemen/jadwal')->assertSee('href="'.route('manajemen.kelola').'" class="flex size-[38px]', false);
        $this->get('/manajemen')->assertSee(route('manajemen.jadwal', ['dari' => 'beranda']), false);
        $this->get('/manajemen/jadwal?dari=beranda')->assertSee('href="'.route('manajemen.beranda').'" class="flex size-[38px]', false);
    }

    public function test_kelompok_usia_tambah_ubah_hapus(): void
    {
        $this->actingAs($this->admin);
        $this->get('/manajemen/kelompok-usia')->assertOk()->assertSee('KU 9–12');

        // Rentang usia tidak boleh bertumpuk dengan kelompok lain
        $this->post('/manajemen/kelompok-usia', ['nama' => 'KU 15–17', 'usia_min' => 15, 'usia_max' => 17, 'iuran_bulanan' => 350000])
            ->assertSessionHasErrors('usia_min');

        $this->post('/manajemen/kelompok-usia', ['nama' => 'KU 17–18', 'usia_min' => 17, 'usia_max' => 18, 'iuran_bulanan' => 350000, 'program' => 'Persiapan tim senior'])
            ->assertRedirect('/manajemen/kelompok-usia');
        $baru = KelompokUsia::where('nama', 'KU 17–18')->firstOrFail();
        $this->assertSame(350000, (int) $baru->iuran_bulanan);

        $this->put("/manajemen/kelompok-usia/{$baru->id}", ['nama' => 'KU 17–18', 'usia_min' => 17, 'usia_max' => 18, 'iuran_bulanan' => 325000])->assertRedirect();
        $this->assertSame(325000, (int) $baru->fresh()->iuran_bulanan);

        $this->delete("/manajemen/kelompok-usia/{$baru->id}")->assertRedirect('/manajemen/kelompok-usia');
        $this->assertModelMissing($baru);
    }

    public function test_kelompok_yang_punya_anggota_tidak_bisa_dihapus(): void
    {
        $ku = KelompokUsia::where('nama', 'KU 9–12')->first();
        $this->actingAs($this->admin)->from('/manajemen/kelompok-usia')
            ->delete("/manajemen/kelompok-usia/{$ku->id}")->assertSessionHasErrors('hapus');
        $this->assertModelExists($ku);
    }

    public function test_tambah_coach_mengirim_tautan_kata_sandi_dan_coach_nonaktif_tidak_bisa_masuk(): void
    {
        Notification::fake();
        $ku = KelompokUsia::where('nama', 'KU 6–8')->first();

        $this->actingAs($this->admin)->post('/manajemen/coach', [
            'nama' => 'Coach Bima', 'no_hp' => '0813 1111 2222', 'email' => 'bima@spiders.test',
            'kelompok' => [$ku->id], 'tampil_di_landing' => '1', 'jabatan' => 'Pelatih KU 6–8',
            'foto' => UploadedFile::fake()->image('bima.jpg', 400, 400),
        ])->assertRedirect('/manajemen/coach');

        $bima = User::where('email', 'bima@spiders.test')->firstOrFail();
        $this->assertTrue($bima->isCoach());
        $this->assertSame('081311112222', $bima->no_hp);
        $this->assertTrue($bima->kelompokDipegang->contains($ku));
        Storage::disk('unggahan')->assertExists(substr($bima->foto, strlen('unggahan/')));
        Notification::assertSentTo($bima, ResetPassword::class);
        $this->get('/')->assertSee('Coach Bima')->assertSee('Pelatih KU 6–8');

        $this->put("/manajemen/coach/{$bima->id}", ['nama' => 'Coach Bima', 'no_hp' => '081311112222', 'email' => 'bima@spiders.test', 'aktif' => '0', 'hapus_foto' => '1'])
            ->assertRedirect('/manajemen/coach');
        $this->assertFalse($bima->fresh()->aktif);
        $this->assertNull($bima->fresh()->foto);
        $this->get('/')->assertDontSee('Coach Bima');

        $bima->update(['password' => 'rahasia123']);
        auth()->logout();
        $this->post('/masuk', ['login' => 'bima@spiders.test', 'password' => 'rahasia123'])->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_halaman_coach_tidak_bisa_dipakai_untuk_akun_orang_tua(): void
    {
        $rina = User::where('email', 'rina.pratama@gmail.com')->first();
        $this->actingAs($this->admin)->get("/manajemen/coach/{$rina->id}/ubah")->assertNotFound();
    }

    public function test_pengaturan_klub_disimpan_dan_tampil_di_halaman_depan(): void
    {
        $this->actingAs($this->admin)->get('/manajemen/pengaturan')->assertOk()->assertSee('GOR Rabbani');

        $this->put('/manajemen/pengaturan', [
            'rekening_bank' => 'BNI', 'rekening_nomor' => '0123456789', 'rekening_atas_nama' => 'Bandung Spiders',
            'whatsapp_admin' => '0811-2436-601', 'alamat_gor' => 'GOR Pajajaran, Jl. Pajajaran No. 37, Bandung',
            'tanggal_jatuh_tempo' => 15, 'batas_verifikasi_hari_kerja' => 2,
            'tentang_judul' => 'Klub basket anak di Bandung', 'tentang_cerita' => 'Kami latihan setiap akhir pekan.',
            'foto_sampul' => UploadedFile::fake()->image('sampul.jpg', 1600, 1200),
        ])->assertRedirect('/manajemen/pengaturan');

        Klub::lupakan();
        $this->assertSame('08112436601', Klub::get('whatsapp_admin'));
        $this->assertStringStartsWith('unggahan/halaman-depan/', Klub::get('foto_sampul'));
        $this->get('/')->assertSee('GOR Pajajaran')->assertSee('wa.me/628112436601', false)
            ->assertSee('Klub basket anak di Bandung')->assertSee(Klub::get('foto_sampul'));
    }

    public function test_pengaturan_menolak_tanggal_jatuh_tempo_di_luar_1_sampai_28(): void
    {
        $this->actingAs($this->admin)->put('/manajemen/pengaturan', ['foto_sampul_tanpa_latar' => UploadedFile::fake()->image('sampul.jpg')])
            ->assertSessionHasErrors('foto_sampul_tanpa_latar'); // JPG tidak bisa transparan

        $this->actingAs($this->admin)->put('/manajemen/pengaturan', ['tanggal_jatuh_tempo' => 31])
            ->assertSessionHasErrors(['tanggal_jatuh_tempo', 'rekening_bank']);
    }

    public function test_ubah_data_member_dan_orang_tua(): void
    {
        $anak = Anak::where('nama', 'Raka Pratama')->firstOrFail();
        $ku = KelompokUsia::where('nama', 'KU 13–16')->first();
        $this->actingAs($this->admin)->get("/manajemen/member/{$anak->id}/ubah")->assertOk()->assertSee('Raka Pratama');

        $this->put("/manajemen/member/{$anak->id}", [
            'nama' => 'Raka Aditya Pratama', 'tanggal_lahir' => '2013-03-02', 'jenis_kelamin' => 'L', 'sekolah' => 'SMPN 2 Bandung',
            'kelompok_usia_id' => $ku->id, 'hubungan_orang_tua' => 'Ibu', 'status' => 'nonaktif',
            'ortu_nama' => 'Rina Pratama', 'ortu_no_hp' => '0812 3456 7899', 'ortu_email' => 'rina.pratama@gmail.com', 'ortu_alamat' => 'Jl. Setiabudi No. 21, Bandung',
        ])->assertRedirect("/manajemen/member/{$anak->id}");

        $anak->refresh();
        $this->assertSame('Raka Aditya Pratama', $anak->nama);
        $this->assertSame($ku->id, $anak->kelompok_usia_id);
        $this->assertSame('nonaktif', $anak->status);
        $this->assertSame('081234567899', $anak->orangTua->no_hp);
        $this->get('/manajemen/member?status=nonaktif')->assertSee('Raka Aditya Pratama');
    }

    public function test_isi_halaman_depan_tambah_ubah_hapus(): void
    {
        $this->actingAs($this->admin);
        foreach (['linimasa', 'turnamen', 'galeri', 'tanya-jawab'] as $jenis) {
            $this->get("/manajemen/konten/{$jenis}")->assertOk();
            $this->get("/manajemen/konten/{$jenis}/tambah")->assertOk();
        }
        $this->get('/manajemen/konten/lain')->assertNotFound();

        $this->post('/manajemen/konten/linimasa', ['tahun' => '20', 'isi' => 'x'])->assertSessionHasErrors('tahun');
        $this->post('/manajemen/konten/linimasa', ['tahun' => '2019', 'isi' => 'Klub mulai latihan rutin.'])->assertRedirect('/manajemen/konten/linimasa');
        $this->get('/')->assertSee('Klub mulai latihan rutin.');

        $this->post('/manajemen/konten/turnamen', [
            'nama' => 'Liga Basket Anak Bandung', 'tanggal_mulai' => '2026-12-05', 'tanggal_selesai' => '2026-12-04', 'lokasi' => 'GOR C-Tra Arena',
        ])->assertSessionHasErrors('tanggal_selesai');
        $this->post('/manajemen/konten/turnamen', [
            'nama' => 'Liga Basket Anak Bandung', 'tanggal_mulai' => '2026-12-05', 'tanggal_selesai' => '2026-12-06', 'lokasi' => 'GOR C-Tra Arena',
            'foto' => UploadedFile::fake()->image('liga.jpg'),
        ])->assertRedirect();
        $liga = Turnamen::where('nama', 'Liga Basket Anak Bandung')->firstOrFail();
        $this->assertSame('5–6 Desember 2026', $liga->rentangTanggal());
        $this->put("/manajemen/konten/turnamen/{$liga->id}", [
            'nama' => 'Liga Basket Anak Bandung', 'tanggal_mulai' => '2026-12-05', 'lokasi' => 'GOR C-Tra Arena', 'hasil' => 'Juara 1 KU 13–16',
        ])->assertRedirect();
        $this->assertSame('Juara 1 KU 13–16', $liga->fresh()->hasil);
        $this->assertNotNull($liga->fresh()->foto); // foto lama tetap kalau tidak diganti

        $foto = $liga->fresh()->foto;
        $this->delete("/manajemen/konten/turnamen/{$liga->id}")->assertRedirect();
        $this->assertModelMissing($liga);
        Storage::disk('unggahan')->assertMissing(substr($foto, strlen('unggahan/')));

        $this->post('/manajemen/konten/tanya-jawab', ['pertanyaan' => 'Ada seragam?', 'jawaban' => 'Ada, dibeli saat mendaftar.', 'urutan' => 9])->assertRedirect();
        $this->assertTrue(TanyaJawab::where('pertanyaan', 'Ada seragam?')->exists());
        $this->assertTrue(Linimasa::where('tahun', '2019')->exists());
    }

    public function test_galeri_foto_wajib_gambar_dan_video_bisa_dari_youtube(): void
    {
        $this->actingAs($this->admin);
        $this->post('/manajemen/konten/galeri', ['jenis' => 'foto', 'keterangan' => 'Tanpa file', 'urutan' => 20])->assertSessionHasErrors('file');
        $this->post('/manajemen/konten/galeri', ['jenis' => 'video', 'keterangan' => 'Tanpa video', 'urutan' => 20])->assertSessionHasErrors('youtube');
        $this->post('/manajemen/konten/galeri', ['jenis' => 'video', 'keterangan' => 'Link salah', 'youtube' => 'https://contoh.com/video', 'urutan' => 20])->assertSessionHasErrors('youtube');

        $this->post('/manajemen/konten/galeri', ['jenis' => 'foto', 'keterangan' => 'Latihan dribel', 'urutan' => 20, 'file' => UploadedFile::fake()->image('dribel.jpg')])->assertRedirect();
        $this->post('/manajemen/konten/galeri', ['jenis' => 'video', 'keterangan' => 'Final antarklub', 'urutan' => 21, 'youtube' => 'https://youtu.be/dQw4w9WgXcQ?si=abc'])->assertRedirect();

        $video = Galeri::where('keterangan', 'Final antarklub')->firstOrFail();
        $this->assertSame('dQw4w9WgXcQ', $video->youtube);
        $this->assertSame('https://i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', $video->gambar());
        $this->get('/')->assertSee('Lihat foto: Latihan dribel')->assertSee('Lihat video: Final antarklub');
    }

    public function test_semua_peran_bisa_ubah_profil_dan_kata_sandi(): void
    {
        foreach (['admin@spiders.test' => '/manajemen/profil', 'dita@spiders.test' => '/coach/profil', 'rina.pratama@gmail.com' => '/orang-tua/profil'] as $email => $profil) {
            $user = User::where('email', $email)->first();
            $this->actingAs($user)->get($profil)->assertSee('Ubah profil dan kata sandi');
            $this->get('/profil/ubah')->assertOk()->assertSee($user->nama);
        }

        $rina = User::where('email', 'rina.pratama@gmail.com')->first();
        $this->actingAs($rina)->put('/profil', ['nama' => 'Rina P.', 'email' => 'rina.p@gmail.com', 'no_hp' => '+62 812 3456 7890'])
            ->assertSessionHasErrors('alamat'); // alamat wajib untuk orang tua
        $this->put('/profil', ['nama' => 'Rina P.', 'email' => 'rina.p@gmail.com', 'no_hp' => '+62 812 3456 7890', 'alamat' => 'Jl. Riau 1'])->assertRedirect();
        $this->assertSame('Rina P.', $rina->fresh()->nama);
        $this->assertSame('081234567890', $rina->fresh()->no_hp);

        $this->put('/profil', ['nama' => 'Rina P.', 'email' => 'admin@spiders.test', 'no_hp' => '081234567890', 'alamat' => 'Jl. Riau 1'])->assertSessionHasErrors('email');

        $this->put('/profil/kata-sandi', ['sandi_lama' => 'salah', 'password' => 'sandibaru1', 'password_confirmation' => 'sandibaru1'])->assertSessionHasErrors('sandi_lama');
        $this->put('/profil/kata-sandi', ['sandi_lama' => 'password', 'password' => 'sandibaru1', 'password_confirmation' => 'sandibaru1'])->assertRedirect();
        $this->assertTrue(Hash::check('sandibaru1', $rina->fresh()->password));
    }
}
