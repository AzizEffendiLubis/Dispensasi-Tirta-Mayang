@extends('layouts.app')
@section('title', 'Detail Dispensasi')
@section('page-title', 'Detail Dispensasi')

@section('content')
@php
    $statusLabel = fn ($status) => match ($status) {
        'menunggu_persetujuan' => 'Menunggu Persetujuan',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
        default => ucfirst($status ?? '-'),
    };
    $statusClass = fn ($status) => match ($status) {
        'menunggu_persetujuan' => 'badge-menunggu',
        'disetujui' => 'badge-disetujui',
        'ditolak' => 'badge-ditolak',
        default => 'badge-default',
    };
@endphp

<div class="mb-8">
    <a href="{{ route('sdm.monitoring.index') }}" class="text-xs text-accent font-semibold mb-2 inline-flex items-center gap-1">
        <i class="fas fa-arrow-left"></i> Kembali ke Monitoring
    </a>
    <div class="flex items-center gap-3 flex-wrap mt-2">
        <h1 class="font-display text-3xl text-ink">{{ $dispensasi->nomor_dispensasi }}</h1>
        <span class="badge {{ $statusClass($dispensasi->status_pengajuan) }}">{{ $statusLabel($dispensasi->status_pengajuan) }}</span>
        @if ($kelompokTampil->count() > 1)
        <span class="badge badge-default">{{ $kelompokTampil->count() }} waktu dalam satu pengajuan</span>
        @endif
    </div>
    <p class="text-xs text-ink-soft mt-2">Halaman ini bersifat lihat saja (read-only), tidak ada aksi persetujuan atau penerbitan surat di sini.</p>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        {{-- Data pegawai --}}
        <div class="card p-6">
            <h3 class="font-semibold text-ink mb-4">Data Pegawai</h3>
            <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">NIK</dt>
                    <dd class="mono-data text-ink">{{ $dispensasi->pegawai->nik }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Nama</dt>
                    <dd class="font-medium text-ink">{{ $dispensasi->pegawai->nama_pegawai }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Jabatan</dt>
                    <dd class="text-ink">{{ $dispensasi->pegawai->jabatan?->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Unit Organisasi</dt>
                    <dd class="text-ink">{{ $dispensasi->unitOrganisasi->nama }}</dd>
                </div>
            </dl>
        </div>

        {{-- Detail pengajuan --}}
        <div class="card p-6">
            <h3 class="font-semibold text-ink mb-4">Detail Pengajuan</h3>
            <dl class="grid sm:grid-cols-2 gap-4 text-sm mb-5">
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Tanggal Dispensasi</dt>
                    <dd class="text-ink">{{ $dispensasi->tanggal_dispensasi->translatedFormat('d F Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Diajukan Oleh</dt>
                    <dd class="text-ink">{{ $dispensasi->adminDepartemen?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Tanggal Pengajuan</dt>
                    <dd class="text-ink">{{ $dispensasi->tanggal_pengajuan?->translatedFormat('d F Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Jumlah Waktu Diajukan</dt>
                    <dd class="text-ink">{{ $kelompokTampil->count() }}</dd>
                </div>
            </dl>

            <p class="text-xs text-ink-soft mb-2">Rincian per waktu:</p>
            <div class="table-scroll-wrapper">
                <table class="table-pro">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Waktu</th>
                            <th>Keterangan</th>
                            <th>Bukti</th>
                            <th>Status</th>
                            <th>Diproses Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kelompokTampil as $baris)
                        <tr @class(['bg-accent/5' => $baris->id === $dispensasi->id])>
                            <td class="mono-data text-ink-soft">{{ $baris->nomor_dispensasi }}</td>
                            <td><span class="badge badge-default">{{ $baris->waktu_dispensasi }}</span></td>
                            <td class="text-ink whitespace-pre-line">{{ $baris->keterangan ?: '-' }}</td>
                            <td>
                                @if ($baris->bukti_pendukung)
                                <a href="{{ Storage::url($baris->bukti_pendukung) }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline">
                                    <i class="fas fa-paperclip"></i> Lihat
                                </a>
                                @else
                                <span class="text-ink-soft text-xs">-</span>
                                @endif
                            </td>
                            <td><span class="badge {{ $statusClass($baris->status_pengajuan) }}">{{ $statusLabel($baris->status_pengajuan) }}</span></td>
                            <td class="text-ink-soft">{{ $baris->diprosesOleh?->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($dispensasi->catatan_persetujuan || $dispensasi->tanggal_keputusan)
            <div class="mt-5 pt-4 border-t border-line space-y-2">
                @if ($dispensasi->catatan_persetujuan)
                <div class="text-sm">
                    <p class="text-xs text-ink-soft mb-0.5">Catatan Persetujuan</p>
                    <p class="text-ink whitespace-pre-line">{{ $dispensasi->catatan_persetujuan }}</p>
                </div>
                @endif
                @if ($dispensasi->tanggal_keputusan)
                <p class="text-xs text-ink-soft">
                    Diputuskan pada {{ $dispensasi->tanggal_keputusan->translatedFormat('d F Y, H:i') }}
                </p>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Status surat e-dispensasi --}}
    <div class="lg:col-span-1">
        <div class="card p-6">
            <h3 class="font-semibold text-ink mb-4">Surat E-Dispensasi</h3>
            @if ($dispensasi->isSudahDicetak())
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Nomor Surat</dt>
                    <dd class="font-medium text-ink">{{ $dispensasi->nomor_surat_dispensasi }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Tanggal Surat</dt>
                    <dd class="text-ink">{{ $dispensasi->tanggal_surat_dispensasi?->translatedFormat('d F Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Diterbitkan Oleh</dt>
                    <dd class="text-ink">{{ $dispensasi->dicetakOleh?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Ditujukan Kepada</dt>
                    <dd class="text-ink">{{ $dispensasi->ditujukanKepada?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Diterbitkan Pada</dt>
                    <dd class="text-ink">{{ $dispensasi->dicetak_pada?->translatedFormat('d F Y, H:i') ?? '-' }}</dd>
                </div>
            </dl>
            @else
            <p class="text-sm text-ink-soft">
                <i class="fas fa-circle-info"></i>
                Surat e-dispensasi untuk pengajuan ini belum diterbitkan oleh admin departemen.
            </p>
            @endif
        </div>
    </div>
</div>
@endsection