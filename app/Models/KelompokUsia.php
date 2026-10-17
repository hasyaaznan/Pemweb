<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Tiga kelompok usia beserta iuran bulanannya.
class KelompokUsia extends Model
{
    protected $table = 'kelompok_usia';

    protected $fillable = ['nama', 'usia_min', 'usia_max', 'iuran_bulanan'];

    public function anak(): HasMany
    {
        return $this->hasMany(Anak::class, 'kelompok_usia_id');
    }

    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class, 'kelompok_usia_id');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'kelompok_usia_id');
    }

    public function sesiLatihan(): HasMany
    {
        return $this->hasMany(SesiLatihan::class, 'kelompok_usia_id');
    }

    public function coach(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coach_kelompok', 'kelompok_usia_id', 'coach_id');
    }
}
