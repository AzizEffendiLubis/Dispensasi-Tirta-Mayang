<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Models\Dispensasi;
use App\Models\UnitOrganisasi;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const NAMA_BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    private const AMBANG_HARI_MENGGANTUNG = 3;

    public function index(Request $request)
    {
        $tahun = (int) $request->input('tahun', now()->year);
        $unitOrganisasiId = $request->input('unit_organisasi_id');
        $status = $request->input('status');

        $base = $this->buildFilteredQuery($tahun, $unitOrganisasiId, $status);

        // Total per status
        $statusCounts = (clone $base)
            ->select('status_pengajuan', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status_pengajuan')
            ->pluck('jumlah', 'status_pengajuan');

        $totalMenunggu  = (int) ($statusCounts['menunggu_persetujuan'] ?? 0);
        $totalDisetujui = (int) ($statusCounts['disetujui'] ?? 0);
        $totalDitolak   = (int) ($statusCounts['ditolak'] ?? 0);
        $totalSemua     = $totalMenunggu + $totalDisetujui + $totalDitolak;

        // Dispensasi per bulan
        $jumlahPerBulanRaw = (clone $base)
            ->select(DB::raw('MONTH(tanggal_dispensasi) as bulan'), DB::raw('COUNT(*) as jumlah'))
            ->groupBy(DB::raw('MONTH(tanggal_dispensasi)'))
            ->pluck('jumlah', 'bulan');

        $perBulan = collect(range(1, 12))->map(fn ($bulan) => [
            'label' => self::NAMA_BULAN[$bulan - 1],
            'total' => (int) ($jumlahPerBulanRaw[$bulan] ?? 0),
        ]);

        // Dispensasi per unit organisasi — termasuk rollup dari unit turunan
        $perUnit = $this->getPerUnitRollup($tahun, $unitOrganisasiId, $status);

        // Dispensasi terbaru
        $terbaru = (clone $base)
            ->with(['pegawai', 'unitOrganisasi', 'diprosesOleh'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Pengajuan menggantung (perlu perhatian)
        $pengajuanMenggantung = $this->getPengajuanMenggantung($unitOrganisasiId);

        // Data untuk filter
        $unitOrganisasis = UnitOrganisasi::active()->orderBy('nama')->get();
        $tahunTersedia = $this->getTahunTersedia();

        return view('dashboard.sdm', compact(
            'totalSemua',
            'totalMenunggu',
            'totalDisetujui',
            'totalDitolak',
            'perBulan',
            'perUnit',
            'terbaru',
            'pengajuanMenggantung',
            'unitOrganisasis',
            'tahunTersedia',
            'tahun',
            'unitOrganisasiId',
            'status'
        ));
    }

    private function buildFilteredQuery(int $tahun, $unitOrganisasiId, $status): Builder
    {
        $query = Dispensasi::query()->whereYear('tanggal_dispensasi', $tahun);

        if ($unitOrganisasiId) {
            $query->where('unit_organisasi_id', $unitOrganisasiId);
        }

        if ($status && in_array($status, ['menunggu_persetujuan', 'disetujui', 'ditolak'], true)) {
            $query->where('status_pengajuan', $status);
        }

        return $query;
    }

    private function getPerUnitRollup(int $tahun, $unitOrganisasiId, $status)
    {
        $langsung = Dispensasi::whereYear('tanggal_dispensasi', $tahun)
            ->when(
                $status && in_array($status, ['menunggu_persetujuan', 'disetujui', 'ditolak'], true),
                fn ($q) => $q->where('status_pengajuan', $status)
            )
            ->select('unit_organisasi_id', DB::raw('COUNT(*) as total'))
            ->groupBy('unit_organisasi_id')
            ->pluck('total', 'unit_organisasi_id');

        $units = UnitOrganisasi::select('id', 'parent_id', 'nama')->active()->get();
        $anakPerInduk = $units->groupBy('parent_id');

        $hitungRollup = function ($unitId) use (&$hitungRollup, $anakPerInduk, $langsung) {
            $total = (int) ($langsung[$unitId] ?? 0);
            foreach ($anakPerInduk->get($unitId, collect()) as $anak) {
                $total += $hitungRollup($anak->id);
            }
            return $total;
        };

        $idDenganTurunan = function ($unitId) use (&$idDenganTurunan, $anakPerInduk) {
            $hasil = [$unitId];
            foreach ($anakPerInduk->get($unitId, collect()) as $anak) {
                $hasil = array_merge($hasil, $idDenganTurunan($anak->id));
            }
            return $hasil;
        };

        $unitTampil = $units;

        if ($unitOrganisasiId && $units->contains('id', (int) $unitOrganisasiId)) {
            $unitTampil = $units->whereIn('id', $idDenganTurunan((int) $unitOrganisasiId));
        }

        return $unitTampil
            ->map(fn ($unit) => [
                'id'    => $unit->id,
                'nama'  => $unit->nama,
                'total' => $hitungRollup($unit->id),
            ])
            ->filter(fn ($row) => $row['total'] > 0)
            ->sortByDesc('total')
            ->values();
    }

    private function getTahunTersedia(): array
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

    private function getPengajuanMenggantung($unitOrganisasiId)
    {
        $query = Dispensasi::where('status_pengajuan', 'menunggu_persetujuan')
            ->where('tanggal_pengajuan', '<=', now()->subDays(self::AMBANG_HARI_MENGGANTUNG)->toDateString())
            ->with(['pegawai', 'unitOrganisasi']);

        if ($unitOrganisasiId) {
            $query->where('unit_organisasi_id', $unitOrganisasiId);
        }

        return $query->orderBy('tanggal_pengajuan')
            ->get()
            ->map(fn ($d) => [
                'nomor' => $d->nomor_dispensasi,
                'pegawai' => $d->pegawai?->nama_pegawai ?? '-',
                'unit' => $d->unitOrganisasi?->nama ?? '-',
                'hari_menunggu' => now()->diffInDays($d->tanggal_pengajuan),
            ]);
    }
}