<?php

namespace App\Http\Requests\Api;

use App\Http\Controllers\PendaftaranController;
use App\Models\User;
use App\Support\Format;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

// Member = data anak + kontak orang tuanya (objek "orang_tua").
// Saat POST, orang tua dengan email atau No. HP yang sama dipakai ulang (satu akun untuk kakak-adik).
class MemberRequest extends ApiRequest
{
    protected function dataLama(): array
    {
        $a = $this->route('member');

        return $a ? [
            'nama' => $a->nama, 'tanggal_lahir' => $a->tanggal_lahir->toDateString(), 'jenis_kelamin' => $a->jenis_kelamin,
            'sekolah' => $a->sekolah, 'kelompok_usia_id' => $a->kelompok_usia_id, 'hubungan_orang_tua' => $a->hubungan_orang_tua,
            'status' => $a->status, 'tanggal_masuk' => $a->tanggal_masuk->toDateString(),
            'orang_tua' => $a->orangTua->only(['nama', 'email', 'no_hp', 'alamat']),
        ] : [];
    }

    protected function rapikan(): void
    {
        if ($this->filled('orang_tua.no_hp')) {
            $this->merge(['orang_tua' => ['no_hp' => Format::noHp((string) $this->input('orang_tua.no_hp'))] + (array) $this->input('orang_tua')]);
        }
    }

    // Akun orang tua yang dipakai: milik member yang diubah, atau akun lama yang cocok saat member baru
    public function orangTuaLama(): ?User
    {
        if ($member = $this->route('member')) {
            return $member->orangTua;
        }

        return User::where('email', $this->input('orang_tua.email'))->orWhere('no_hp', $this->input('orang_tua.no_hp'))->first();
    }

    public function rules(): array
    {
        $ortuId = $this->route('member')?->orang_tua_id;
        // Member baru: email/No. HP yang sudah terdaftar boleh, karena akunnya dipakai ulang (dicek di after())
        $unik = fn (string $kolom) => $this->route('member') ? [Rule::unique('users', $kolom)->ignore($ortuId)] : [];

        return [
            'nama' => ['required', 'string', 'max:100'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'sekolah' => ['nullable', 'string', 'max:100'],
            'kelompok_usia_id' => ['required', 'integer', 'exists:kelompok_usia,id'],
            'hubungan_orang_tua' => ['required', Rule::in(PendaftaranController::HUBUNGAN)],
            'status' => ['sometimes', 'in:aktif,nonaktif'],
            'tanggal_masuk' => ['sometimes', 'date'],
            'orang_tua' => ['required', 'array'],
            'orang_tua.nama' => ['required', 'string', 'max:100'],
            'orang_tua.email' => ['required', 'email', 'max:100', ...$unik('email')],
            'orang_tua.no_hp' => ['required', 'regex:/^08\d{8,11}$/', ...$unik('no_hp')],
            'orang_tua.alamat' => ['nullable', 'string', 'max:255'],
            'orang_tua.password' => ['sometimes', 'nullable', 'string', Password::min(8)],
        ];
    }

    public function after(): array
    {
        return [function (Validator $v) {
            if ($this->route('member') || $v->errors()->isNotEmpty()) {
                return;
            }
            $ada = $this->orangTuaLama();
            if ($ada && ! $ada->isOrangTua()) {
                $v->errors()->add('orang_tua.email', 'Email atau No. HP ini milik akun coach/Admin, tidak bisa dipakai sebagai orang tua.');
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'orang_tua.nama' => 'nama orang tua', 'orang_tua.email' => 'email orang tua',
            'orang_tua.no_hp' => 'No. WhatsApp orang tua', 'orang_tua.alamat' => 'alamat orang tua',
        ];
    }

    public function messages(): array
    {
        return ['orang_tua.no_hp.regex' => 'No. WhatsApp diawali 08 dan berisi 10–13 angka.'] + parent::messages();
    }
}
