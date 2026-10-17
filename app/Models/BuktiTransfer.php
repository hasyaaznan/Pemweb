<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Setiap unggahan bukti transfer. Bukti yang ditolak tetap disimpan sebagai riwayat.
class BuktiTransfer extends Model
{
    protected $table = 'bukti_transfer';

    protected $fillable = ['tagihan_id', 'diunggah_oleh', 'file_path', 'file_tipe', 'ukuran_kb', 'tanggal_transfer', 'nominal_transfer', 'status', 'alasan_tolak', 'diverifikasi_oleh', 'diverifikasi_pada'];

    protected function casts(): array
    {
        return ['tanggal_transfer' => 'date', 'diverifikasi_pada' => 'datetime'];
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    public function diunggahOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diunggah_oleh');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
