<?php

namespace App\Support;

use App\Models\Pengaturan;

// Nilai dari tabel pengaturan (rekening, WhatsApp admin, dll.) yang bisa diubah manajemen
class Klub
{
    private static ?array $cache = null;

    public static function get(string $kunci, ?string $bawaan = null): ?string
    {
        self::$cache ??= Pengaturan::pluck('nilai', 'kunci')->all();

        return self::$cache[$kunci] ?? $bawaan;
    }

    public static function lupakan(): void
    {
        self::$cache = null;
    }
}
