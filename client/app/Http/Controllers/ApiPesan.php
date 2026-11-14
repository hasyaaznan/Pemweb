<?php

namespace App\Http\Controllers;

// Pesan untuk jawaban API yang gagal
class ApiPesan
{
    public const TIDAK_TERHUBUNG = 'API Server tidak dapat dihubungi. Pastikan proyek spiders-web berjalan di port 8000.';

    public static function untuk(int $status): string
    {
        return match ($status) {
            503 => self::TIDAK_TERHUBUNG,
            403 => 'Akun ini tidak boleh melakukan aksi tersebut (hanya Admin).',
            404 => 'Member tidak ditemukan.',
            default => 'API Server mengembalikan error (HTTP '.$status.').',
        };
    }
}
