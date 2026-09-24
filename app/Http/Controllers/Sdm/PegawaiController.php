<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePegawaiRequest;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $unitOrganisasiId = $request->input('unit_organisasi_id');
        $status = $request->input('status');

        $query = Pegawai::with(['jabatan', 'unitOrganisasi']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'LIKE', "%{$search}%")
                  ->orWhere('nama_pegawai', 'LIKE', "%{$search}%");
            });
        }

        if ($unitOrganisasiId) {
            $unitTerpilih = UnitOrganisasi::find($unitOrganisasiId);
            $unitIds = $unitTerpilih ? $unitTerpilih->selfAndDescendantIds() : [(int) $unitOrganisasiId];
            $query->whereIn('unit_organisasi_id', $unitIds);
        }

        if ($status && in_array($status, ['aktif', 'nonaktif'], true)) {
            $query->where('status', $status);
        }

        $pegawaiPerUnit = $query->orderBy('nama_pegawai')->get()->groupBy('unit_organisasi_id');

        $semuaUnit = UnitOrganisasi::active()->orderBy('urutan')->orderBy('nama')->get();
        $adaFilterAktif = (bool) ($search || $unitOrganisasiId || $status);

        $pohonUnit = $this->susunPohonUnit($semuaUnit, null, $pegawaiPerUnit, $adaFilterAktif);

        return view('sdm.pegawai.index', [
            'pohonUnit'        => $pohonUnit,
            'unitFlatIndent'   => $this->unitFlatDenganIndentasi($semuaUnit),
            'search'           => $search,
            'unitOrganisasiId' => $unitOrganisasiId,
            'status'           => $status,
        ]);
    }

    private function susunPohonUnit($semuaUnit, ?int $parentId, $pegawaiPerUnit, bool $adaFilterAktif): array
    {
        $hasil = [];

        foreach ($semuaUnit->where('parent_id', $parentId) as $unit) {
            $pegawaiUnitIni = $pegawaiPerUnit->get($unit->id, collect());
            $children = $this->susunPohonUnit($semuaUnit, $unit->id, $pegawaiPerUnit, $adaFilterAktif);
            $totalTermasukAnak = $pegawaiUnitIni->count() + collect($children)->sum('total_pegawai');

            if ($adaFilterAktif && $totalTermasukAnak === 0) {
                continue;
            }

            $hasil[] = [
                'unit'          => $unit,
                'pegawai'       => $pegawaiUnitIni,
                'children'      => $children,
                'total_pegawai' => $totalTermasukAnak,
            ];
        }

        return $hasil;
    }

    private function unitFlatDenganIndentasi($semuaUnit, ?int $parentId = null, int $depth = 0): array
    {
        $hasil = [];

        foreach ($semuaUnit->where('parent_id', $parentId) as $unit) {
            $hasil[] = ['unit' => $unit, 'depth' => $depth];
            $hasil = array_merge($hasil, $this->unitFlatDenganIndentasi($semuaUnit, $unit->id, $depth + 1));
        }

        return $hasil;
    }

    public function create()
    {
        return view('sdm.pegawai.create', [
            'unitOrganisasis' => UnitOrganisasi::active()->orderBy('tingkat')->orderBy('nama')->get(),
            'jabatans'        => Jabatan::urut()->get(),
        ]);
    }

    public function store(StorePegawaiRequest $request)
    {
        Pegawai::create($request->validated());

        return redirect()->route('sdm.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function edit(Pegawai $pegawai)
    {
        return view('sdm.pegawai.edit', [
            'pegawai'         => $pegawai,
            'unitOrganisasis' => UnitOrganisasi::active()->orderBy('tingkat')->orderBy('nama')->get(),
            'jabatans'        => Jabatan::urut()->get(),
        ]);
    }

    public function update(StorePegawaiRequest $request, Pegawai $pegawai)
    {
        $pegawai->update($request->validated());

        return redirect()->route('sdm.pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai)
    {
        $pegawai->update(['status' => 'nonaktif']);

        return back()->with('success', 'Pegawai berhasil dinonaktifkan.');
    }
}