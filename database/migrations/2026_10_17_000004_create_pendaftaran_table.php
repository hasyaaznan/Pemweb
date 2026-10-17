<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Formulir pendaftaran calon member (3 langkah) sampai diterima atau ditolak manajemen. (K1)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelompok_usia_id')->constrained('kelompok_usia')->restrictOnDelete();
            $table->string('nama_anak', 100);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('sekolah', 100);
            $table->string('nama_orang_tua', 100);
            $table->string('hubungan', 30);
            $table->string('no_whatsapp', 20);
            $table->string('email', 100);
            $table->string('alamat', 255);
            $table->enum('status', ['menunggu_verifikasi', 'diterima', 'ditolak'])->default('menunggu_verifikasi');
            $table->string('alasan_tolak', 255)->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('diverifikasi_pada')->nullable();
            $table->foreignId('anak_id')->nullable()->unique()->constrained('anak')->nullOnDelete();
            $table->timestamps();
        });

        // CHECK constraint hanya dipasang di MySQL/MariaDB (SQLite untuk test tidak mendukung ALTER ... ADD CHECK)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE pendaftaran ADD CONSTRAINT chk_pendaftaran_1 CHECK (status <> 'ditolak' OR alasan_tolak IS NOT NULL)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran');
    }
};
