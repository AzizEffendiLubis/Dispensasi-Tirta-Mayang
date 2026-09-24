<?php

namespace Database\Seeders;

use App\Models\UnitOrganisasi;
use Illuminate\Database\Seeder;

class UnitOrganisasiSeeder extends Seeder
{
    public function run(): void
    {
        $divisiDefinisi = [
            'Divisi Perencanaan & Pengelolaan Aset'    => 'DIV-ASET',
            'Divisi Produksi & Distribusi'              => 'DIV-PRODIST',
            'Divisi Keuangan & Pengelolaan Pelanggan'  => 'DIV-KEU',
            'Divisi Bisnis'                              => 'DIV-BISNIS',
            'Satuan Pengawas Intern'                    => 'SPI',
            'Sekretaris Perusahaan'                     => 'SEK',
        ];

        $divisiIds = [];
        $urutan = 1;
        foreach ($divisiDefinisi as $nama => $kode) {
            $divisiIds[$nama] = UnitOrganisasi::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'tingkat' => 'divisi', 'parent_id' => null, 'is_active' => true, 'urutan' => $urutan++]
            )->id;
        }

        $departemenDiBawahDivisi = [
            'Bisnis Wilayah I'                             => ['kode' => 'BSN1', 'divisi' => 'Divisi Bisnis'],
            'Bisnis Wilayah II'                            => ['kode' => 'BSN2', 'divisi' => 'Divisi Bisnis'],
            'Keuangan'                                      => ['kode' => 'KEU', 'divisi' => 'Divisi Keuangan & Pengelolaan Pelanggan'],
            'Pengelolaan Pelanggan'                        => ['kode' => 'PEL', 'divisi' => 'Divisi Keuangan & Pengelolaan Pelanggan'],
            'Produksi'                                       => ['kode' => 'PRD', 'divisi' => 'Divisi Produksi & Distribusi'],
            'Distribusi'                                     => ['kode' => 'DIST', 'divisi' => 'Divisi Produksi & Distribusi'],
            'Pengawasan Teknik & Pemeliharaan Bangunan'    => ['kode' => 'PWS', 'divisi' => 'Divisi Perencanaan & Pengelolaan Aset'],
            'Perencanaan dan Database Aset'                => ['kode' => 'REN', 'divisi' => 'Divisi Perencanaan & Pengelolaan Aset'],
        ];

        $departemenMandiri = [
            'IT'        => 'IT',
            'Pengadaan' => 'PGD',
            'SDM'       => 'SDM',
        ];

        $departemenIds = [];
        $urutan = 1;
        foreach ($departemenDiBawahDivisi as $nama => $detail) {
            $departemenIds[$nama] = UnitOrganisasi::updateOrCreate(
                ['kode' => $detail['kode']],
                [
                    'nama'      => $nama,
                    'tingkat'   => 'departemen',
                    'parent_id' => $divisiIds[$detail['divisi']],
                    'is_active' => true,
                    'urutan'    => $urutan++,
                ]
            )->id;
        }

        foreach ($departemenMandiri as $nama => $kode) {
            $departemenIds[$nama] = UnitOrganisasi::updateOrCreate(
                ['kode' => $kode],
                [
                    'nama'      => $nama,
                    'tingkat'   => 'departemen',
                    'parent_id' => null,
                    'is_active' => true,
                    'urutan'    => $urutan++,
                ]
            )->id;
        }

        $subdepartemenDefinisi = [
            'Pemasaran Wilayah I'                  => ['kode' => 'BSN1-PMR', 'departemen' => 'Bisnis Wilayah I'],
            'Sambung Baru Wilayah I'                => ['kode' => 'BSN1-SB', 'departemen' => 'Bisnis Wilayah I'],
            'Pemasaran Wilayah II'                  => ['kode' => 'BSN2-PMR', 'departemen' => 'Bisnis Wilayah II'],
            'Sambung Baru Wilayah II'                => ['kode' => 'BSN2-SB', 'departemen' => 'Bisnis Wilayah II'],
            'Akuntansi'                               => ['kode' => 'KEU-AKT', 'departemen' => 'Keuangan'],
            'Anggaran'                                 => ['kode' => 'KEU-ANG', 'departemen' => 'Keuangan'],
            'Kas & Perpajakan'                        => ['kode' => 'KEU-KAS', 'departemen' => 'Keuangan'],
            'Meter Air'                                => ['kode' => 'PEL-MTR', 'departemen' => 'Pengelolaan Pelanggan'],
            'Tunggakan Pelanggan'                      => ['kode' => 'PEL-TGK', 'departemen' => 'Pengelolaan Pelanggan'],
            'Baca Meter & Rekening'                    => ['kode' => 'PEL-BCR', 'departemen' => 'Pengelolaan Pelanggan'],
            'Laboratorium'                             => ['kode' => 'PRD-LAB', 'departemen' => 'Produksi'],
            'Pengolahan Air I'                          => ['kode' => 'PRD-OLA1', 'departemen' => 'Produksi'],
            'Pengolahan Air II'                         => ['kode' => 'PRD-OLA2', 'departemen' => 'Produksi'],
            'Pemeliharaan Aset Produksi'               => ['kode' => 'PRD-AST', 'departemen' => 'Produksi'],
            'Pengaliran Wilayah I'                      => ['kode' => 'DIST-AL1', 'departemen' => 'Distribusi'],
            'Pengaliran Wilayah II'                     => ['kode' => 'DIST-AL2', 'departemen' => 'Distribusi'],
            'Pemeliharaan & Perbaikan Perpipaan'        => ['kode' => 'DIST-PIPA', 'departemen' => 'Distribusi'],
            'Pengawasan Teknik & Perizinan'             => ['kode' => 'PWS-TEK', 'departemen' => 'Pengawasan Teknik & Pemeliharaan Bangunan'],
            'Pemeliharaan Bangunan & K3'                => ['kode' => 'PWS-K3', 'departemen' => 'Pengawasan Teknik & Pemeliharaan Bangunan'],
            'Perencanaan Aset'                          => ['kode' => 'REN-AST', 'departemen' => 'Perencanaan dan Database Aset'],
            'Database Aset & GIS'                       => ['kode' => 'REN-GIS', 'departemen' => 'Perencanaan dan Database Aset'],
            'Pergudangan'                                => ['kode' => 'REN-GDG', 'departemen' => 'Perencanaan dan Database Aset'],

            'Aplikasi & Pengamanan IT'   => ['kode' => 'IT-APP', 'departemen' => 'IT'],
            'Infrastruktur IT'           => ['kode' => 'IT-INF', 'departemen' => 'IT'],
            'Administrasi Pengadaan'     => ['kode' => 'PGD-ADM', 'departemen' => 'Pengadaan'],
            'Personalia & Payroll'       => ['kode' => 'SDM-PER', 'departemen' => 'SDM'],
            'Pelatihan & Pengembangan'   => ['kode' => 'SDM-DIK', 'departemen' => 'SDM'],

        ];

        $urutan = 1;
        foreach ($subdepartemenDefinisi as $nama => $detail) {
            UnitOrganisasi::updateOrCreate(
                ['kode' => $detail['kode']],
                [
                    'nama'      => $nama,
                    'tingkat'   => 'subdepartemen',
                    'parent_id' => $departemenIds[$detail['departemen']],
                    'is_active' => true,
                    'urutan'    => $urutan++,
                ]
            );
        }
    }
}