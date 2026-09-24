<?php

namespace App\Http\Requests;

use App\Models\Jabatan;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public const ROLE_TERSEDIA = [
        'admin_sdm',
        'admin_departemen',
        'approver',
    ];

    public function rules(): array
    {
        $userId = $this->route('pengguna')?->id;
        $role = $this->input('role');

        return [
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(self::ROLE_TERSEDIA)],
            'is_plt' => ['sometimes', 'boolean'],

            'jabatan_id' => [
                Rule::requiredIf($role === 'approver'),
                'nullable',
                'exists:jabatans,id',
            ],

            'unit_organisasi_id' => [
                Rule::requiredIf($role === 'admin_departemen'),
                'nullable',
                'exists:unit_organisasis,id',
            ],

            'keterangan_tambahan' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $role = $this->input('role');

            if ($role !== 'approver') {
                return;
            }

            $jabatanId = $this->input('jabatan_id');
            $unitOrganisasiId = $this->input('unit_organisasi_id');

            if (! $jabatanId) {
                return;
            }

            $jabatan = Jabatan::find($jabatanId);

            $jabatanBolehTanpaUnit = $jabatan && str_starts_with($jabatan->kode, 'direktur');

            if (! $unitOrganisasiId && ! $jabatanBolehTanpaUnit) {
                $validator->errors()->add(
                    'unit_organisasi_id',
                    'Unit organisasi wajib diisi untuk jabatan ini.'
                );
                return;
            }

            $userId = $this->route('pengguna')?->id;

            $query = User::where('jabatan_id', $jabatanId)
                ->where('is_active', true)
                ->when($userId, fn ($q) => $q->where('id', '!=', $userId));

            if ($unitOrganisasiId) {
                $query->where('unit_organisasi_id', $unitOrganisasiId);
            } else {
                $query->whereNull('unit_organisasi_id');
            }

            if ($query->exists()) {
                $validator->errors()->add(
                    'jabatan_id',
                    'Jabatan ini sudah punya akun aktif di unit yang sama. Nonaktifkan yang lama terlebih dahulu.'
                );
            }
        });
    }
}