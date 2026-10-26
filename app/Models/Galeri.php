<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Foto atau video kegiatan di halaman depan (K8)
class Galeri extends Model
{
    protected $table = 'galeri';

    protected $fillable = ['jenis', 'file', 'poster', 'youtube', 'keterangan', 'urutan'];

    // Gambar kecil di grid: foto itu sendiri, poster video, atau gambar dari YouTube
    public function gambar(): ?string
    {
        if ($this->jenis === 'foto') {
            return $this->file ? asset($this->file) : null;
        }
        if ($this->poster) {
            return asset($this->poster);
        }

        return $this->youtube ? 'https://i.ytimg.com/vi/'.$this->youtube.'/hqdefault.jpg' : null;
    }
}
