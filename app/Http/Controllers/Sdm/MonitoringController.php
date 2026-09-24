<?php

namespace App\Http\Controllers\Sdm;

use App\Exports\DispensasiExport;
use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\UnitOrganisasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringController extends Controller
{
    private const NAMA_BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    private const URUTAN_WAKTU = ['T', 'TBO', 'TBI', 'CP'];

    public function index(Request $request)
    {
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $unitOrganisasiId = $request->input('unit_organisasi_id');
        $status = $request->input('status');
        $urutan = $request->input('urutan') === 'terlama' ? 'terlama' : 'terbaru';
        $arah = $urutan === 'terlama' ? 'asc' : 'desc';

        $semuaBaris = $this->filteredQuery($tahun, $bulan, $unitOrganisasiId, $status)
            ->with(['pegawai', 'unitOrganisasi.parent.parent', 'diprosesOleh'])
            ->orderBy('tanggal_pengajuan', $arah)
            ->orderBy('created_at', $arah)
            ->get();

        $kelompok = $semuaBaris
            ->groupBy(fn ($d) => $d->pegawai_id . '|' . $d->tanggal_dispensasi->format('Y-m-d'))
            ->map(function ($baris) {
                $acuan = $baris->first();
                $statusUnik = $baris->pluck('status_pengajuan')->unique();
                return (object) [
                    'acuan'             => $acuan,
                    'baris'             => $baris->sortBy(
                        fn ($d) => array_search($d->waktu_dispensasi, self::URUTAN_WAKTU)
                    )->values(),
                    'nomor_list'        => $baris->pluck('nomor_dispensasi')->implode(', '),
                    'statusSeragam'     => $statusUnik->count() === 1 ? $statusUnik->first() : null,
                    'tanggal_pengajuan' => $baris->max('tanggal_pengajuan'),
                    'createdAtTerbaru'  => $baris->max('created_at'),
                    'unitTampil'        => $this->labelUnitTampil($acuan->unitOrganisasi),
                ];
            });

        $kelompok = $kelompok->sortBy([
            ['tanggal_pengajuan', $arah],
            ['createdAtTerbaru', $arah],
        ])->values();

        $perPage = 20;
        $halaman = $request->input('page', 1);
        $dispensasis = new LengthAwarePaginator(
            $kelompok->forPage($halaman, $perPage)->values(),
            $kelompok->count(),
            $perPage,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('sdm.monitoring.index', [
            'dispensasis'      => $dispensasis,
            'unitOrganisasis'  => $this->unitOrganisasiUntukFilter(),
            'tahunTersedia'    => $this->tahunTersedia(),
            'namaBulan'        => self::NAMA_BULAN,
            'tahun'            => $tahun,
            'bulan'            => $bulan,
            'unitOrganisasiId' => $unitOrganisasiId,
            'status'           => $status,
            'urutan'           => $urutan,
        ]);
    }

    public function show(Dispensasi $dispensasi)
    {
        $dispensasi->load([
            'pegawai',
            'unitOrganisasi',
            'adminDepartemen',
            'diprosesOleh',
            'dicetakOleh',
            'ditujukanKepada',
        ]);

        $kelompokTampil = Dispensasi::query()
            ->satuKelompokPegawaiTanggal($dispensasi)
            ->orderByRaw("FIELD(waktu_dispensasi, 'T','TBO','TBI','CP')")
            ->get();

        return view('sdm.monitoring.show', compact('dispensasi', 'kelompokTampil'));
    }

    public function exportExcel(Request $request)
    {
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $unitOrganisasiId = $request->input('unit_organisasi_id');

        $namaUnit = $unitOrganisasiId
            ? (UnitOrganisasi::find($unitOrganisasiId)?->nama ?? 'Unit')
            : 'Semua Unit';

        $bagianNamaFile = [Str::slug($namaUnit)];

        if ($bulan && isset(self::NAMA_BULAN[$bulan])) {
            $bagianNamaFile[] = Str::slug(self::NAMA_BULAN[$bulan]);
        }

        if ($tahun) {
            $bagianNamaFile[] = $tahun;
        }

        $namaFile = implode('-', $bagianNamaFile) . '.xlsx';

        return Excel::download(
            new DispensasiExport($tahun, $bulan, $unitOrganisasiId, self::NAMA_BULAN),
            $namaFile
        );
    }

    private function filteredQuery(?string $tahun, ?string $bulan, ?string $unitOrganisasiId, ?string $status): Builder
    {
        $query = Dispensasi::query();

        if ($tahun) {
            $query->whereYear('tanggal_dispensasi', $tahun);
        }
        if ($bulan) {
            $query->whereMonth('tanggal_dispensasi', $bulan);
        }
        if ($unitOrganisasiId) {
            $unit = UnitOrganisasi::find($unitOrganisasiId);
            $unitIds = $unit ? $unit->selfAndDescendantIds() : [(int) $unitOrganisasiId];
            $query->whereIn('unit_organisasi_id', $unitIds);
        }
        if ($status && in_array($status, ['menunggu_persetujuan', 'disetujui', 'ditolak'], true)) {
            $query->where('status_pengajuan', $status);
        }

        return $query;
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

    private function tahunTersedia(): array
    {
        $tahun = Dispensasi::selectRaw('DISTINCT YEAR(tanggal_dispensasi) as tahun')
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