<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Turnamen atau event yang diikuti klub (K8). Hasil diisi setelah acara selesai.
class Turnamen extends Model
{
    protected $table = 'turnamen';

    protected $fillable = ['nama', 'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'hasil', 'foto'];

    protected function casts(): array
    {
        return ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];
    }

    // "20–24 Desember 2023" atau "5 Mei 2026"
    public function rentangTanggal(): string
    {
        $mulai = $this->tanggal_mulai;
        $selesai = $this->tanggal_selesai;
        if (! $selesai || $selesai->isSameDay($mulai)) {
            return $mulai->translatedFormat('j F Y');
        }
        if ($selesai->isSameMonth($mulai)) {
            return $mulai->format('j').'–'.$selesai->translatedFormat('j F Y');
        }

        return $mulai->translatedFormat('j M').' – '.$selesai->translatedFormat('j M Y');
    }

    public function akanDatang(): bool
    {
        return ($this->tanggal_selesai ?? $this->tanggal_mulai)->endOfDay()->isFuture();
    }
}
