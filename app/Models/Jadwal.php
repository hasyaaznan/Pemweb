<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Jadwal mingguan tetap per kelompok usia.
class Jadwal extends Model
{
    protected $table = 'jadwal';

    protected $fillable = ['kelompok_usia_id', 'hari', 'jam_mulai', 'jam_selesai', 'lokasi', 'berlaku_mulai', 'aktif'];

    protected function casts(): array
    {
        return ['berlaku_mulai' => 'date', 'aktif' => 'boolean'];
    }

    public function kelompokUsia(): BelongsTo
    {
        return $this->belongsTo(KelompokUsia::class, 'kelompok_usia_id');
    }

    public function sesiLatihan(): HasMany
    {
        return $this->hasMany(SesiLatihan::class, 'jadwal_id');
    }
}
