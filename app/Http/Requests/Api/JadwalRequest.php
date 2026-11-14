<?php

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;

class JadwalRequest extends ApiRequest
{
    protected function dataLama(): array
    {
        $j = $this->route('jadwal');

        return $j ? [
            'kelompok_usia_id' => $j->kelompok_usia_id, 'hari' => $j->hari,
            'jam_mulai' => substr($j->jam_mulai, 0, 5), 'jam_selesai' => substr($j->jam_selesai, 0, 5),
            'lokasi' => $j->lokasi, 'berlaku_mulai' => $j->berlaku_mulai->toDateString(), 'aktif' => $j->aktif,
        ] : [];
    }

    public function rules(): array
    {
        return [
            'kelompok_usia_id' => ['required', 'integer', 'exists:kelompok_usia,id'],
            // Satu kelompok usia hanya punya satu latihan aktif per hari
            'hari' => ['required', 'integer', 'between:1,7', Rule::unique('jadwal')->where(fn ($q) => $q
                ->where('kelompok_usia_id', $this->input('kelompok_usia_id'))->where('aktif', true))->ignore($this->route('jadwal'))],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'lokasi' => ['required', 'string', 'max:100'],
            'berlaku_mulai' => ['required', 'date'],
            'aktif' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['hari.unique' => 'Kelompok ini sudah punya latihan aktif di hari tersebut.'] + parent::messages();
    }
}
