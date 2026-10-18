<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        // Email lupa kata sandi dalam bahasa Indonesia (K2, layar L16)
        ResetPassword::toMailUsing(function (object $user, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $user->email]);

            return (new MailMessage)
                ->subject('Atur ulang kata sandi Bandung Spiders')
                ->greeting('Halo, '.$user->nama)
                ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.')
                ->action('Atur ulang kata sandi', $url)
                ->line('Tautan ini berlaku 30 menit. Kalau Anda tidak meminta, abaikan email ini.')
                ->salutation('Admin Bandung Spiders Basketball');
        });
    }
}
