<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Kehadiran satu anak di satu sesi latihan. (K3, K4)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_latihan_id')->constrained('sesi_latihan')->cascadeOnDelete();
            $table->foreignId('anak_id')->constrained('anak')->restrictOnDelete();
            $table->enum('status', ['hadir', 'izin', 'alpa'])->default('hadir');
            $table->foreignId('dicatat_oleh')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['sesi_latihan_id', 'anak_id'], 'uq_absensi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
