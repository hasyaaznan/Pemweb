<?php

namespace App\Http\Resources;

use App\Http\Controllers\Admin\JadwalController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JadwalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kelompok_usia' => $this->whenLoaded('kelompokUsia', fn () => ['id' => $this->kelompokUsia->id, 'nama' => $this->kelompokUsia->nama]),
            'kelompok_usia_id' => $this->kelompok_usia_id,
            'hari' => $this->hari,
            'nama_hari' => JadwalController::HARI[$this->hari] ?? null,
            'jam_mulai' => substr($this->jam_mulai, 0, 5),
            'jam_selesai' => substr($this->jam_selesai, 0, 5),
            'lokasi' => $this->lokasi,
            'berlaku_mulai' => $this->berlaku_mulai?->format('Y-m-d'),
            'aktif' => $this->aktif,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
