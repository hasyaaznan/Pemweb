<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Setiap unggahan bukti transfer. Bukti yang ditolak tetap disimpan sebagai riwayat. (K5, K10, K11)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukti_transfer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihan')->restrictOnDelete();
            $table->foreignId('diunggah_oleh')->constrained('users')->restrictOnDelete();
            $table->string('file_path', 255);
            $table->enum('file_tipe', ['jpg', 'png', 'pdf']);
            $table->unsignedInteger('ukuran_kb');
            $table->date('tanggal_transfer');
            $table->unsignedInteger('nominal_transfer');
            $table->enum('status', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $table->string('alasan_tolak', 255)->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('diverifikasi_pada')->nullable();
            $table->timestamps();
        });

        // CHECK constraint hanya dipasang di MySQL/MariaDB (SQLite untuk test tidak mendukung ALTER ... ADD CHECK)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE bukti_transfer ADD CONSTRAINT chk_bukti_transfer_1 CHECK (ukuran_kb <= 2048)");
            DB::statement("ALTER TABLE bukti_transfer ADD CONSTRAINT chk_bukti_transfer_2 CHECK (status <> 'ditolak' OR alasan_tolak IS NOT NULL)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_transfer');
    }
};
