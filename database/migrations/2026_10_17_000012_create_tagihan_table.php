<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Tagihan iuran bulanan per anak, dibuat sistem setiap tanggal 1. (K5)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anak')->restrictOnDelete();
            $table->date('periode');
            $table->unsignedInteger('nominal');
            $table->date('jatuh_tempo');
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'ditolak'])->default('belum_bayar');
            $table->timestamps();
            $table->unique(['anak_id', 'periode'], 'uq_tagihan');
        });

        // CHECK constraint hanya dipasang di MySQL/MariaDB (SQLite untuk test tidak mendukung ALTER ... ADD CHECK)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE tagihan ADD CONSTRAINT chk_tagihan_1 CHECK (DAY(periode) = 1)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan');
    }
};
