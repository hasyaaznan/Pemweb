<?php

namespace App\Console\Commands;

use App\Support\TagihanBulanan;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class BuatTagihanBulanan extends Command
{
    protected $signature = 'tagihan:buat {--periode= : Bulan tagihan, format YYYY-MM (bawaan: bulan ini)}';

    protected $description = 'Buat tagihan iuran bulanan untuk semua anak aktif yang belum punya tagihan bulan itu';

    public function handle(): int
    {
        $periode = $this->option('periode') ? Carbon::createFromFormat('!Y-m', $this->option('periode')) : now();
        $jumlah = TagihanBulanan::buat($periode);
        $this->info("{$jumlah} tagihan ".$periode->translatedFormat('F Y').' dibuat.');

        return self::SUCCESS;
    }
}
