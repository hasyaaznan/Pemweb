<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Satu latihan pada tanggal tertentu. Dibuat dari jadwal; dipakai untuk absensi dan untuk perubahan jadwal per tanggal.
class SesiLatihan extends Model
{
    protected $table = 'sesi_latihan';

    protected $fillable = ['kelompok_usia_id', 'jadwal_id', 'tanggal', 'jam_mulai', 'jam_selesai', 'lokasi', 'status', 'alasan_perubahan', 'diubah_oleh'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function kelompokUsia(): BelongsTo
    {
        return $this->belongsTo(KelompokUsia::class, 'kelompok_usia_id');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    public function diubahOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'sesi_latihan_id');
    }
}
