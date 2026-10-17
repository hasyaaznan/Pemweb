<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Tiga dokumen wajib per pendaftaran (langkah 3). File disimpan di storage, database hanya menyimpan path. (K1)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete();
            $table->enum('jenis', ['foto_3x4', 'akte', 'kk']);
            $table->string('file_path', 255);
            $table->enum('file_tipe', ['jpg', 'png', 'pdf']);
            $table->unsignedInteger('ukuran_kb');
            $table->timestamps();
            $table->unique(['pendaftaran_id', 'jenis'], 'uq_dok');
        });

        // CHECK constraint hanya dipasang di MySQL/MariaDB (SQLite untuk test tidak mendukung ALTER ... ADD CHECK)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE dokumen_pendaftaran ADD CONSTRAINT chk_dokumen_pendaftaran_1 CHECK (ukuran_kb <= 2048)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_pendaftaran');
    }
};
