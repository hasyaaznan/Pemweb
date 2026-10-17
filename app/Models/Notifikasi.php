<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Notifikasi di dalam website untuk orang tua (ikon lonceng).
class Notifikasi extends Model
{
    protected $table = 'notifikasi';

    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'jenis', 'judul', 'isi', 'tautan', 'dibaca_pada'];

    protected function casts(): array
    {
        return ['dibaca_pada' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
