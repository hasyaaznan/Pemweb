<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Akun login untuk 3 peran: orang_tua, coach, manajemen (K2).
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const PERAN_ORANG_TUA = 'orang_tua';
    public const PERAN_COACH = 'coach';
    public const PERAN_MANAJEMEN = 'manajemen';

    protected $fillable = ['nama', 'email', 'no_hp', 'password', 'peran', 'alamat', 'aktif', 'jabatan', 'lisensi', 'foto', 'tampil_di_landing'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'aktif' => 'boolean', 'tampil_di_landing' => 'boolean'];
    }

    // Orang tua: anak miliknya (dasar aturan akses K10)
    public function anak(): HasMany
    {
        return $this->hasMany(Anak::class, 'orang_tua_id');
    }

    // Coach: kelompok usia yang ia pegang (K3, K10)
    public function kelompokDipegang(): BelongsToMany
    {
        return $this->belongsToMany(KelompokUsia::class, 'coach_kelompok', 'coach_id', 'kelompok_usia_id');
    }

    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'user_id');
    }

    public function isOrangTua(): bool
    {
        return $this->peran === self::PERAN_ORANG_TUA;
    }

    public function isCoach(): bool
    {
        return $this->peran === self::PERAN_COACH;
    }

    public function isManajemen(): bool
    {
        return $this->peran === self::PERAN_MANAJEMEN;
    }
}
