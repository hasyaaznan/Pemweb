<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Nilai yang bisa diubah manajemen tanpa mengubah kode: rekening tujuan, No. WhatsApp admin, alamat GOR, tanggal jatuh tempo.
class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $primaryKey = 'kunci';

    public $incrementing = false;

    protected $keyType = 'string';

    const CREATED_AT = null;

    protected $fillable = ['kunci', 'nilai'];
}
