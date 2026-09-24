<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pegawaiId = $this->route('pegawai')?->id;

        return [
            'nik'                => ['required', 'string', 'max:20', Rule::unique('pegawais', 'nik')->ignore($pegawaiId)],
            'nama_pegawai'       => ['required', 'string', 'max:100'],
            'jabatan_id'         => ['required', 'exists:jabatans,id'],
            'unit_organisasi_id' => ['required', 'exists:unit_organisasis,id'],
            'no_telepon'         => ['nullable', 'string', 'max:20'],
            'email'              => ['nullable', 'email', 'max:100'],
            'status'             => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }
}