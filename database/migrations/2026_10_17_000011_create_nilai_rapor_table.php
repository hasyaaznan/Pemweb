<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Nilai dan catatan coach untuk satu kategori dalam satu rapor (5 baris per rapor terbit). (K3, K4)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_rapor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapor_id')->constrained('rapor')->cascadeOnDelete();
            $table->foreignId('kategori_rapor_id')->constrained('kategori_rapor')->restrictOnDelete();
            $table->enum('nilai', ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-']);
            $table->string('catatan', 300)->nullable();
            $table->timestamps();
            $table->unique(['rapor_id', 'kategori_rapor_id'], 'uq_nilai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_rapor');
    }
};
