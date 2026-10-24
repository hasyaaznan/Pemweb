<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Pertanyaan yang sering ditanyakan calon member, tampil di halaman depan
class TanyaJawab extends Model
{
    protected $table = 'tanya_jawab';

    protected $fillable = ['pertanyaan', 'jawaban', 'urutan'];
}
