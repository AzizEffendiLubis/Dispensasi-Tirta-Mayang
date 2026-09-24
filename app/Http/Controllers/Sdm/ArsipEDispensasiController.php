<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\UnitOrganisasi;
use App\Support\Concerns\MenyusunDataSuratEDispensasi;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ArsipEDispensasiController extends Controller
{
    use MenyusunDataSuratEDispensasi;

    private const NAMA_BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $unitOrganisasiId = $request->input('unit_organisasi_id');
        $kataKunci = $request->input('cari');

        $query = Dispensasi::query()
            ->whereNotNull('nomor_surat_dispensasi')
            ->with(['pegawai', 'unitOrganisasi.parent.parent', 'diprosesOleh', 'dicetakOleh']);

        if ($tahun) {
            $query->whereYear('tanggal_surat_dispensasi', $tahun);
        }
        if ($bulan) {
            $query->whereMonth('tanggal_surat_dispensasi', $bulan);
        }
        if ($unitOrganisasiId) {
            $unit = UnitOrganisasi::find($unitOrganisasiId);
            $unitIds = $unit ? $unit->selfAndDescendantIds() : [(int) $unitOrganisasiId];
            $query->whereIn('unit_organisasi_id', $unitIds);
        }
        if ($kataKunci) {
            $query->where(function ($q) use ($kataKunci) {
                $q->where('nomor_surat_dispensasi', 'like', "%{$kataKunci}%")
                  ->orWhereHas('pegawai', fn ($qq) => $qq->where('nama_pegawai', 'like', "%{$kataKunci}%"));
            });
        }

        $semuaBaris = $query->orderByDesc('tanggal_surat_dispensasi')->get();

        $suratTerkelompok = $semuaBaris
            ->groupBy('nomor_surat_dispensasi')
            ->map(function ($baris, $nomorSurat) {
                $acuan = $baris->first();
                return (object) [
                    'nomor_surat'        => $nomorSurat,
                    'tanggal_surat'      => $acuan->tanggal_surat_dispensasi,
                    'tanggal_dispensasi' => $acuan->tanggal_dispensasi,
                    'unit'               => $this->labelUnitTampil($acuan->unitOrganisasi),
                    'penyetuju'          => $acuan->diprosesOleh,
                    'dicetak_oleh'       => $acuan->dicetakOleh,
                    'dicetak_pada'       => $acuan->dicetak_pada,
                    'jumlah_pegawai'     => $baris->pluck('pegawai_id')->unique()->count(),
                    'baris_jangkar_id'   => $acuan->id,
                    'daftar_nama'        => $baris->pluck('pegawai.nama_pegawai')->unique()->implode(', '),
                ];
            })
            ->values()
            ->sortByDesc('tanggal_surat')
            ->values();

        $perPage = 15;
        $halaman = $request->input('page', 1);
        $arsip = new LengthAwarePaginator(
            $suratTerkelompok->forPage($halaman, $perPage)->values(),
            $suratTerkelompok->count(),
            $perPage,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('sdm.arsip-e-dispensasi.index', [
            'arsip'              => $arsip,
            'tahun'              => $tahun,
            'bulan'              => $bulan,
            'unitOrganisasiId'   => $unitOrganisasiId,
            'cari'               => $kataKunci,
            'tahunTersedia'      => $this->getTahunTersedia(),
            'namaBulan'          => self::NAMA_BULAN,
            'unitOrganisasiList' => $this->unitOrganisasiUntukFilter(),
        ]);
    }

    public function show(Dispensasi $dispensasi)
    {
        abort_unless($dispensasi->isSudahDicetak(), 404, 'Surat e-dispensasi belum diterbitkan untuk pengajuan ini.');

        $data = $this->siapkanDataSuratEDispensasi($dispensasi);

        return view('sdm.arsip-e-dispensasi.show', $data);
    }

    private function labelUnitTampil(?UnitOrganisasi $unit): ?UnitOrganisasi
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

    private function unitOrganisasiUntukFilter()
    {
        $departemen = UnitOrganisasi::active()->tingkat('departemen')->get();

        $divisiTanpaDepartemen = UnitOrganisasi::active()
            ->tingkat('divisi')
            ->whereDoesntHave('children', function ($q) {
                $q->where('is_active', true)->where('tingkat', 'departemen');
            })
            ->get();

        return $departemen
            ->merge($divisiTanpaDepartemen)
            ->sortBy('nama')
            ->values();
    }

    private function getTahunTersedia(): array
    {
        $tahun = Dispensasi::whereNotNull('tanggal_surat_dispensasi')
            ->selectRaw('DISTINCT YEAR(tanggal_surat_dispensasi) as tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn ($t) => (int) $t)
            ->toArray();
        $tahunSekarang = now()->year;
        if (! in_array($tahunSekarang, $tahun, true)) {
            $tahun[] = $tahunSekarang;
            rsort($tahun);
        }
        return $tahun;
    }
}