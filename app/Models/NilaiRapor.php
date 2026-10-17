<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Nilai dan catatan coach untuk satu kategori dalam satu rapor (5 baris per rapor terbit).
class NilaiRapor extends Model
{
    protected $table = 'nilai_rapor';

    protected $fillable = ['rapor_id', 'kategori_rapor_id', 'nilai', 'catatan'];

    public function rapor(): BelongsTo
    {
        return $this->belongsTo(Rapor::class, 'rapor_id');
    }

    public function kategoriRapor(): BelongsTo
    {
        return $this->belongsTo(KategoriRapor::class, 'kategori_rapor_id');
    }
}
