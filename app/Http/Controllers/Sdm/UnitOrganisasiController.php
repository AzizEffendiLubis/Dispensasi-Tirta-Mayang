<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUnitOrganisasiRequest;
use App\Models\Dispensasi;
use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitOrganisasiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tingkat = $request->input('tingkat');
        $parentId = $request->input('parent_id');
        $status = $request->input('status'); 

        $query = UnitOrganisasi::with('parent')->withCount('children');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        if ($tingkat) {
            $query->where('tingkat', $tingkat);
        }

        if ($parentId) {
            $query->where('parent_id', $parentId);
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        $unitOrganisasis = $query->orderBy('tingkat')->orderBy('urutan')->orderBy('nama')->get();

        $statistik = $this->getStatistikUnit();

        return view('sdm.unit-organisasi.index', compact(
            'unitOrganisasis',
            'search',
            'tingkat',
            'parentId',
            'status',
            'statistik'
        ));
    }

    public function show($id)
    {
        $unitOrganisasi = UnitOrganisasi::with([
            'parent',
            'children' => fn ($q) => $q->orderBy('urutan')->orderBy('nama'),
            'pegawais' => fn ($q) => $q->where('status', 'aktif')->with('jabatan')->orderBy('nama_pegawai'),
            'users' => fn ($q) => $q->where('is_active', true)->with('jabatan'),
        ])->findOrFail($id);

        $statistik = $this->getStatistikUnit();

        $unitIds = $unitOrganisasi->selfAndDescendantIds();
        $statistikDispensasi = $this->getStatistikDispensasiUnit($unitIds);

        return view('sdm.unit-organisasi.show', compact(
            'unitOrganisasi',
            'statistik',
            'statistikDispensasi'
        ));
    }

    public function list(Request $request)
    {
        $query = UnitOrganisasi::select('id', 'kode', 'nama', 'tingkat', 'parent_id')
            ->active()
            ->orderBy('nama');

        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->input('tingkat'));
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->input('parent_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    public function tree()
    {
        $semuaUnit = UnitOrganisasi::active()->orderBy('urutan')->orderBy('nama')->get();

        $bangunPohon = function ($parentId) use (&$bangunPohon, $semuaUnit) {
            return $semuaUnit
                ->where('parent_id', $parentId)
                ->map(fn ($unit) => [
                    'id'       => $unit->id,
                    'kode'     => $unit->kode,
                    'nama'     => $unit->nama,
                    'tingkat'  => $unit->tingkat,
                    'children' => $bangunPohon($unit->id),
                ])
                ->values();
        };

        return response()->json([
            'success' => true,
            'data' => $bangunPohon(null),
        ]);
    }

    public function create(Request $request)
    {
        $tingkatDiminta = $request->input('tingkat', 'divisi');

        return view('sdm.unit-organisasi.create', [
            'tingkatDiminta'   => $tingkatDiminta,
            'parentIdTerpilih' => $request->input('parent_id'),
            'parents'          => $this->calonParent(),
        ]);
    }

    public function store(StoreUnitOrganisasiRequest $request)
    {
        UnitOrganisasi::create($request->validated());

        return redirect()->route('sdm.unit-organisasi.index')->with('success', 'Unit organisasi berhasil ditambahkan.');
    }

    public function edit(UnitOrganisasi $unitOrganisasi)
    {
        return view('sdm.unit-organisasi.edit', [
            'unitOrganisasi' => $unitOrganisasi,
            'parents'        => $this->calonParent($unitOrganisasi),
        ]);
    }

    public function update(StoreUnitOrganisasiRequest $request, UnitOrganisasi $unitOrganisasi)
    {
        $pindahParent = $unitOrganisasi->parent_id !== $request->input('parent_id');

        $unitOrganisasi->update($request->validated());

        $pesan = 'Data unit organisasi berhasil diperbarui.';

        if ($pindahParent) {
            $jumlahDireassign = $this->reassignApproverPengajuanMenunggu($unitOrganisasi);

            $pesan .= $jumlahDireassign > 0
                ? " Unit ini baru saja dipindahkan strukturnya — {$jumlahDireassign} pengajuan dispensasi yang masih menunggu persetujuan di unit ini (dan turunannya) sudah dihitung ulang approver-nya sesuai Alur Approval terbaru."
                : ' Unit ini baru saja dipindahkan strukturnya. Tidak ada pengajuan dispensasi yang masih menunggu persetujuan di unit ini saat ini, jadi tidak perlu penyesuaian approver.';
        }

        return redirect()->route('sdm.unit-organisasi.index')->with('success', $pesan);
    }

    public function destroy(UnitOrganisasi $unitOrganisasi)
    {
        if ($unitOrganisasi->children()->active()->exists()) {
            return back()->with('error', "Tidak bisa menonaktifkan {$unitOrganisasi->nama} karena masih punya unit AKTIF di bawahnya. Nonaktifkan unit anaknya dulu.");
        }

        $unitOrganisasi->update(['is_active' => false]);

        return back()->with('success', "{$unitOrganisasi->nama} berhasil dinonaktifkan.");
    }

    public function aktifkan(UnitOrganisasi $unitOrganisasi)
    {
        $unitOrganisasi->update(['is_active' => true]);

        return back()->with('success', "{$unitOrganisasi->nama} berhasil diaktifkan kembali.");
    }

    private function calonParent(?UnitOrganisasi $unitSaatIni = null)
    {
        $query = UnitOrganisasi::active()->orderBy('tingkat')->orderBy('nama');

        if ($unitSaatIni) {
            $query->whereNotIn('id', $unitSaatIni->selfAndDescendantIds());
        }

        return $query->get();
    }

    private function reassignApproverPengajuanMenunggu(UnitOrganisasi $unit): int
    {
        $unitIds = $unit->selfAndDescendantIds();

        $dispensasiTerdampak = Dispensasi::whereIn('unit_organisasi_id', $unitIds)
            ->where('status_pengajuan', 'menunggu_persetujuan')
            ->with('pegawai.jabatan', 'pegawai.unitOrganisasi')
            ->get();

        $jumlahBerubah = 0;
        foreach ($dispensasiTerdampak as $dispensasi) {
            $approverLamaId = $dispensasi->approver_saat_ini_id;
            $dispensasi->tentukanApproverAwal();

            if ($dispensasi->approver_saat_ini_id !== $approverLamaId) {
                $dispensasi->save();
                $jumlahBerubah++;
            }
        }

        return $jumlahBerubah;
    }

    private function getStatistikUnit(): array
    {
        $units = UnitOrganisasi::select('id', 'parent_id')->active()->get();

        $pegawaiLangsungAktif = DB::table('pegawais')
            ->select('unit_organisasi_id', DB::raw('COUNT(*) as total'))
            ->where('status', 'aktif')
            ->groupBy('unit_organisasi_id')
            ->pluck('total', 'unit_organisasi_id');

        $adminLangsung = DB::table('users')
            ->select('unit_organisasi_id', DB::raw('COUNT(*) as total'))
            ->where('role', 'admin_departemen')
            ->where('is_active', true)
            ->groupBy('unit_organisasi_id')
            ->pluck('total', 'unit_organisasi_id');

        $approverLangsung = DB::table('users')
            ->select('unit_organisasi_id', DB::raw('COUNT(*) as total'))
            ->where('role', 'approver')
            ->where('is_active', true)
            ->groupBy('unit_organisasi_id')
            ->pluck('total', 'unit_organisasi_id');

        $anakPerInduk = $units->groupBy('parent_id');

        $hitungPegawaiRollup = function ($unitId) use (&$hitungPegawaiRollup, $anakPerInduk, $pegawaiLangsungAktif) {
            $total = (int) ($pegawaiLangsungAktif[$unitId] ?? 0);

            foreach ($anakPerInduk->get($unitId, collect()) as $anak) {
                $total += $hitungPegawaiRollup($anak->id);
            }

            return $total;
        };

        return $units->mapWithKeys(fn ($unit) => [
            $unit->id => [
                'unit_organisasi_id' => $unit->id,
                'pegawai_aktif'      => $hitungPegawaiRollup($unit->id),
                'pegawai_langsung'   => (int) ($pegawaiLangsungAktif[$unit->id] ?? 0),
                'total_admin'        => (int) ($adminLangsung[$unit->id] ?? 0),
                'total_approver'     => (int) ($approverLangsung[$unit->id] ?? 0),
            ],
        ])->toArray();
    }

    private function getStatistikDispensasiUnit(array $unitOrganisasiIds): array
    {
        $tahunIni = now()->year;
        $bulanIni = now()->month;

        $counts = Dispensasi::whereIn('unit_organisasi_id', $unitOrganisasiIds)
            ->whereYear('tanggal_dispensasi', $tahunIni)
            ->select(
                'status_pengajuan',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN MONTH(tanggal_dispensasi) = {$bulanIni} THEN 1 ELSE 0 END) as total_bulan_ini")
            )
            ->groupBy('status_pengajuan')
            ->get();

        $perStatus = [
            'menunggu_persetujuan' => 0,
            'disetujui'            => 0,
            'ditolak'              => 0,
        ];
        $totalTahunIni = 0;
        $bulanIniData = 0;

        foreach ($counts as $row) {
            if (array_key_exists($row->status_pengajuan, $perStatus)) {
                $perStatus[$row->status_pengajuan] = (int) $row->total;
            }
            $totalTahunIni += (int) $row->total;
            $bulanIniData  += (int) $row->total_bulan_ini;
        }

        return [
            'total_tahun_ini' => $totalTahunIni,
            'bulan_ini'       => $bulanIniData,
            'per_status'      => $perStatus,
        ];
    }
}