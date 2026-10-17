<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Rapor bulanan satu anak. Hanya satu rapor per anak per bulan.
class Rapor extends Model
{
    protected $table = 'rapor';

    protected $fillable = ['anak_id', 'coach_id', 'periode', 'status', 'terbit_pada'];

    protected function casts(): array
    {
        return ['periode' => 'date', 'terbit_pada' => 'datetime'];
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class, 'anak_id');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function nilaiRapor(): HasMany
    {
        return $this->hasMany(NilaiRapor::class, 'rapor_id');
    }
}
