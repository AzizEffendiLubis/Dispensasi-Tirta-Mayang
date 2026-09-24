<?php

namespace App\Http\Requests;

use App\Models\UnitOrganisasi;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUnitOrganisasiRequest extends FormRequest
{
    private const URUTAN_TINGKAT = [
        'divisi'        => 1,
        'departemen'    => 2,
        'subdepartemen' => 3,
    ];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode' => $this->filled('kode') ? trim($this->input('kode')) : null,
        ]);
    }

    private function unitSaatIni(): ?UnitOrganisasi
    {
        return collect($this->route()?->parameters() ?? [])
            ->first(fn ($param) => $param instanceof UnitOrganisasi);
    }

    public function rules(): array
    {
        $unitId = $this->unitSaatIni()?->id;

        return [
            'tingkat' => ['required', Rule::in(['divisi', 'departemen', 'subdepartemen'])],

            'parent_id' => ['nullable', 'exists:unit_organisasis,id', Rule::notIn([$unitId])],

            'kode' => ['nullable', 'string', 'max:20', Rule::unique('unit_organisasis', 'kode')->ignore($unitId)],
            'nama' => ['required', 'string', 'max:150'],
            'urutan' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $unit = $this->unitSaatIni();
            $parentId = $this->input('parent_id');
            $tingkatUnit = $this->input('tingkat');

            if ($unit && $parentId) {
                $idTerlarang = $unit->selfAndDescendantIds();

                if (in_array((int) $parentId, $idTerlarang, true)) {
                    $validator->errors()->add(
                        'parent_id',
                        'Induk unit tidak boleh unit ini sendiri atau salah satu unit di bawahnya (akan membuat struktur melingkar).'
                    );

                    return;
                }
            }

            if ($tingkatUnit === 'divisi' && $parentId) {
                $validator->errors()->add(
                    'parent_id',
                    'Divisi berada di tingkat paling atas dan tidak boleh punya induk unit.'
                );

                return;
            }

            if ($parentId) {
                $parent = UnitOrganisasi::find($parentId);

                if ($parent) {
                    $urutanParent = self::URUTAN_TINGKAT[$parent->tingkat] ?? 99;
                    $urutanUnit = self::URUTAN_TINGKAT[$tingkatUnit] ?? 99;

                    if ($urutanParent >= $urutanUnit) {
                        $validator->errors()->add(
                            'parent_id',
                            'Induk unit harus berada di tingkat yang lebih tinggi — Sub Departemen harus di bawah Departemen atau Divisi, dan Departemen harus di bawah Divisi.'
                        );
                    }
                }
            }
        });
    }
}