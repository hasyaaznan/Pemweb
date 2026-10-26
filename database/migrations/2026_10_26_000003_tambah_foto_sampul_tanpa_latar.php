<?php

use App\Models\Pengaturan;
use Illuminate\Database\Migrations\Migration;

// Laptop memakai foto sampul tanpa latar di atas lingkaran oranye; HP tetap memakai foto sampul biasa.
// Database yang sudah ada diberi foto tanpa latar bawaan; foto yang sudah diunggah Admin dibiarkan.
return new class extends Migration
{
    public function up(): void
    {
        if (! Pengaturan::exists()) {
            return; // database baru: diisi KontenLandingSeeder
        }
        // Sempat ada versi yang menaruh foto tanpa latar di foto_sampul
        Pengaturan::where('kunci', 'foto_sampul')->where('nilai', 'images/klub/sampul-tanpa-latar.webp')
            ->update(['nilai' => 'images/klub/sampul.webp']);
        Pengaturan::firstOrCreate(['kunci' => 'foto_sampul_tanpa_latar'], ['nilai' => 'images/klub/sampul-tanpa-latar.webp']);
    }

    public function down(): void
    {
        Pengaturan::where('kunci', 'foto_sampul_tanpa_latar')->delete();
    }
};
