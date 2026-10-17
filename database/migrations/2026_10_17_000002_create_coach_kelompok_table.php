<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Kelompok usia yang dipegang tiap coach. Satu coach bisa memegang lebih dari satu KU. (K3, K10)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_kelompok', function (Blueprint $table) {
            $table->foreignId('coach_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kelompok_usia_id')->constrained('kelompok_usia')->restrictOnDelete();
            $table->primary(['coach_id', 'kelompok_usia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_kelompok');
    }
};
