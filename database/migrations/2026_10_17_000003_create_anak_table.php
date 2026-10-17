<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Data anak yang sudah resmi menjadi member. (K1, K4, K10)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orang_tua_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('kelompok_usia_id')->constrained('kelompok_usia')->restrictOnDelete();
            $table->string('nama', 100);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('sekolah', 100)->nullable();
            $table->string('hubungan_orang_tua', 30);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->date('tanggal_masuk');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anak');
    }
};
