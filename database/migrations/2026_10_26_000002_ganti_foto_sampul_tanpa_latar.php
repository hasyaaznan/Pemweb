<?php

use App\Models\Pengaturan;
use Illuminate\Database\Migrations\Migration;

// Sampul halaman depan diganti foto tanpa latar (anak-anak dan coach di atas lingkaran oranye).
// Hanya mengganti sampul bawaan; sampul yang sudah diunggah Admin dibiarkan.
return new class extends Migration
{
    public function up(): void
    {
        Pengaturan::where('kunci', 'foto_sampul')->where('nilai', 'images/klub/sampul.webp')
            ->update(['nilai' => 'images/klub/sampul-tanpa-latar.webp']);
    }

    public function down(): void
    {
        Pengaturan::where('kunci', 'foto_sampul')->where('nilai', 'images/klub/sampul-tanpa-latar.webp')
            ->update(['nilai' => 'images/klub/sampul.webp']);
    }
};
