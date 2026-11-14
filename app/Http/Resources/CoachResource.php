<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CoachResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'jabatan' => $this->jabatan,
            'lisensi' => $this->lisensi,
            'foto_url' => $this->foto ? asset($this->foto) : null,
            'tampil_di_landing' => (bool) $this->tampil_di_landing,
            'aktif' => (bool) $this->aktif,
            'kelompok_usia' => $this->whenLoaded('kelompokDipegang', fn () => $this->kelompokDipegang->map(fn ($k) => ['id' => $k->id, 'nama' => $k->nama])->values()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
