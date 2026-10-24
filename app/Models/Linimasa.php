<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Satu tonggak sejarah klub di halaman depan, diurutkan menurut tahun.
class Linimasa extends Model
{
    protected $table = 'linimasa';

    protected $fillable = ['tahun', 'isi'];
}
