<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tagihan iuran terbit setiap tanggal 1 pukul 06.00 (K5). Di server perlu cron: * * * * * php artisan schedule:run
Schedule::command('tagihan:buat')->monthlyOn(1, '06:00');
