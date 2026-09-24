<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Models\AlurApproval;
use App\Models\Dispensasi;
use App\Models\Jabatan;
use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;

class AlurApprovalController extends Controller
{
    public function index()
    {
        $unitOrganisasis = UnitOrganisasi::active()
            ->whereIn('tingkat', ['divisi', 'departemen'])
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        return view('sdm.alur-approval.index', compact('unitOrganisasis'));
    }

    public function edit(UnitOrganisasi $unitOrganisasi)
    {
        $jabatans = Jabatan::urut()->get();

        $aturanPerJabatanPengaju = AlurApproval::untukUnit($unitOrganisasi->id)
            ->where('urutan', 1)
            ->get()
            ->keyBy('jabatan_pengaju_id');

        $warisanPerJabatanPengaju = [];
        foreach ($jabatans as $jabatanPengaju) {
            if ($aturanPerJabatanPengaju->has($jabatanPengaju->id)) {
                continue;
            }

            foreach ($unitOrganisasi->ancestors() as $unitInduk) {
                $aturanInduk = AlurApproval::untukUnit($unitInduk->id)
                    ->untukJabatanPengaju($jabatanPengaju->id)
                    ->urutan(1)
                    ->first();

                if ($aturanInduk) {
                    $warisanPerJabatanPengaju[$jabatanPengaju->id] = [
                        'unit'            => $unitInduk,
                        'jabatanApprover' => $aturanInduk->jabatanApprover,
                    ];
                    break;
                }
            }
        }

        return view('sdm.alur-approval.edit', [
            'unitOrganisasi'           => $unitOrganisasi,
            'jabatans'                 => $jabatans,
            'aturanPerJabatanPengaju'  => $aturanPerJabatanPengaju,
            'warisanPerJabatanPengaju' => $warisanPerJabatanPengaju,
        ]);
    }

    public function update(Request $request, UnitOrganisasi $unitOrganisasi)
    {
        $data = $request->validate([
            'jabatan_pengaju'   => ['required', 'array'],
            'jabatan_pengaju.*' => ['nullable', 'exists:jabatans,id'],
        ]);

        foreach ($data['jabatan_pengaju'] as $jabatanPengajuId => $jabatanApproverId) {
            if (! $jabatanApproverId) {
                AlurApproval::query()
                    ->untukUnit($unitOrganisasi->id)
                    ->untukJabatanPengaju((int) $jabatanPengajuId)
                    ->urutan(1)
                    ->delete();
                continue;
            }

            AlurApproval::updateOrCreate(
                [
                    'unit_organisasi_id' => $unitOrganisasi->id,
                    'jabatan_pengaju_id' => $jabatanPengajuId,
                    'urutan'             => 1,
                ],
                ['jabatan_approver_id' => $jabatanApproverId]
            );
        }

        $jumlahDireassign = $this->reassignApproverPengajuanMenunggu($unitOrganisasi);

        $pesan = "Alur approval untuk {$unitOrganisasi->nama} berhasil disimpan.";
        if ($jumlahDireassign > 0) {
            $pesan .= " {$jumlahDireassign} pengajuan dispensasi yang masih menunggu persetujuan di unit ini (dan turunannya) sudah dihitung ulang approver-nya sesuai aturan terbaru.";
        }

        return redirect()
            ->route('sdm.alur-approval.edit', $unitOrganisasi)
            ->with('success', $pesan);
    }

    private function reassignApproverPengajuanMenunggu(UnitOrganisasi $unit): int
    {
        $unitIds = $unit->selfAndDescendantIds();

        $dispensasiTerdampak = Dispensasi::whereIn('unit_organisasi_id', $unitIds)
            ->where('status_pengajuan', 'menunggu_persetujuan')
            ->with('pegawai.jabatan', 'pegawai.unitOrganisasi')
            ->get();

        $jumlahBerubah = 0;
        foreach ($dispensasiTerdampak as $dispensasi) {
            $approverLamaId = $dispensasi->approver_saat_ini_id;
            $dispensasi->tentukanApproverAwal();

            if ($dispensasi->approver_saat_ini_id !== $approverLamaId) {
                $dispensasi->save();
                $jumlahBerubah++;
            }
        }

        return $jumlahBerubah;
    }
}