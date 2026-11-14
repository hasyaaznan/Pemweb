<?php

namespace App\Support;

use App\Models\Anak;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

// Batas data yang boleh dilihat setiap peran lewat API (K10):
// Admin melihat semua, coach hanya anak di kelompok yang ia pegang, orang tua hanya anaknya sendiri.
class AksesData
{
    /** @return Builder<Anak> */
    public static function anak(User $user): Builder
    {
        return match ($user->peran) {
            User::PERAN_MANAJEMEN => Anak::query(),
            User::PERAN_COACH => Anak::whereIn('kelompok_usia_id', $user->kelompokDipegang()->pluck('kelompok_usia.id')),
            default => Anak::where('orang_tua_id', $user->id),
        };
    }

    public static function bolehLihatAnak(User $user, int $anakId): bool
    {
        return self::anak($user)->whereKey($anakId)->exists();
    }
}
