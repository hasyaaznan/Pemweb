<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Foto dan video unggahan Admin untuk halaman depan, disimpan di public/unggahan/{folder}.
// Path yang disimpan di database relatif terhadap folder public, misalnya "unggahan/galeri/abc.jpg",
// sama dengan foto bawaan di "images/klub/..." sehingga keduanya ditampilkan dengan asset().
class Berkas
{
    public static function simpan(UploadedFile $file, string $folder): string
    {
        return 'unggahan/'.Storage::disk('unggahan')->putFile($folder, $file);
    }

    // Hanya berkas unggahan yang dihapus; foto bawaan di images/ tetap ada
    public static function hapus(?string $path): void
    {
        if ($path && str_starts_with($path, 'unggahan/')) {
            Storage::disk('unggahan')->delete(substr($path, strlen('unggahan/')));
        }
    }
}
