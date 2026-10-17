<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Tiga dokumen wajib per pendaftaran (langkah 3). File disimpan di storage, database hanya menyimpan path.
class DokumenPendaftaran extends Model
{
    protected $table = 'dokumen_pendaftaran';

    protected $fillable = ['pendaftaran_id', 'jenis', 'file_path', 'file_tipe', 'ukuran_kb'];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }
}
