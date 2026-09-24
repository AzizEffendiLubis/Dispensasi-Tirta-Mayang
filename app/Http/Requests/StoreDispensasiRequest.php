<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDispensasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()?->isAdminDepartemen() ?? false;
    }

    public function rules(): array
    {
        $unit = Auth::user()->unitOrganisasi;
        $unitIds = $unit ? $unit->selfAndDescendantIds() : [];

        return [
            'pegawai_id' => [
                'required',
                Rule::exists('pegawais', 'id')->whereIn('unit_organisasi_id', $unitIds)->where('status', 'aktif'),
            ],
            'tanggal_dispensasi' => ['required', 'date'],
            'waktu_dispensasi'   => ['required', 'array', 'min:1'],
            'waktu_dispensasi.*' => [Rule::in(['T', 'TBO', 'TBI', 'CP'])],
            'keterangan'         => ['required', 'array'],
            'keterangan.*'       => ['nullable', 'string', 'max:1000'],
            'bukti_pendukung'    => ['nullable', 'array'],
            'bukti_pendukung.*'  => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'pegawai_id.exists'         => 'Pegawai tidak ditemukan atau bukan bagian dari unit Anda.',
            'waktu_dispensasi.required' => 'Pilih minimal satu waktu dispensasi.',
            'waktu_dispensasi.min'      => 'Pilih minimal satu waktu dispensasi.',
            'keterangan.required'       => 'Isi keterangan untuk waktu yang dicentang.',
            'bukti_pendukung.*.mimes'   => 'Bukti pendukung harus berupa file PDF, JPG, atau PNG.',
            'bukti_pendukung.*.max'     => 'Ukuran bukti pendukung maksimal 2MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $waktuDipilih = (array) $this->input('waktu_dispensasi', []);
            $keterangan = (array) $this->input('keterangan', []);

            foreach ($waktuDipilih as $waktu) {
                if (trim((string) ($keterangan[$waktu] ?? '')) === '') {
                    $validator->errors()->add(
                        "keterangan.$waktu",
                        "Keterangan untuk waktu {$waktu} wajib diisi."
                    );
                }
            }
        });
    }
}