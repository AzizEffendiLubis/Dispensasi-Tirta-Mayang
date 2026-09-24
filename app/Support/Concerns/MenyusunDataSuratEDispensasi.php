<?php

namespace App\Support\Concerns;

use App\Models\Dispensasi;
use App\Models\UnitOrganisasi;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

trait MenyusunDataSuratEDispensasi
{
    private const KODE_JABATAN_LEVEL_DIVISI = [
        'senior_manajer', 'asisten_direktur', 'sekretaris_perusahaan', 'kepala_spi',
        'direktur_teknik', 'direktur_administrasi_keuangan', 'direktur_utama',
    ];

    private const MAKS_BARIS_HALAMAN_UTAMA = 12;

    /**
     * @return array{
     *   dispensasi: Dispensasi,
     *   nomorSurat: ?string,
     *   tanggalSurat: mixed,
     *   penyetuju: mixed,
     *   jabatanPenyetuju: string,
     *   jabatanTandaTangan: string,
     *   labelInstansi: string,
     *   jabatanTujuan: string,
     *   unitBarisUtama: ?UnitOrganisasi,
     *   barisHalamanUtama: \Illuminate\Support\Collection,
     *   barisLampiran: \Illuminate\Support\Collection,
     *   adaLampiran: bool,
     *   totalPegawai: int,
     *   qrSvg: string,
     * }
     */
    protected function siapkanDataSuratEDispensasi(Dispensasi $dispensasi): array
    {
        $dispensasi->load(['pegawai.jabatan', 'unitOrganisasi.parent', 'dicetakOleh', 'ditujukanKepada', 'diprosesOleh.jabatan']);

        $kelompokDispensasi = Dispensasi::query()
            ->satuSurat($dispensasi->nomor_surat_dispensasi)
            ->with(['pegawai.jabatan', 'unitOrganisasi'])
            ->orderBy('pegawai_id')
            ->orderByRaw("FIELD(waktu_dispensasi, 'T','TBO','TBI','CP')")
            ->get();

        $barisPegawai = $kelompokDispensasi
            ->groupBy('pegawai_id')
            ->map(function ($barisWaktu) {
                $acuan = $barisWaktu->first();
                return (object) [
                    'pegawai'    => $acuan->pegawai,
                    'unit'       => $acuan->unitOrganisasi,
                    'waktu'      => $barisWaktu->pluck('waktu_dispensasi')->unique()->values()->toArray(),
                    'keterangan' => $barisWaktu->pluck('keterangan')->filter()->unique()->implode('; ') ?: '-',
                ];
            })
            ->values();

        $adaLevelDivisi = $barisPegawai->contains(
            fn ($b) => in_array($b->pegawai->jabatan?->kode, self::KODE_JABATAN_LEVEL_DIVISI, true)
        );

        $unitBarisUtama = $adaLevelDivisi
            ? $this->unitPalingAtas($dispensasi->unitOrganisasi)
            : $this->unitInstansiUntukSurat($dispensasi->unitOrganisasi);

        $barisHalamanUtama = $barisPegawai->take(self::MAKS_BARIS_HALAMAN_UTAMA)->values();
        $barisLampiran = $barisPegawai->slice(self::MAKS_BARIS_HALAMAN_UTAMA)->values();
        $adaLampiran = $barisLampiran->isNotEmpty();

        $penyetuju = $dispensasi->diprosesOleh;
        $unitAdalahSdm = $dispensasi->unitOrganisasi?->kode === 'SDM';
        $kodeJabatanPenyetuju = $penyetuju?->jabatan?->kode;

        $pemohonAdalahManajerSdm = $unitAdalahSdm
            && $barisPegawai->contains(fn ($b) => $b->pegawai->jabatan?->kode === 'manajer');

        if ($pemohonAdalahManajerSdm) {
            $jabatanPenyetuju = 'Manajer SDM';
            $jabatanTujuan = 'Direktur Administrasi dan Keuangan';
        } elseif ($unitAdalahSdm) {
            $jabatanTujuan = 'Direktur Administrasi dan Keuangan';

            if (in_array($kodeJabatanPenyetuju, ['manajer', 'direktur_administrasi_keuangan'], true)) {
                $jabatanPenyetuju = 'Manajer SDM';
            } else {
                $jabatanPenyetuju = $penyetuju?->jabatanLengkap() ?? 'Manajer SDM';
            }
        } else {
            $jabatanPenyetuju = $penyetuju?->jabatanLengkap() ?? '-';
            $jabatanTujuan = 'Manajer SDM';
        }

        $jabatanTandaTangan = $penyetuju?->jabatanLengkap() ?? '-';

        $labelInstansi = $unitAdalahSdm
            ? 'DEPARTEMEN SUMBER DAYA MANUSIA'
            : \Illuminate\Support\Str::upper(($unitBarisUtama?->labelTingkat() ?? '') . ' ' . ($unitBarisUtama?->nama ?? '-'));

        $qrSvg = QrCode::size(140)->generate(
            route('verifikasi.surat', ['token' => $dispensasi->token_verifikasi])
        );

        return [
            'dispensasi'                => $dispensasi,
            'nomorSurat'                => $dispensasi->nomor_surat_dispensasi,
            'tanggalSurat'              => $dispensasi->tanggal_surat_dispensasi,
            'penyetuju'                 => $penyetuju,
            'jabatanPenyetuju'          => $jabatanPenyetuju,
            'jabatanTandaTangan'        => $jabatanTandaTangan,
            'labelInstansi'             => $labelInstansi,
            'jabatanTujuan'             => $jabatanTujuan,
            'unitBarisUtama'            => $unitBarisUtama,
            'barisHalamanUtama'         => $barisHalamanUtama,
            'barisLampiran'             => $barisLampiran,
            'adaLampiran'               => $adaLampiran,
            'totalPegawai'              => $barisPegawai->count(),
            'qrSvg'                     => $qrSvg,
        ];
    }

    private function unitPalingAtas(?UnitOrganisasi $unit): ?UnitOrganisasi
    {
        if (! $unit) {
            return null;
        }

        $rantai = $unit->selfAndAncestors();

        return end($rantai) ?: $unit;
    }

    private function unitInstansiUntukSurat(?UnitOrganisasi $unit): ?UnitOrganisasi
    {
        if (! $unit) {
            return null;
        }

        foreach ($unit->selfAndAncestors() as $unitAcuan) {
            if ($unitAcuan->tingkat === 'departemen') {
                return $unitAcuan;
            }
        }

        foreach ($unit->selfAndAncestors() as $unitAcuan) {
            if ($unitAcuan->tingkat === 'divisi') {
                return $unitAcuan;
            }
        }

        return $unit;
    }
}