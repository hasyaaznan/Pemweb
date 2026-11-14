<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KelompokUsiaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'usia_min' => $this->usia_min,
            'usia_max' => $this->usia_max,
            'iuran_bulanan' => $this->iuran_bulanan,
            'program' => $this->program,
            'jumlah_anak_aktif' => $this->whenCounted('anak'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
