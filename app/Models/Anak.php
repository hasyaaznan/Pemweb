<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// Data anak yang sudah resmi menjadi member.
class Anak extends Model
{
    protected $table = 'anak';

    protected $fillable = ['orang_tua_id', 'kelompok_usia_id', 'nama', 'tanggal_lahir', 'jenis_kelamin', 'sekolah', 'hubungan_orang_tua', 'status', 'tanggal_masuk'];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date', 'tanggal_masuk' => 'date'];
    }

    public function orangTua(): BelongsTo
    {
        return $this->belongsTo(User::class, 'orang_tua_id');
    }

    public function kelompokUsia(): BelongsTo
    {
        return $this->belongsTo(KelompokUsia::class, 'kelompok_usia_id');
    }

    public function pendaftaran(): HasOne
    {
        return $this->hasOne(Pendaftaran::class, 'anak_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'anak_id');
    }

    public function rapor(): HasMany
    {
        return $this->hasMany(Rapor::class, 'anak_id');
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(Tagihan::class, 'anak_id');
    }
}
