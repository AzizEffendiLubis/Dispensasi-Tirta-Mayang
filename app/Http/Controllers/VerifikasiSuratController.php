<?php

namespace App\Http\Controllers;

use App\Models\Dispensasi;
use App\Support\Concerns\MenyusunDataSuratEDispensasi;

class VerifikasiSuratController extends Controller
{
    use MenyusunDataSuratEDispensasi;

    private const URUTAN_WAKTU = ['T', 'TBO', 'TBI', 'CP'];

    public function show(string $token)
    {
        $dispensasi = Dispensasi::where('token_verifikasi', $token)
            ->with(['pegawai', 'unitOrganisasi.parent', 'dicetakOleh', 'diprosesOleh', 'ditujukanKepada'])
            ->first();

        if (! $dispensasi) {
            return view('verifikasi.tidak-ditemukan');
        }

        $data = $this->siapkanDataSuratEDispensasi($dispensasi);

        $barisPegawai = $data['barisHalamanUtama']->concat($data['barisLampiran'])->values();

        $waktuList = $barisPegawai
            ->flatMap(fn ($baris) => $baris->waktu)
            ->unique()
            ->sortBy(fn ($w) => array_search($w, self::URUTAN_WAKTU))
            ->values();

        return view('verifikasi.surat', [
            'dispensasi'     => $dispensasi,
            'barisPegawai'   => $barisPegawai,
            'waktuList'      => $waktuList,
            'unitBarisUtama' => $data['unitBarisUtama'],
        ]);
    }
}