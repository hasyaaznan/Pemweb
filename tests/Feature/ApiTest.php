<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Jadwal;
use App\Models\KelompokUsia;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

// REST API /api/v1: login token, data master member, kelompok usia, coach, jadwal
class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function sebagai(string $email): User
    {
        $user = User::where('email', $email)->firstOrFail();
        Sanctum::actingAs($user);

        return $user;
    }

    private function admin(): User
    {
        return $this->sebagai('admin@spiders.test');
    }

    private function memberBaru(array $ubah = []): array
    {
        return array_replace_recursive([
            'nama' => 'Bima Saputra', 'tanggal_lahir' => '2015-05-02', 'jenis_kelamin' => 'L', 'sekolah' => 'SDN 5 Bandung',
            'kelompok_usia_id' => KelompokUsia::where('nama', 'KU 9–12')->value('id'), 'hubungan_orang_tua' => 'Ayah',
            'orang_tua' => ['nama' => 'Andi Saputra', 'email' => 'andi@contoh.test', 'no_hp' => '0812 9999 0001', 'password' => 'rahasia123'],
        ], $ubah);
    }

    public function test_masuk_dapat_token_lalu_saya_dan_keluar(): void
    {
        $res = $this->postJson('/api/v1/masuk', ['login' => 'admin@spiders.test', 'password' => 'password'])
            ->assertOk()->assertJsonPath('data.peran', 'manajemen')->assertJsonStructure(['token', 'token_type']);
        $token = $res->json('token');

        $this->withToken($token)->getJson('/api/v1/saya')->assertOk()->assertJsonPath('data.email', 'admin@spiders.test');
        $this->withToken($token)->postJson('/api/v1/keluar')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_masuk_dengan_no_hp_dan_sandi_salah(): void
    {
        $this->postJson('/api/v1/masuk', ['login' => '+62 812-3456-0000', 'password' => 'password'])->assertOk();
        $this->postJson('/api/v1/masuk', ['login' => 'admin@spiders.test', 'password' => 'salah'])
            ->assertStatus(422)->assertJsonValidationErrors('login');
    }

    public function test_tanpa_token_401_json_walau_tanpa_header_accept(): void
    {
        $this->get('/api/v1/member')->assertStatus(401)->assertJsonStructure(['message']);
    }

    public function test_admin_crud_member_lengkap(): void
    {
        $this->admin();

        // POST
        $id = $this->postJson('/api/v1/member', $this->memberBaru())
            ->assertCreated()
            ->assertJsonPath('data.nama', 'Bima Saputra')
            ->assertJsonPath('data.orang_tua.no_hp', '081299990001')
            ->assertJsonPath('data.status', 'aktif')
            ->json('data.id');
        $this->assertTrue(User::where('email', 'andi@contoh.test')->first()->isOrangTua());

        // GET daftar dan detail
        $this->getJson('/api/v1/member?cari=bima')->assertOk()->assertJsonCount(1, 'data')->assertJsonStructure(['data', 'links', 'meta']);
        $this->getJson("/api/v1/member/{$id}")->assertOk()->assertJsonPath('data.kelompok_usia.nama', 'KU 9–12');

        // PUT: semua kolom wajib
        $this->putJson("/api/v1/member/{$id}", ['nama' => 'Bima'])->assertStatus(422)->assertJsonValidationErrors(['tanggal_lahir', 'orang_tua']);
        $this->putJson("/api/v1/member/{$id}", $this->memberBaru(['nama' => 'Bima S.', 'orang_tua' => ['alamat' => 'Jl. Riau 1']]))
            ->assertOk()->assertJsonPath('data.nama', 'Bima S.')->assertJsonPath('data.orang_tua.alamat', 'Jl. Riau 1');

        // PATCH: cukup kolom yang diubah, yang lain tetap
        $this->patchJson("/api/v1/member/{$id}", ['status' => 'nonaktif', 'orang_tua' => ['nama' => 'Andi S.']])
            ->assertOk()->assertJsonPath('data.status', 'nonaktif')->assertJsonPath('data.nama', 'Bima S.')
            ->assertJsonPath('data.orang_tua.nama', 'Andi S.')->assertJsonPath('data.orang_tua.email', 'andi@contoh.test');

        // DELETE
        $this->deleteJson("/api/v1/member/{$id}")->assertOk()->assertJsonStructure(['message']);
        $this->getJson("/api/v1/member/{$id}")->assertNotFound()->assertJsonPath('message', 'Data tidak ditemukan.');
    }

    public function test_member_baru_memakai_akun_orang_tua_yang_sudah_ada(): void
    {
        $this->admin();
        $rina = User::where('email', 'rina.pratama@gmail.com')->first();

        $this->postJson('/api/v1/member', $this->memberBaru(['nama' => 'Rara Pratama', 'orang_tua' => ['email' => $rina->email, 'no_hp' => $rina->no_hp]]))
            ->assertCreated()->assertJsonPath('data.orang_tua.id', $rina->id);
        $this->assertSame(2, $rina->anak()->count());

        // Email milik coach tidak boleh dipakai sebagai orang tua
        $this->postJson('/api/v1/member', $this->memberBaru(['orang_tua' => ['email' => 'dita@spiders.test']]))
            ->assertStatus(422)->assertJsonValidationErrors('orang_tua.email');
    }

    public function test_member_dengan_riwayat_tidak_dihapus(): void
    {
        $this->admin();
        $raka = Anak::where('nama', 'Raka Pratama')->first();
        Tagihan::create(['anak_id' => $raka->id, 'periode' => '2026-11-01', 'nominal' => 250000, 'jatuh_tempo' => '2026-11-10', 'status' => 'belum_bayar']);

        $this->deleteJson("/api/v1/member/{$raka->id}")->assertStatus(409);
        $this->assertModelExists($raka);
    }

    public function test_orang_tua_dan_coach_hanya_melihat_member_miliknya(): void
    {
        $raka = Anak::where('nama', 'Raka Pratama')->first();
        $lain = Anak::create($this->anakLain('KU 13–16'));

        $this->sebagai('rina.pratama@gmail.com');
        $this->getJson('/api/v1/member')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $raka->id);
        $this->getJson("/api/v1/member/{$lain->id}")->assertNotFound();
        $this->postJson('/api/v1/member', $this->memberBaru())->assertForbidden();
        $this->deleteJson("/api/v1/member/{$raka->id}")->assertForbidden();

        // Dita memegang KU 6–8 dan KU 9–12, bukan KU 13–16
        $this->sebagai('dita@spiders.test');
        $this->getJson('/api/v1/member')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/member/{$lain->id}")->assertNotFound();
        $this->getJson('/api/v1/coach')->assertForbidden();
    }

    private function anakLain(string $ku): array
    {
        $ortu = User::factory()->create();

        return [
            'orang_tua_id' => $ortu->id, 'kelompok_usia_id' => KelompokUsia::where('nama', $ku)->value('id'), 'nama' => 'Dewi',
            'tanggal_lahir' => '2011-01-01', 'jenis_kelamin' => 'P', 'hubungan_orang_tua' => 'Ibu', 'tanggal_masuk' => '2026-01-01',
        ];
    }

    public function test_crud_kelompok_usia_dan_rentang_tidak_boleh_bertabrakan(): void
    {
        $this->admin();
        $this->getJson('/api/v1/kelompok-usia')->assertOk()->assertJsonCount(3, 'data')->assertJsonPath('data.1.jumlah_anak_aktif', 1);

        $this->postJson('/api/v1/kelompok-usia', ['nama' => 'KU 15–18', 'usia_min' => 15, 'usia_max' => 18, 'iuran_bulanan' => 300000])
            ->assertStatus(422)->assertJsonValidationErrors('usia_min');
        $id = $this->postJson('/api/v1/kelompok-usia', ['nama' => 'KU 17–19', 'usia_min' => 17, 'usia_max' => 19, 'iuran_bulanan' => 350000])
            ->assertCreated()->json('data.id');

        $this->patchJson("/api/v1/kelompok-usia/{$id}", ['iuran_bulanan' => 375000])->assertOk()
            ->assertJsonPath('data.iuran_bulanan', 375000)->assertJsonPath('data.usia_min', 17);
        $this->putJson("/api/v1/kelompok-usia/{$id}", ['nama' => 'KU 17–20', 'usia_min' => 17, 'usia_max' => 20, 'iuran_bulanan' => 400000])
            ->assertOk()->assertJsonPath('data.nama', 'KU 17–20');
        $this->deleteJson("/api/v1/kelompok-usia/{$id}")->assertOk();

        // Kelompok yang sudah punya anggota tidak dihapus
        $this->deleteJson('/api/v1/kelompok-usia/'.KelompokUsia::where('nama', 'KU 9–12')->value('id'))->assertStatus(409);
    }

    public function test_crud_jadwal(): void
    {
        $this->admin();
        $ku = KelompokUsia::where('nama', 'KU 6–8')->value('id');

        $this->getJson("/api/v1/jadwal?kelompok_usia_id={$ku}")->assertOk()->assertJsonCount(1, 'data');
        // KU 6–8 sudah latihan hari Sabtu
        $this->postJson('/api/v1/jadwal', ['kelompok_usia_id' => $ku, 'hari' => 6, 'jam_mulai' => '09:00', 'jam_selesai' => '10:00', 'lokasi' => 'GOR', 'berlaku_mulai' => '2026-11-01'])
            ->assertStatus(422)->assertJsonValidationErrors('hari');

        $id = $this->postJson('/api/v1/jadwal', ['kelompok_usia_id' => $ku, 'hari' => 3, 'jam_mulai' => '16:00', 'jam_selesai' => '17:30', 'lokasi' => 'GOR Rabbani', 'berlaku_mulai' => '2026-11-01'])
            ->assertCreated()->assertJsonPath('data.nama_hari', 'Rabu')->json('data.id');

        $this->patchJson("/api/v1/jadwal/{$id}", ['jam_selesai' => '15:00'])->assertStatus(422)->assertJsonValidationErrors('jam_selesai');
        $this->patchJson("/api/v1/jadwal/{$id}", ['jam_selesai' => '18:00'])->assertOk()
            ->assertJsonPath('data.jam_mulai', '16:00')->assertJsonPath('data.jam_selesai', '18:00');
        $this->deleteJson("/api/v1/jadwal/{$id}")->assertOk();
        $this->assertNull(Jadwal::find($id));

        // Orang tua boleh membaca jadwal, tapi tidak boleh mengubah
        $this->sebagai('rina.pratama@gmail.com');
        $this->getJson('/api/v1/jadwal')->assertOk()->assertJsonCount(5, 'data');
        $this->postJson('/api/v1/jadwal', [])->assertForbidden();
    }

    public function test_crud_coach(): void
    {
        $this->admin();
        $ku = KelompokUsia::orderBy('usia_min')->pluck('id');

        $id = $this->postJson('/api/v1/coach', ['nama' => 'Bayu', 'email' => 'bayu@spiders.test', 'no_hp' => '081277770000', 'jabatan' => 'Asisten coach', 'kelompok_usia_ids' => [$ku[2]], 'password' => 'rahasia123'])
            ->assertCreated()->assertJsonPath('data.kelompok_usia.0.nama', 'KU 13–16')->json('data.id');

        // Coach baru bisa langsung masuk dengan kata sandi awal
        $token = $this->postJson('/api/v1/masuk', ['login' => 'bayu@spiders.test', 'password' => 'rahasia123'])->assertOk()->json('token');

        $this->admin();
        $this->getJson('/api/v1/coach')->assertOk()->assertJsonCount(2, 'data');
        $this->patchJson("/api/v1/coach/{$id}", ['kelompok_usia_ids' => [$ku[0], $ku[2]]])->assertOk()->assertJsonCount(2, 'data.kelompok_usia');
        $this->putJson("/api/v1/coach/{$id}", ['nama' => 'Bayu'])->assertStatus(422)->assertJsonValidationErrors(['email', 'no_hp']);

        // Nonaktif = tokennya dicabut
        $this->patchJson("/api/v1/coach/{$id}", ['aktif' => false])->assertOk()->assertJsonPath('data.aktif', false);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $id]);
        $this->postJson('/api/v1/masuk', ['login' => 'bayu@spiders.test', 'password' => 'rahasia123'])->assertStatus(422);

        // Id milik orang tua bukan coach
        $rina = User::where('email', 'rina.pratama@gmail.com')->value('id');
        $this->getJson("/api/v1/coach/{$rina}")->assertNotFound();

        $this->deleteJson("/api/v1/coach/{$id}")->assertOk();
        $this->assertNull(User::find($id));
        $this->assertNotNull($token);
    }

    public function test_alamat_dan_metode_salah_dijawab_json(): void
    {
        $this->admin();
        $this->getJson('/api/v1/tidak-ada')->assertNotFound()->assertJsonPath('message', 'Alamat API tidak ditemukan.');
        $this->deleteJson('/api/v1/member')->assertStatus(405);
    }
}
