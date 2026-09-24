<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use App\Models\UnitOrganisasi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    private const KODE_UNIT_ADMIN_DEPARTEMEN = [
        'SPI', 'SEK', 'IT', 'PGD', 'SDM',
        'BSN1', 'BSN2', 'KEU', 'PEL', 'PRD', 'DIST', 'PWS', 'REN',
    ];

    private const KODE_UNIT_MANAJER = ['IT', 'PGD', 'SDM', 'BSN1', 'BSN2', 'KEU', 'PEL'];

    private const KODE_DIVISI_SENIOR_MANAJER = ['DIV-PRODIST', 'DIV-ASET'];

    public function run(): void
    {
        DB::transaction(function () {
            $defaultPassword = Hash::make('password123');

            // 1. Admin SDM 
            User::create([
                'name'                  => 'Admin SDM',
                'email'                 => 'adminsdm@tirtamayang.co.id',
                'password'              => $defaultPassword,
                'role'                  => 'admin_sdm',
                'jabatan_id'            => null,
                'unit_organisasi_id'    => null,
                'is_active'             => true,
                'must_change_password'  => true,
            ]);

            // 2. Admin Departemen 
            UnitOrganisasi::whereIn('kode', self::KODE_UNIT_ADMIN_DEPARTEMEN)
                ->get()
                ->each(function (UnitOrganisasi $unit) use ($defaultPassword) {
                    User::create([
                        'name'                  => "Admin Departemen {$unit->nama}",
                        'email'                 => 'admindept.' . $this->slugKodeUnit($unit) . '@tirtamayang.co.id',
                        'password'              => $defaultPassword,
                        'role'                  => 'admin_departemen',
                        'jabatan_id'            => null,
                        'unit_organisasi_id'    => $unit->id,
                        'is_active'             => true,
                        'must_change_password'  => true,
                    ]);
                });

            $jabatanManajer = Jabatan::where('kode', 'manajer')->firstOrFail();
            $jabatanSeniorManajer = Jabatan::where('kode', 'senior_manajer')->firstOrFail();
            $jabatanSekretarisPerusahaan = Jabatan::where('kode', 'sekretaris_perusahaan')->firstOrFail();
            $jabatanKepalaSpi = Jabatan::where('kode', 'kepala_spi')->firstOrFail();
            $jabatanDirekturTeknik = Jabatan::where('kode', 'direktur_teknik')->firstOrFail();
            $jabatanDirekturAdminKeu = Jabatan::where('kode', 'direktur_administrasi_keuangan')->firstOrFail();
            $jabatanDirekturUtama = Jabatan::where('kode', 'direktur_utama')->firstOrFail();

            // 3. Manajer 
            UnitOrganisasi::whereIn('kode', self::KODE_UNIT_MANAJER)
                ->get()
                ->each(function (UnitOrganisasi $unit) use ($defaultPassword, $jabatanManajer) {
                    $this->buatApprover(
                        "Manajer {$unit->nama}",
                        'manajer.' . $this->slugKodeUnit($unit),
                        $defaultPassword,
                        $jabatanManajer->id,
                        $unit->id
                    );
                });

            // 4. Senior Manajer 
            UnitOrganisasi::whereIn('kode', self::KODE_DIVISI_SENIOR_MANAJER)
                ->get()
                ->each(function (UnitOrganisasi $unit) use ($defaultPassword, $jabatanSeniorManajer) {
                    $this->buatApprover(
                        "Senior Manajer {$unit->nama}",
                        'seniormanajer.' . $this->slugKodeUnit($unit),
                        $defaultPassword,
                        $jabatanSeniorManajer->id,
                        $unit->id
                    );
                });

            // 5. Sekretaris Perusahaan 
            $sek = UnitOrganisasi::where('kode', 'SEK')->first();
            if ($sek) {
                $this->buatApprover(
                    'Sekretaris Perusahaan',
                    'sekper',
                    $defaultPassword,
                    $jabatanSekretarisPerusahaan->id,
                    $sek->id
                );
            }

            // 6. Kepala SPI
            $spi = UnitOrganisasi::where('kode', 'SPI')->first();
            if ($spi) {
                $this->buatApprover(
                    'Kepala SPI',
                    'kepala.spi',
                    $defaultPassword,
                    $jabatanKepalaSpi->id,
                    $spi->id
                );
            }

            // 7. Direksi — tidak terikat unit 
            $this->buatApprover('Direktur Teknik', 'direktur.teknik', $defaultPassword, $jabatanDirekturTeknik->id, null);
            $this->buatApprover('Direktur Administrasi & Keuangan', 'direktur.adminkeu', $defaultPassword, $jabatanDirekturAdminKeu->id, null);
            $this->buatApprover('Direktur Utama', 'direktur.utama', $defaultPassword, $jabatanDirekturUtama->id, null);
        });
    }

    private function slugKodeUnit(UnitOrganisasi $unit): string
    {
        return strtolower(str_replace('DIV-', '', $unit->kode));
    }

    private function buatApprover(string $nama, string $slugEmail, string $passwordHash, int $jabatanId, ?int $unitOrganisasiId): void
    {
        User::create([
            'name'                  => $nama,
            'email'                 => "{$slugEmail}@tirtamayang.co.id",
            'password'              => $passwordHash,
            'role'                  => 'approver',
            'jabatan_id'            => $jabatanId,
            'unit_organisasi_id'    => $unitOrganisasiId,
            'is_active'             => true,
            'must_change_password'  => true,
        ]);
    }
}