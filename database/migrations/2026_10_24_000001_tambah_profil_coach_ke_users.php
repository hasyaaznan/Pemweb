<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Data coach yang tampil di halaman depan: jabatan, lisensi, foto. Dikelola Admin.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jabatan', 60)->nullable()->after('alamat');
            $table->string('lisensi', 100)->nullable()->after('jabatan');
            $table->string('foto')->nullable()->after('lisensi');
            $table->boolean('tampil_di_landing')->default(false)->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['jabatan', 'lisensi', 'foto', 'tampil_di_landing']);
        });
    }
};
