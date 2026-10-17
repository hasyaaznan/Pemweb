<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Formulir pendaftaran calon member (3 langkah) sampai diterima atau ditolak manajemen.
class Pendaftaran extends Model
{
    protected $table = 'pendaftaran';

    protected $fillable = ['kelompok_usia_id', 'nama_anak', 'tanggal_lahir', 'jenis_kelamin', 'sekolah', 'nama_orang_tua', 'hubungan', 'no_whatsapp', 'email', 'alamat', 'status', 'alasan_tolak', 'diverifikasi_oleh', 'diverifikasi_pada', 'anak_id'];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date', 'diverifikasi_pada' => 'datetime'];
    }

    public function kelompokUsia(): BelongsTo
    {
        return $this->belongsTo(KelompokUsia::class, 'kelompok_usia_id');
    }

    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(Anak::class, 'anak_id');
    }

    public function dokumenPendaftaran(): HasMany
    {
        return $this->hasMany(DokumenPendaftaran::class, 'pendaftaran_id');
    }
}
