<?php

namespace App\Http\Requests\Api;

use App\Support\Format;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CoachRequest extends ApiRequest
{
    protected function dataLama(): array
    {
        $c = $this->route('coach');

        return $c ? $c->only(['nama', 'email', 'no_hp', 'jabatan', 'lisensi', 'tampil_di_landing', 'aktif'])
            + ['kelompok_usia_ids' => $c->kelompokDipegang()->pluck('kelompok_usia.id')->all()] : [];
    }

    protected function rapikan(): void
    {
        if ($this->filled('no_hp')) {
            $this->merge(['no_hp' => Format::noHp((string) $this->input('no_hp'))]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('coach')?->id;

        return [
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users')->ignore($id)],
            'no_hp' => ['required', 'regex:/^08\d{8,11}$/', Rule::unique('users')->ignore($id)],
            'jabatan' => ['nullable', 'string', 'max:60'],
            'lisensi' => ['nullable', 'string', 'max:100'],
            'tampil_di_landing' => ['sometimes', 'boolean'],
            'aktif' => ['sometimes', 'boolean'],
            'kelompok_usia_ids' => ['sometimes', 'array'],
            'kelompok_usia_ids.*' => ['integer', 'exists:kelompok_usia,id'],
            // Opsional: kalau kosong, coach membuat kata sandi sendiri lewat tautan email
            'password' => ['sometimes', 'nullable', 'string', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return ['no_hp.regex' => 'No. WhatsApp diawali 08 dan berisi 10–13 angka.'] + parent::messages();
    }
}
