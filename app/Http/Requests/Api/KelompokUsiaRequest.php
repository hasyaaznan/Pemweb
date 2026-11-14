<?php

namespace App\Http\Requests\Api;

use App\Models\KelompokUsia;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class KelompokUsiaRequest extends ApiRequest
{
    protected function dataLama(): array
    {
        return $this->route('kelompok_usia')?->only(['nama', 'usia_min', 'usia_max', 'iuran_bulanan', 'program']) ?? [];
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:20', Rule::unique('kelompok_usia')->ignore($this->route('kelompok_usia'))],
            'usia_min' => ['required', 'integer', 'between:3,20'],
            'usia_max' => ['required', 'integer', 'between:3,20', 'gte:usia_min'],
            'iuran_bulanan' => ['required', 'integer', 'min:0', 'max:10000000'],
            'program' => ['nullable', 'string', 'max:100'],
        ];
    }

    // Rentang usia tidak boleh bertabrakan, karena kelompok calon member dipilih otomatis dari tanggal lahir (K1)
    public function after(): array
    {
        return [function (Validator $v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $bentrok = KelompokUsia::where('usia_min', '<=', $this->input('usia_max'))->where('usia_max', '>=', $this->input('usia_min'))
                ->when($this->route('kelompok_usia'), fn ($q, $ku) => $q->whereKeyNot($ku->id))->first();
            if ($bentrok) {
                $v->errors()->add('usia_min', "Rentang usia bertabrakan dengan {$bentrok->nama} ({$bentrok->usia_min}–{$bentrok->usia_max} tahun).");
            }
        }];
    }
}
