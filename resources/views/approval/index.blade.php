@extends('layouts.app')
@section('title', 'Persetujuan Dispensasi')
@section('page-title', 'Persetujuan Dispensasi')

@section('content')
<div class="mb-8">
    <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Persetujuan</p>
    <h1 class="font-display text-3xl text-ink">Menunggu Keputusan Anda</h1>
    <p class="text-sm text-ink-soft mt-1">
        Pengajuan dispensasi yang perlu Anda setujui atau tolak.
    </p>
</div>

<div class="table-scroll-wrapper">
    <table class="table-pro">
        <thead>
            <tr>
                <th>Nomor</th>
                <th>Pegawai</th>
                <th>Unit Organisasi</th>
                <th>Tanggal Dispensasi</th>
                <th>Waktu</th>
                <th>Diajukan</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kelompokPengajuan as $kelompok)
            @php
                $acuan = $kelompok->acuan;
                $jumlahLain = $kelompok->baris->count() - 1;
            @endphp
            <tr>
                <td class="mono-data text-ink-soft">
                    {{ $acuan->nomor_dispensasi }}
                    @if ($jumlahLain > 0)
                    <span class="block text-xs text-ink-soft"
                          title="{{ $kelompok->baris->pluck('nomor_dispensasi')->implode(', ') }}">
                        +{{ $jumlahLain }} nomor lainnya
                    </span>
                    @endif
                </td>
                <td>
                    <p class="font-medium text-ink">{{ $acuan->pegawai->nama_pegawai }}</p>
                    <p class="text-xs text-ink-soft">{{ $acuan->pegawai->jabatan?->nama ?? '-' }}</p>
                </td>
                <td class="text-ink-soft">{{ $acuan->unitOrganisasi->nama }}</td>
                <td>{{ $acuan->tanggal_dispensasi->format('d M Y') }}</td>
                <td class="text-ink-soft">
                    @foreach ($kelompok->waktu as $w)
                    <span class="badge badge-default">{{ $w }}</span>
                    @endforeach
                </td>
                <td class="text-ink-soft text-xs">
                    {{ \Carbon\Carbon::parse($kelompok->tanggal_pengajuan)->format('d M Y') }}
                </td>
                <td class="text-right whitespace-nowrap">
                    <a href="{{ route('approval.show', $acuan) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> Tinjau
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center text-ink-soft py-12">
                    <i class="fas fa-circle-check text-2xl mb-2 block"></i>
                    Tidak ada pengajuan yang menunggu persetujuan Anda saat ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($kelompokPengajuan->isNotEmpty())
<div class="flex items-center justify-between flex-wrap gap-3 mt-3">
    <p class="text-xs text-ink-soft">
        Menampilkan {{ $kelompokPengajuan->firstItem() }}–{{ $kelompokPengajuan->lastItem() }}
        dari {{ $kelompokPengajuan->total() }} kelompok pengajuan.
    </p>
    {{ $kelompokPengajuan->links() }}
</div>
@endif
@endsection