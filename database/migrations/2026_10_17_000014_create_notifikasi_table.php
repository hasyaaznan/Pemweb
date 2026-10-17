<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Notifikasi di dalam website untuk orang tua (ikon lonceng). (K13)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('jenis', ['tagihan_terbit', 'bukti_ditolak', 'iuran_lunas', 'rapor_terbit', 'jadwal_berubah']);
            $table->string('judul', 100);
            $table->string('isi', 255);
            $table->string('tautan', 255)->nullable();
            $table->dateTime('dibaca_pada')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
