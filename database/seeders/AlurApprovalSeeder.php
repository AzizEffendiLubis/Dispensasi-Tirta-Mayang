<?php

namespace Database\Seeders;

use App\Models\AlurApproval;
use App\Models\Jabatan;
use App\Models\UnitOrganisasi;
use Illuminate\Database\Seeder;

class AlurApprovalSeeder extends Seeder
{
    public function run(): void
    {
        $jabatan = Jabatan::pluck('id', 'kode');
        $unit = UnitOrganisasi::pluck('id', 'kode');

        foreach (['IT', 'PGD', 'SDM', 'BSN1', 'BSN2', 'KEU', 'PEL'] as $kodeUnit) {
            $this->aturan($unit[$kodeUnit], $jabatan['staf'], $jabatan['manajer']);
        }

        foreach (['IT', 'PGD'] as $kodeUnit) {
            $this->aturan($unit[$kodeUnit], $jabatan['manajer'], $jabatan['direktur_utama']);
        }

        foreach (['SDM', 'BSN1', 'BSN2', 'KEU', 'PEL'] as $kodeUnit) {
            $this->aturan($unit[$kodeUnit], $jabatan['manajer'], $jabatan['direktur_administrasi_keuangan']);
        }

        foreach (['DIV-PRODIST', 'DIV-ASET'] as $kodeDivisi) {
            $this->aturan($unit[$kodeDivisi], $jabatan['staf'], $jabatan['senior_manajer']);
            $this->aturan($unit[$kodeDivisi], $jabatan['senior_manajer'], $jabatan['direktur_teknik']);
        }

        if (isset($unit['SEK'])) {
            $this->aturan($unit['SEK'], $jabatan['staf'], $jabatan['asisten_bidang']);
            $this->aturan($unit['SEK'], $jabatan['asisten_bidang'], $jabatan['sekretaris_perusahaan']);
            $this->aturan($unit['SEK'], $jabatan['sekretaris_perusahaan'], $jabatan['direktur_utama']);
        }

        if (isset($unit['SPI'])) {
            $this->aturan($unit['SPI'], $jabatan['staf'], $jabatan['kepala_spi']);
            $this->aturan($unit['SPI'], $jabatan['sekretaris_spi'], $jabatan['kepala_spi']);
            $this->aturan($unit['SPI'], $jabatan['kepala_spi'], $jabatan['direktur_utama']);
        }
    }

    private function aturan(int $unitOrganisasiId, int $jabatanPengajuId, int $jabatanApproverId, int $urutan = 1): void
    {
        AlurApproval::updateOrCreate(
            [
                'unit_organisasi_id' => $unitOrganisasiId,
                'jabatan_pengaju_id' => $jabatanPengajuId,
                'urutan'             => $urutan,
            ],
            ['jabatan_approver_id' => $jabatanApproverId]
        );
    }
}