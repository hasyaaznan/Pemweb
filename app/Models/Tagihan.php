<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Tagihan iuran bulanan per anak, dibuat sistem setiap tanggal 1.
class Tagihan extends Model
{
    protected $table = 'tagihan';

    protected $fillable = ['anak_id', 'periode', 'nominal', 'jatuh_tempo', 'status'];

    protected function casts(): array
    {
        return ['periode' => 'date', 'jatuh_tempo' => 'date'];
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class, 'anak_id');
    }

    public function buktiTransfer(): HasMany
    {
        return $this->hasMany(BuktiTransfer::class, 'tagihan_id');
    }
}
