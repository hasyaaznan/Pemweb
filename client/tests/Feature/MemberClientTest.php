<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

// Client memanggil API server; Http::fake membuat tes tidak butuh server hidup
class MemberClientTest extends TestCase
{
    private string $api = 'http://api.test/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        config(['api.server_url' => $this->api]);
    }

    private function masuk(): static
    {
        return $this->withSession(['api_token' => 'tok123', 'pengguna' => ['nama' => 'Sari', 'peran' => 'manajemen']]);
    }

    private function member(array $ubah = []): array
    {
        return $ubah + [
            'id' => 7, 'nama' => 'Bima', 'tanggal_lahir' => '2015-05-02', 'usia' => 11, 'jenis_kelamin' => 'L', 'sekolah' => null,
            'hubungan_orang_tua' => 'Ayah', 'status' => 'aktif', 'tanggal_masuk' => '2026-11-14', 'kelompok_usia_id' => 2,
            'kelompok_usia' => ['id' => 2, 'nama' => 'KU 9–12'],
            'orang_tua' => ['id' => 3, 'nama' => 'Andi', 'email' => 'andi@contoh.test', 'no_hp' => '081299990001', 'alamat' => null],
        ];
    }

    private function isian(): array
    {
        return [
            'nama' => 'Bima', 'tanggal_lahir' => '2015-05-02', 'jenis_kelamin' => 'L', 'kelompok_usia_id' => 2, 'hubungan_orang_tua' => 'Ayah',
            'orang_tua' => ['nama' => 'Andi', 'email' => 'andi@contoh.test', 'no_hp' => '081299990001'],
        ];
    }

    public function test_belum_masuk_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/member')->assertRedirect('/masuk');
    }

    public function test_masuk_menyimpan_token(): void
    {
        Http::fake(["{$this->api}/masuk" => Http::response(['token' => 'tok123', 'data' => ['nama' => 'Sari', 'peran' => 'manajemen']])]);

        $this->post('/masuk', ['login' => 'admin@spiders.test', 'password' => 'password'])
            ->assertRedirect('/member')->assertSessionHas('api_token', 'tok123');
    }

    public function test_daftar_member_dari_api_dengan_token(): void
    {
        Http::fake(["{$this->api}/member*" => Http::response(['data' => [$this->member()], 'meta' => ['total' => 1, 'last_page' => 1, 'current_page' => 1]])]);

        $this->masuk()->get('/member')->assertOk()->assertSee('Bima')->assertSee('KU 9–12');
        Http::assertSent(fn (Request $r) => $r->hasHeader('Authorization', 'Bearer tok123') && $r->method() === 'GET');
    }

    public function test_tambah_member_mengirim_post(): void
    {
        Http::fake(["{$this->api}/member" => Http::response(['data' => $this->member()], 201)]);

        $this->masuk()->post('/member', $this->isian())->assertRedirect('/member/7');
        Http::assertSent(fn (Request $r) => $r->method() === 'POST' && $r['orang_tua']['email'] === 'andi@contoh.test');
    }

    public function test_error_422_dari_api_diteruskan_ke_form(): void
    {
        Http::fake(["{$this->api}/member" => Http::response(['message' => 'x', 'errors' => ['orang_tua.no_hp' => ['No. WhatsApp diawali 08.']]], 422)]);

        $this->masuk()->from('/member/create')->post('/member', $this->isian())
            ->assertRedirect('/member/create')->assertSessionHasErrors('orang_tua.no_hp');
    }

    public function test_ubah_put_patch_dan_hapus(): void
    {
        Http::fake([
            "{$this->api}/member/7" => Http::sequence()
                ->push(['data' => $this->member(['nama' => 'Bima S.'])])
                ->push(['data' => $this->member(['status' => 'nonaktif'])])
                ->push(['message' => 'Member Bima berhasil dihapus.']),
        ]);

        $this->masuk()->put('/member/7', $this->isian() + ['status' => 'aktif'])->assertRedirect('/member/7');
        $this->masuk()->patch('/member/7/status', ['status' => 'nonaktif'])->assertRedirect('/member/7');
        $this->masuk()->delete('/member/7')->assertRedirect('/member')->assertSessionHas('success', 'Member Bima berhasil dihapus.');

        $metode = collect(Http::recorded())->map(fn ($p) => $p[0]->method())->all();
        $this->assertSame(['PUT', 'PATCH', 'DELETE'], $metode);
    }

    public function test_hapus_ditolak_409_menampilkan_pesan_api(): void
    {
        Http::fake(["{$this->api}/member/1" => Http::response(['message' => 'Raka sudah punya riwayat.'], 409)]);

        $this->masuk()->delete('/member/1')->assertRedirect('/member')->assertSessionHas('error', 'Raka sudah punya riwayat.');
    }

    public function test_server_mati_menampilkan_pesan(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('mati'));

        $this->masuk()->get('/member')->assertOk()->assertSee('API Server tidak dapat dihubungi');
    }

    public function test_token_kedaluwarsa_kembali_ke_halaman_masuk(): void
    {
        Http::fake(["{$this->api}/member*" => Http::response(['message' => 'x'], 401)]);

        $this->masuk()->get('/member')->assertRedirect('/masuk')->assertSessionMissing('api_token');
    }
}
