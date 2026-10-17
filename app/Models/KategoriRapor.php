<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Lima kategori rapor. Dibuat tabel agar urutan dan nama bisa diubah tanpa mengubah struktur.
class KategoriRapor extends Model
{
    protected $table = 'kategori_rapor';

    public $timestamps = false;

    protected $fillable = ['nama', 'urutan'];

    public function nilaiRapor(): HasMany
    {
        return $this->hasMany(NilaiRapor::class, 'kategori_rapor_id');
    }
}
