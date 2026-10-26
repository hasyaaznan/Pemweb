<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Isi halaman depan yang dikelola Admin (K7, K8): linimasa klub, turnamen, galeri foto/video, tanya jawab.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linimasa', function (Blueprint $table) {
            $table->id();
            $table->string('tahun', 10);
            $table->string('isi', 200);
            $table->timestamps();
        });

        // Turnamen dan event yang diikuti klub, beserta hasilnya kalau ada (K8)
        Schema::create('turnamen', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 120);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('lokasi', 150);
            $table->string('hasil', 200)->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });

        // Foto atau video kegiatan (K8). Video bisa file mp4 atau ID YouTube.
        Schema::create('galeri', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['foto', 'video']);
            $table->string('file')->nullable();
            $table->string('poster')->nullable();
            $table->string('youtube', 20)->nullable();
            $table->string('keterangan', 150);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('tanya_jawab', function (Blueprint $table) {
            $table->id();
            $table->string('pertanyaan', 200);
            $table->string('jawaban', 500);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanya_jawab');
        Schema::dropIfExists('galeri');
        Schema::dropIfExists('turnamen');
        Schema::dropIfExists('linimasa');
    }
};
