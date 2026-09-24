<?php

namespace App\Http\Controllers;

use App\Models\Dispensasi;

class VerifikasiSuratController extends Controller
{
    private const URUTAN_WAKTU = ['T', 'TBO', 'TBI', 'CP'];

    public function show(string $token)
    {
        $dispensasi = Dispensasi::where('token_verifikasi', $token)
            ->with(['pegawai', 'unitOrganisasi.parent', 'dicetakOleh', 'diprosesOleh', 'ditujukanKepada'])
            ->first();

        if (! $dispensasi) {
            return view('verifikasi.tidak-ditemukan');
        }

        $kelompokDispensasi = Dispensasi::query()
            ->satuSurat($dispensasi->nomor_surat_dispensasi)
            ->with('pegawai')
            ->orderBy('pegawai_id')
            ->orderByRaw("FIELD(waktu_dispensasi, 'T','TBO','TBI','CP')")
            ->get();

        $barisPegawai = $kelompokDispensasi
            ->groupBy('pegawai_id')
            ->map(function ($barisWaktu) {
                $acuan = $barisWaktu->first();
                return (object) [
                    'pegawai' => $acuan->pegawai,
                    'waktu'   => $barisWaktu->pluck('waktu_dispensasi')
                        ->sortBy(fn ($w) => array_search($w, self::URUTAN_WAKTU))
                        ->values(),
                ];
            })
            ->values();

        $waktuList = $kelompokDispensasi->pluck('waktu_dispensasi')->unique()
            ->sortBy(fn ($w) => array_search($w, self::URUTAN_WAKTU))
            ->values();

        $adaDivisi = $dispensasi->unitOrganisasi?->parent !== null;

        return view('verifikasi.surat', compact('dispensasi', 'barisPegawai', 'waktuList', 'adaDivisi'));
    }
}