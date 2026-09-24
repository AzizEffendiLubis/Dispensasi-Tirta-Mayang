@extends('layouts.app')
@section('title', 'Monitoring Dispensasi')
@section('page-title', 'Monitoring Dispensasi')

@section('content')
<div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
    <div>
        <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin SDM</p>
        <h1 class="font-display text-3xl text-ink">Monitoring Dispensasi</h1>
    </div>
    <a href="{{ route('sdm.monitoring.export.excel', request()->query()) }}" class="btn btn-primary">
        <i class="fas fa-file-excel"></i> Export ke Excel
    </a>
</div>

<p class="text-xs text-ink-soft -mt-6 mb-6">
    <i class="fas fa-circle-info"></i>
    Export Excel hanya menyertakan pengajuan berstatus <strong>Disetujui</strong>
    sesuai filter tahun/bulan/unit yang sedang aktif di bawah — filter status
    tidak ikut mempengaruhi export.
</p>

{{-- Filter --}}
<form method="GET" class="card p-4 mb-6 flex gap-3 flex-wrap items-end">
    <div class="flex-1 min-w-[140px]">
        <label class="text-xs text-ink-soft mb-1 block">Tahun</label>
        <select name="tahun" class="field-input">
            <option value="">Semua Tahun</option>
            @foreach ($tahunTersedia as $t)
            <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[140px]">
        <label class="text-xs text-ink-soft mb-1 block">Bulan</label>
        <select name="bulan" class="field-input">
            <option value="">Semua Bulan</option>
            @foreach ($namaBulan as $angka => $nama)
            <option value="{{ $angka }}" @selected($bulan == $angka)>{{ $nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="text-xs text-ink-soft mb-1 block">Unit Organisasi</label>
        <select name="unit_organisasi_id" class="field-input">
            <option value="">Semua Unit</option>
            @foreach ($unitOrganisasis as $u)
            <option value="{{ $u->id }}" @selected($unitOrganisasiId == $u->id)>{{ $u->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="text-xs text-ink-soft mb-1 block">Status</label>
        <select name="status" class="field-input">
            <option value="">Semua Status</option>
            <option value="menunggu_persetujuan" @selected($status === 'menunggu_persetujuan')>Menunggu Persetujuan</option>
            <option value="disetujui" @selected($status === 'disetujui')>Disetujui</option>
            <option value="ditolak" @selected($status === 'ditolak')>Ditolak</option>
        </select>
    </div>
    <div class="flex-1 min-w-[160px]">
        <label class="text-xs text-ink-soft mb-1 block">Urutkan</label>
        <select name="urutan" class="field-input">
            <option value="terbaru" @selected($urutan === 'terbaru')>Terbaru</option>
            <option value="terlama" @selected($urutan === 'terlama')>Terlama</option>
        </select>
    </div>
    <button class="btn btn-primary">Terapkan Filter</button>
    @if ($tahun || $bulan || $unitOrganisasiId || $status || $urutan === 'terlama')
    <a href="{{ route('sdm.monitoring.index') }}" class="btn btn-outline">Reset</a>
    @endif
</form>

<div class="table-scroll-wrapper">
    <table class="table-pro">
        <thead>
            <tr>
                <th>Nomor</th>
                <th>Pegawai</th>
                <th>Unit Organisasi</th>
                <th>Tanggal Dispensasi</th>
                <th>Waktu</th>
                <th>Diproses Oleh</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dispensasis as $kelompok)
            @php
                $acuan = $kelompok->acuan;
                $jumlahLain = $kelompok->baris->count() - 1;
                $statusLabel = match ($kelompok->statusSeragam) {
                    'menunggu_persetujuan' => 'Menunggu',
                    'disetujui' => 'Disetujui',
                    'ditolak' => 'Ditolak',
                    null => 'Status Beragam',
                    default => ucfirst($kelompok->statusSeragam),
                };
                $statusClass = match ($kelompok->statusSeragam) {
                    'menunggu_persetujuan' => 'badge-menunggu',
                    'disetujui' => 'badge-disetujui',
                    'ditolak' => 'badge-ditolak',
                    default => 'badge-default',
                };
            @endphp
            <tr>
                <td class="mono-data text-ink-soft">
                    {{ $acuan->nomor_dispensasi }}
                    @if ($jumlahLain > 0)
                    <span class="block text-xs text-ink-soft" title="{{ $kelompok->nomor_list }}">
                        +{{ $jumlahLain }} nomor lainnya
                    </span>
                    @endif
                </td>
                <td class="font-medium">{{ $acuan->pegawai->nama_pegawai }}</td>
                <td class="text-ink-soft">{{ $kelompok->unitTampil->nama ?? $acuan->unitOrganisasi->nama }}</td>
                <td>{{ $acuan->tanggal_dispensasi->format('d M Y') }}</td>
                <td class="text-ink-soft">
                    @foreach ($kelompok->baris as $baris)
                    <span class="badge badge-default">{{ $baris->waktu_dispensasi }}</span>
                    @endforeach
                </td>
                <td class="text-ink-soft">{{ $acuan->diprosesOleh?->name ?? '-' }}</td>
                <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                <td>
                    <a href="{{ route('sdm.monitoring.show', $acuan) }}" class="btn btn-sm btn-outline" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-ink-soft py-12">
                    <i class="fas fa-inbox text-2xl mb-2 block"></i>
                    Belum ada data dispensasi sesuai filter yang dipilih.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($dispensasis->isNotEmpty())
<div class="flex items-center justify-between flex-wrap gap-3 mt-3">
    <p class="text-xs text-ink-soft">
        Menampilkan {{ $dispensasis->firstItem() }}–{{ $dispensasis->lastItem() }}
        dari {{ $dispensasis->total() }} kelompok data.
    </p>
    {{ $dispensasis->links() }}
</div>
@endif
@endsection