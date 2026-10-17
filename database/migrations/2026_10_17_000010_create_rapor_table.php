<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Rapor bulanan satu anak. Hanya satu rapor per anak per bulan. (K3, K4)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anak_id')->constrained('anak')->restrictOnDelete();
            $table->foreignId('coach_id')->constrained('users')->restrictOnDelete();
            $table->date('periode');
            $table->enum('status', ['draf', 'terbit'])->default('draf');
            $table->dateTime('terbit_pada')->nullable();
            $table->timestamps();
            $table->unique(['anak_id', 'periode'], 'uq_rapor');
        });

        // CHECK constraint hanya dipasang di MySQL/MariaDB (SQLite untuk test tidak mendukung ALTER ... ADD CHECK)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE rapor ADD CONSTRAINT chk_rapor_1 CHECK (DAY(periode) = 1)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rapor');
    }
};
