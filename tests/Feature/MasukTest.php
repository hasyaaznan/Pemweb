<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

// L0 masuk, L16 lupa kata sandi, dan pembagian halaman per peran (K10)
class MasukTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_halaman_masuk_tampil(): void
    {
        $this->get('/masuk')->assertOk()->assertSee('Email atau No. HP')->assertSee('Lupa kata sandi?');
    }

    public function test_masuk_dengan_email(): void
    {
        $this->post('/masuk', ['login' => 'Rina.Pratama@gmail.com', 'password' => 'password'])
            ->assertRedirect(route('beranda'));
        $this->assertAuthenticatedAs(User::where('email', 'rina.pratama@gmail.com')->first());
    }

    public function test_masuk_dengan_no_hp_format_62(): void
    {
        $this->post('/masuk', ['login' => '+62 812-3456-7890', 'password' => 'password'])->assertRedirect(route('beranda'));
        $this->assertAuthenticated();
    }

    public function test_kata_sandi_salah(): void
    {
        $this->from('/masuk')->post('/masuk', ['login' => 'rina.pratama@gmail.com', 'password' => 'salah123'])
            ->assertRedirect('/masuk')
            ->assertSessionHasErrors(['password' => 'Email atau kata sandi salah. Coba lagi atau atur ulang.']);
        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        User::where('email', 'rina.pratama@gmail.com')->update(['aktif' => false]);
        $this->post('/masuk', ['login' => 'rina.pratama@gmail.com', 'password' => 'password'])->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_beranda_mengarahkan_sesuai_peran(): void
    {
        foreach (['rina.pratama@gmail.com' => 'ortu.beranda', 'dita@spiders.test' => 'coach.beranda', 'admin@spiders.test' => 'manajemen.beranda'] as $email => $rute) {
            $this->actingAs(User::where('email', $email)->first())->get('/beranda')->assertRedirect(route($rute));
        }
    }

    public function test_peran_lain_ditolak(): void
    {
        $coach = User::where('email', 'dita@spiders.test')->first();
        $this->actingAs($coach)->get('/orang-tua')->assertForbidden()->assertSee('Akses ditolak');
        $this->actingAs(User::where('email', 'rina.pratama@gmail.com')->first())->get('/manajemen')->assertForbidden();
    }

    public function test_tamu_diarahkan_ke_masuk(): void
    {
        $this->get('/orang-tua/tagihan')->assertRedirect(route('login'));
    }

    public function test_keluar(): void
    {
        $this->actingAs(User::first())->post('/keluar')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_lupa_sandi_lewat_no_hp_mengirim_tautan_dan_bisa_atur_ulang(): void
    {
        Notification::fake();
        $rina = User::where('email', 'rina.pratama@gmail.com')->first();

        $this->post('/lupa-kata-sandi', ['login' => '081234567890'])->assertSessionHas('terkirim');

        $token = null;
        Notification::assertSentTo($rina, ResetPassword::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });

        $this->get('/atur-ulang-kata-sandi/'.$token.'?email='.$rina->email)->assertOk();
        $this->post('/atur-ulang-kata-sandi', [
            'token' => $token, 'email' => $rina->email, 'password' => 'sandibaru123', 'password_confirmation' => 'sandibaru123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('sandibaru123', $rina->fresh()->password));
    }

    public function test_lupa_sandi_akun_tidak_dikenal_tetap_pesan_umum(): void
    {
        Notification::fake();
        $this->post('/lupa-kata-sandi', ['login' => 'tidakada@contoh.com'])->assertSessionHas('terkirim');
        Notification::assertNothingSent();
    }
}
