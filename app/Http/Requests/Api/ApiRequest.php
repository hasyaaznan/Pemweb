<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

// Dasar semua validasi API.
// PUT  = ganti seluruh data, semua kolom wajib dikirim.
// PATCH = ubah sebagian, kolom yang tidak dikirim diisi dari data lama lalu divalidasi dengan aturan yang sama.
// Hak akses per peran dicek di middleware rute, jadi authorize() selalu true.
abstract class ApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Data yang sedang diubah, dipakai untuk mengisi kolom yang tidak dikirim saat PATCH. */
    protected function dataLama(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        if ($this->isMethod('PATCH')) {
            $this->replace(array_replace_recursive($this->dataLama(), $this->all()));
        }
        $this->rapikan();
    }

    /** Merapikan masukan sebelum divalidasi, misalnya format No. HP. */
    protected function rapikan(): void {}

    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_without' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa angka bulat.',
            'boolean' => ':attribute harus true atau false.',
            'array' => ':attribute harus berupa daftar (array).',
            'email' => 'Format :attribute belum benar.',
            'date' => ':attribute harus tanggal dengan format YYYY-MM-DD.',
            'date_format' => ':attribute harus berformat :format.',
            'before' => ':attribute harus sebelum :date.',
            'after' => ':attribute harus setelah :date.',
            'in' => ':attribute tidak valid. Pilihan: :values.',
            'exists' => ':attribute tidak ditemukan.',
            'unique' => ':attribute sudah dipakai.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.numeric' => ':attribute maksimal :max.',
            'min.string' => ':attribute minimal :min karakter.',
            'min.numeric' => ':attribute minimal :min.',
            'between.numeric' => ':attribute harus antara :min dan :max.',
            'gte.numeric' => ':attribute tidak boleh lebih kecil dari :value.',
            'regex' => 'Format :attribute belum benar.',
        ];
    }
}
