<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Kehadiran satu anak di satu sesi latihan.
class Absensi extends Model
{
    protected $table = 'absensi';

    protected $fillable = ['sesi_latihan_id', 'anak_id', 'status', 'dicatat_oleh'];

    public function sesiLatihan(): BelongsTo
    {
        return $this->belongsTo(SesiLatihan::class, 'sesi_latihan_id');
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class, 'anak_id');
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
