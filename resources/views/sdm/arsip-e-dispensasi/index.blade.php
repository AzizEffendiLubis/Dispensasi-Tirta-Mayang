@extends('layouts.app')
@section('title', 'Arsip E-Dispensasi')
@section('page-title', 'Arsip E-Dispensasi')

@section('content')
<div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
    <div>
        <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin SDM</p>
        <h1 class="font-display text-3xl text-ink">Arsip E-Dispensasi</h1>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('sdm.arsip-e-dispensasi.index') }}"
      class="card p-4 mb-6 flex gap-3 flex-wrap items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="field-label">Cari</label>
        <input type="text" name="cari" value="{{ $cari }}" placeholder="Nomor surat / nama pegawai"
               class="field-input">
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="field-label">Unit Organisasi</label>
        <select name="unit_organisasi_id" class="field-input">
            <option value="">Semua Unit</option>
            @foreach($unitOrganisasiList as $u)
                <option value="{{ $u->id }}" @selected($unitOrganisasiId == $u->id)>
                    {{ $u->nama }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[160px]">
        <label class="field-label">Bulan</label>
        <select name="bulan" class="field-input">
            <option value="">Semua Bulan</option>
            @foreach($namaBulan as $angka => $nama)
                <option value="{{ $angka }}" @selected($bulan == $angka)>{{ $nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[140px]">
        <label class="field-label">Tahun</label>
        <select name="tahun" class="field-input">
            <option value="">Semua Tahun</option>
            @foreach($tahunTersedia as $th)
                <option value="{{ $th }}" @selected($tahun == $th)>{{ $th }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-filter"></i> Filter
    </button>
    @if ($cari || $unitOrganisasiId || $bulan || $tahun)
    <a href="{{ route('sdm.arsip-e-dispensasi.index') }}" class="btn btn-outline">Reset</a>
    @endif
</form>

<div class="table-scroll-wrapper">
    <table class="table-pro">
        <thead>
            <tr>
                <th>Nomor Surat</th>
                <th>Tanggal Surat</th>
                <th>Tanggal Dispensasi</th>
                <th>Unit Organisasi</th>
                <th>Pegawai</th>
                <th>Penyetuju</th>
                <th>Diterbitkan Oleh</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($arsip as $surat)
            <tr>
                <td class="mono-data text-ink-soft">{{ $surat->nomor_surat }}</td>
                <td>{{ optional($surat->tanggal_surat)->translatedFormat('d M Y') }}</td>
                <td class="text-ink-soft">{{ optional($surat->tanggal_dispensasi)->translatedFormat('d M Y') }}</td>
                <td>{{ $surat->unit?->nama ?? '-' }}</td>
                <td>
                    @if($surat->jumlah_pegawai > 1)
                        <span class="badge badge-default">{{ $surat->jumlah_pegawai }} pegawai</span>
                        <p class="text-xs text-ink-soft mt-1 truncate max-w-xs" title="{{ $surat->daftar_nama }}">
                            {{ $surat->daftar_nama }}
                        </p>
                    @else
                        <span class="text-ink font-medium">{{ $surat->daftar_nama }}</span>
                    @endif
                </td>
                <td class="text-ink-soft">{{ $surat->penyetuju?->name ?? '-' }}</td>
                <td class="text-ink-soft">{{ $surat->dicetak_oleh?->name ?? '-' }}</td>
                <td class="text-right whitespace-nowrap">
                    <a href="{{ route('sdm.arsip-e-dispensasi.show', $surat->baris_jangkar_id) }}"
                       class="btn btn-sm btn-outline" title="Lihat Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('dispensasi.surat.unduh', $surat->baris_jangkar_id) }}"
                       class="btn btn-sm btn-outline">
                        <i class="fas fa-file-pdf"></i> Unduh
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-ink-soft py-12">
                    <i class="fas fa-box-archive text-2xl mb-2 block"></i>
                    Belum ada surat e-dispensasi yang cocok dengan filter ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($arsip->isNotEmpty())
<div class="flex items-center justify-between flex-wrap gap-3 mt-3">
    <p class="text-xs text-ink-soft">
        Menampilkan {{ $arsip->firstItem() }}–{{ $arsip->lastItem() }}
        dari {{ $arsip->total() }} surat.
    </p>
    {{ $arsip->links() }}
</div>
@endif
@endsection