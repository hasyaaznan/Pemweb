<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Satu latihan pada tanggal tertentu. Dibuat dari jadwal; dipakai untuk absensi dan untuk perubahan jadwal per tanggal. (K3, K6)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesi_latihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_usia_id')->constrained('kelompok_usia')->restrictOnDelete();
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwal')->nullOnDelete();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi', 100);
            $table->enum('status', ['terjadwal', 'diubah', 'batal'])->default('terjadwal');
            $table->string('alasan_perubahan', 255)->nullable();
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['kelompok_usia_id', 'tanggal'], 'uq_sesi');
        });

        // CHECK constraint hanya dipasang di MySQL/MariaDB (SQLite untuk test tidak mendukung ALTER ... ADD CHECK)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE sesi_latihan ADD CONSTRAINT chk_sesi_latihan_1 CHECK (status = 'terjadwal' OR alasan_perubahan IS NOT NULL)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_latihan');
    }
};
