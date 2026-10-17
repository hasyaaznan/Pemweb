<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Lima kategori rapor. Dibuat tabel agar urutan dan nama bisa diubah tanpa mengubah struktur. (K3)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_rapor', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 30)->unique();
            $table->unsignedTinyInteger('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_rapor');
    }
};
