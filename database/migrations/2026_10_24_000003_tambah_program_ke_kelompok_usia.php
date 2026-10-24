<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Keterangan singkat program tiap kelompok usia di halaman depan, sekarang bisa diubah Admin
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelompok_usia', function (Blueprint $table) {
            $table->string('program', 100)->nullable()->after('iuran_bulanan');
        });

        foreach ([6 => 'Gerak dasar lewat permainan', 9 => 'Dribbling, passing, kerja tim', 13 => 'Taktik tim dan persiapan turnamen'] as $usia => $program) {
            DB::table('kelompok_usia')->where('usia_min', $usia)->update(['program' => $program]);
        }
    }

    public function down(): void
    {
        Schema::table('kelompok_usia', function (Blueprint $table) {
            $table->dropColumn('program');
        });
    }
};
