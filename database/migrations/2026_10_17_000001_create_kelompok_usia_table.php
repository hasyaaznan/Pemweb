<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Tiga kelompok usia beserta iuran bulanannya. (K1, K7)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelompok_usia', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 20)->unique();
            $table->unsignedTinyInteger('usia_min');
            $table->unsignedTinyInteger('usia_max');
            $table->unsignedInteger('iuran_bulanan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelompok_usia');
    }
};
