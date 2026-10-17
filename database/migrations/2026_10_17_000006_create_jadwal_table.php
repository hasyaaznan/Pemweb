<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Jadwal mingguan tetap per kelompok usia. (K6, K7)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_usia_id')->constrained('kelompok_usia')->restrictOnDelete();
            $table->unsignedTinyInteger('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi', 100);
            $table->date('berlaku_mulai');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // CHECK constraint hanya dipasang di MySQL/MariaDB (SQLite untuk test tidak mendukung ALTER ... ADD CHECK)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE jadwal ADD CONSTRAINT chk_jadwal_1 CHECK (hari BETWEEN 1 AND 7)");
            DB::statement("ALTER TABLE jadwal ADD CONSTRAINT chk_jadwal_2 CHECK (jam_selesai > jam_mulai)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
