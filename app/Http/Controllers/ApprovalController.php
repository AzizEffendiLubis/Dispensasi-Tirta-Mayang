<?php

namespace App\Http\Controllers;

use App\Models\Dispensasi;
use App\Notifications\DispensasiDiputuskan;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    private const URUTAN_WAKTU = ['T', 'TBO', 'TBI', 'CP'];

    public function index()
    {
        $user = Auth::user();
        abort_unless($user->isPemberiKeputusan(), 403, 'Akun Anda tidak berwenang memutuskan pengajuan dispensasi.');

        $dispensasis = Dispensasi::untukPemberiKeputusan($user)
            ->with(['pegawai', 'unitOrganisasi'])
            ->get();

        $kelompok = $dispensasis
            ->groupBy(fn ($d) => $d->pegawai_id . '|' . $d->tanggal_dispensasi->format('Y-m-d'))
            ->map(function ($baris) {
                $acuan = $baris->first();
                return (object) [
                    'acuan'             => $acuan,
                    'baris'             => $baris->sortBy(
                        fn ($d) => array_search($d->waktu_dispensasi, self::URUTAN_WAKTU)
                    )->values(),
                    'waktu'             => $baris->pluck('waktu_dispensasi')->values(),
                    'tanggal_pengajuan' => $baris->max('tanggal_pengajuan'),
                ];
            })
            ->sortByDesc('tanggal_pengajuan')
            ->values();

        $perPage = 10;
        $halaman = request()->input('page', 1);
        $paginated = new LengthAwarePaginator(
            $kelompok->forPage($halaman, $perPage)->values(),
            $kelompok->count(),
            $perPage,
            $halaman,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('approval.index', ['kelompokPengajuan' => $paginated]);
    }

    public function show(Dispensasi $dispensasi)
    {
        $this->authorizeAccess($dispensasi);
        $kelompokPengajuan = Dispensasi::query()
            ->satuPengajuanMenunggu($dispensasi)
            ->with(['pegawai', 'unitOrganisasi', 'adminDepartemen'])
            ->get()
            ->sortBy(fn ($d) => array_search($d->waktu_dispensasi, self::URUTAN_WAKTU))
            ->values();
        return view('approval.show', compact('dispensasi', 'kelompokPengajuan'));
    }

    public function approve(Request $request, Dispensasi $dispensasi)
    {
        $this->authorizeAccess($dispensasi);
        $kelompok = Dispensasi::query()->satuPengajuanMenunggu($dispensasi)->get();
        DB::transaction(function () use ($kelompok, $request) {
            Dispensasi::query()
                ->whereIn('id', $kelompok->pluck('id'))
                ->update([
                    'status_pengajuan'    => 'disetujui',
                    'diproses_oleh_id'    => Auth::id(),
                    'tanggal_keputusan'   => now(),
                    'catatan_persetujuan' => $request->input('catatan'),
                ]);
        });
        $kelompok->fresh()->each(fn ($baris) => $this->beriTahuAdminDepartemen($baris));
        $nomorList = $kelompok->pluck('nomor_dispensasi')->implode(', ');
        return redirect(Auth::user()->dashboardRoute())
            ->with('success', "Dispensasi {$nomorList} disetujui.");
    }

    public function reject(Request $request, Dispensasi $dispensasi)
    {
        $request->validate(['catatan' => 'required|string|min:5'], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
            'catatan.min'       => 'Alasan penolakan minimal 5 karakter.',
        ]);
        $this->authorizeAccess($dispensasi);
        $kelompok = Dispensasi::query()->satuPengajuanMenunggu($dispensasi)->get();
        DB::transaction(function () use ($kelompok, $request) {
            Dispensasi::query()
                ->whereIn('id', $kelompok->pluck('id'))
                ->update([
                    'status_pengajuan'    => 'ditolak',
                    'diproses_oleh_id'    => Auth::id(),
                    'tanggal_keputusan'   => now(),
                    'catatan_persetujuan' => $request->input('catatan'),
                ]);
        });
        $kelompok->fresh()->each(fn ($baris) => $this->beriTahuAdminDepartemen($baris));
        $nomorList = $kelompok->pluck('nomor_dispensasi')->implode(', ');
        return redirect(Auth::user()->dashboardRoute())
            ->with('success', "Dispensasi {$nomorList} ditolak.");
    }

    private function beriTahuAdminDepartemen(Dispensasi $dispensasi): void
    {
        $dispensasi->adminDepartemen?->notify(new DispensasiDiputuskan($dispensasi));
    }

    private function authorizeAccess(Dispensasi $dispensasi): void
    {
        abort_unless(
            $dispensasi->approver_saat_ini_id !== null
                && $dispensasi->approver_saat_ini_id === Auth::id(),
            403,
            'Anda tidak berwenang mengakses pengajuan ini.'
        );
    }
}