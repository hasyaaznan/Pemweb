<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// Satu member: data anak, kelompok usia, dan kontak orang tua
class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
            'usia' => $this->tanggal_lahir?->age,
            'jenis_kelamin' => $this->jenis_kelamin,
            'sekolah' => $this->sekolah,
            'hubungan_orang_tua' => $this->hubungan_orang_tua,
            'status' => $this->status,
            'tanggal_masuk' => $this->tanggal_masuk?->format('Y-m-d'),
            'kelompok_usia_id' => $this->kelompok_usia_id,
            'kelompok_usia' => $this->whenLoaded('kelompokUsia', fn () => ['id' => $this->kelompokUsia->id, 'nama' => $this->kelompokUsia->nama]),
            'orang_tua' => $this->whenLoaded('orangTua', fn () => [
                'id' => $this->orangTua->id,
                'nama' => $this->orangTua->nama,
                'email' => $this->orangTua->email,
                'no_hp' => $this->orangTua->no_hp,
                'alamat' => $this->orangTua->alamat,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
