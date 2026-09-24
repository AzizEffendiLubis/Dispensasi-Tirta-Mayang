@extends('layouts.app')
@section('title', 'Pengajuan Dispensasi')
@section('page-title', 'Pengajuan Dispensasi')

@section('content')
<div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
    <div>
        <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin Departemen</p>
        <h1 class="font-display text-3xl text-ink">Pengajuan Dispensasi</h1>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('dispensasi.export.pdf', request()->query()) }}" class="btn btn-outline">
            <i class="fas fa-file-pdf"></i> Export ke PDF
        </a>
        <a href="{{ route('dispensasi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajukan Dispensasi
        </a>
    </div>
</div>

<p class="text-xs text-ink-soft -mt-6 mb-6">
    <i class="fas fa-circle-info"></i>
    Export PDF hanya menyertakan pengajuan berstatus <strong>Disetujui</strong> sesuai filter tahun/bulan yang sedang aktif di bawah.
</p>

@if ($belumDijadikanSuratCount > 0)
<div class="card p-4 mb-6 flex items-center gap-3 border-amber-200 bg-amber-50">
    <i class="fas fa-triangle-exclamation text-amber-500"></i>
    <p class="text-sm text-amber-700">
        Ada <strong>{{ $belumDijadikanSuratCount }}</strong> dispensasi yang sudah disetujui namun
        belum dijadikan e-dispensasi.
    </p>
</div>
@endif

{{-- Filter --}}
<form method="GET" class="card p-4 mb-6 flex gap-3 flex-wrap items-end">
    <div class="flex-1 min-w-[160px]">
        <label class="text-xs text-ink-soft mb-1 block">Tahun</label>
        <select name="tahun" class="field-input">
            <option value="">Semua Tahun</option>
            @foreach ($tahunTersedia as $t)
            <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[160px]">
        <label class="text-xs text-ink-soft mb-1 block">Bulan</label>
        <select name="bulan" class="field-input">
            <option value="">Semua Bulan</option>
            @foreach ($namaBulan as $angka => $nama)
            <option value="{{ $angka }}" @selected($bulan == $angka)>{{ $nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[220px]">
        <label class="text-xs text-ink-soft mb-1 block">Status Surat</label>
        <label class="field-input flex items-center gap-2.5 cursor-pointer select-none">
            <input
                type="checkbox"
                name="belum_surat"
                value="1"
                class="peer sr-only"
                @checked($belumSurat)
                onchange="this.form.requestSubmit()"
            >
            <span class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full bg-slate-200 transition-colors peer-checked:bg-accent">
                <span class="inline-block h-3.5 w-3.5 translate-x-1 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4.5"></span>
            </span>
            <span class="text-sm text-ink">Belum dibuat surat e-dispensasi</span>
        </label>
    </div>
    <button class="btn btn-primary">Terapkan Filter</button>
    @if ($tahun || $bulan || $belumSurat)
    <a href="{{ route('dispensasi.index') }}" class="btn btn-outline">Reset</a>
    @endif
</form>

<div class="table-scroll-wrapper">
    <table class="table-pro">
        <thead>
            <tr>
                <th>Nomor</th>
                <th>Pegawai</th>
                <th>Tanggal Dispensasi</th>
                <th>Waktu</th>
                <th>Status</th>
                <th>Diajukan</th>
                <th></th>
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
                <td>
                    <p class="font-medium text-ink">{{ $acuan->pegawai->nama_pegawai }}</p>
                    @if ($acuan->subdepartemen)
                    <p class="text-xs text-ink-soft">{{ $acuan->subdepartemen->nama_subdepartemen }}</p>
                    @endif
                </td>
                <td>{{ $acuan->tanggal_dispensasi->format('d M Y') }}</td>
                <td class="text-ink-soft">
                    @foreach ($kelompok->baris as $baris)
                    <span class="badge badge-default">{{ $baris->waktu_dispensasi }}</span>
                    @endforeach
                </td>
                <td>
                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    @if ($kelompok->keteranganSurat)
                    <span class="block mt-1 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-0.5 w-max">
                        {{ $kelompok->keteranganSurat }}
                    </span>
                    @endif
                </td>
                <td class="text-ink-soft text-xs">
                    {{ \Carbon\Carbon::parse($kelompok->tanggal_pengajuan)->format('d M Y') }}
                </td>
                <td class="text-right whitespace-nowrap">
                    <a href="{{ route('dispensasi.show', $acuan) }}" class="btn btn-sm btn-outline">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-ink-soft py-12">
                    <i class="fas fa-inbox text-2xl mb-2 block"></i>
                    Belum ada pengajuan dispensasi dari departemen Anda.
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
        dari {{ $dispensasis->total() }} kelompok pengajuan.
    </p>
    {{ $dispensasis->links() }}
</div>
@endif
@endsection