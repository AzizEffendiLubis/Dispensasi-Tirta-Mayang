<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            ['kode' => 'staf',                           'nama' => 'Staf',                              'level_urutan' => 10],
            ['kode' => 'asisten_manajer',                 'nama' => 'Asisten Manajer',                   'level_urutan' => 20],
            ['kode' => 'asisten_bidang',                  'nama' => 'Asisten Bidang',                    'level_urutan' => 20],
            ['kode' => 'manajer',                         'nama' => 'Manajer',                           'level_urutan' => 30],
            ['kode' => 'sekretaris_spi',                  'nama' => 'Sekretaris SPI',                    'level_urutan' => 30],
            ['kode' => 'senior_manajer',                  'nama' => 'Senior Manajer',                    'level_urutan' => 40],
            ['kode' => 'asisten_direktur',                'nama' => 'Asisten Direktur',                  'level_urutan' => 40],
            ['kode' => 'sekretaris_perusahaan',            'nama' => 'Sekretaris Perusahaan',             'level_urutan' => 40],
            ['kode' => 'kepala_spi',                      'nama' => 'Kepala SPI',                        'level_urutan' => 40],
            ['kode' => 'direktur_teknik',                 'nama' => 'Direktur Teknik',                   'level_urutan' => 50],
            ['kode' => 'direktur_administrasi_keuangan',  'nama' => 'Direktur Administrasi & Keuangan',  'level_urutan' => 50],
            ['kode' => 'direktur_utama',                  'nama' => 'Direktur Utama',                    'level_urutan' => 60],
        ];

        foreach ($daftar as $jabatan) {
            Jabatan::updateOrCreate(['kode' => $jabatan['kode']], $jabatan);
        }
    }
}