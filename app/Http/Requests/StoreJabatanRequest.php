<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJabatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $jabatanId = $this->route('jabatan')?->id;

        return [
            'kode' => [
                'required', 'string', 'max:40', 'alpha_dash',
                Rule::unique('jabatans', 'kode')->ignore($jabatanId),
            ],
            'nama' => ['required', 'string', 'max:100'],
            'level_urutan' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode.alpha_dash' => 'Kode hanya boleh huruf, angka, garis bawah (_), dan strip (-), tanpa spasi. Mis. "asisten_direktur".',
        ];
    }
}