@extends('layouts.app')
@section('title', 'Detail Pengajuan — ' . $dispensasi->nomor_dispensasi)
@section('page-title', 'Detail Pengajuan')

@section('content')
@php
    $statusLabel = match ($dispensasi->status_pengajuan) {
        'menunggu_persetujuan' => 'Menunggu Persetujuan',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
        default => ucfirst($dispensasi->status_pengajuan),
    };
    $statusClass = match ($dispensasi->status_pengajuan) {
        'menunggu_persetujuan' => 'badge-menunggu',
        'disetujui' => 'badge-disetujui',
        'ditolak' => 'badge-ditolak',
        default => 'badge-default',
    };
@endphp

<div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
    <div>
        <a href="{{ route('dispensasi.index') }}" class="text-xs text-accent font-semibold mb-2 inline-flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Kembali ke Pengajuan Dispensasi
        </a>
        <div class="flex items-center gap-3 flex-wrap mt-2">
            <h1 class="font-display text-3xl text-ink">{{ $dispensasi->nomor_dispensasi }}</h1>
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
            @if ($kelompokTampil->count() > 1)
            <span class="badge badge-default">{{ $kelompokTampil->count() }} waktu dalam satu pengajuan</span>
            @endif
        </div>
    </div>

    @if ($dispensasi->isDisetujui() && ! $dispensasi->isSudahDicetak())
        <a href="{{ route('dispensasi.cetak.form', $dispensasi) }}" class="btn btn-outline">
            <i class="fas fa-file-lines"></i> Cetak Dispensasi
        </a>
    @elseif ($dispensasi->isSudahDicetak())
        <a href="{{ route('dispensasi.surat.unduh', $dispensasi) }}" class="btn btn-outline">
            <i class="fas fa-download"></i> Unduh Surat E-Dispensasi
        </a>
    @endif
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
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

        <div class="card p-6">
            <h3 class="font-semibold text-ink mb-4">Detail Pengajuan</h3>
            <dl class="grid sm:grid-cols-2 gap-4 text-sm mb-5">
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Tanggal Dispensasi</dt>
                    <dd class="text-ink">{{ $dispensasi->tanggal_dispensasi->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Diajukan Oleh</dt>
                    <dd class="text-ink">{{ $dispensasi->adminDepartemen?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Tanggal Pengajuan</dt>
                    <dd class="text-ink">{{ $dispensasi->tanggal_pengajuan->format('d M Y') }}</dd>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($kelompokTampil as $baris)
                        <tr>
                            <td class="mono-data text-ink-soft">{{ $baris->nomor_dispensasi }}</td>
                            <td><span class="badge badge-default">{{ $baris->waktu_dispensasi }}</span></td>
                            <td class="text-ink whitespace-pre-line">{{ $baris->keterangan ?: '-' }}</td>
                            <td>
                                @if ($baris->bukti_pendukung)
                                <a href="{{ asset('storage/' . $baris->bukti_pendukung) }}" target="_blank" class="btn btn-sm btn-outline">
                                    <i class="fas fa-paperclip"></i> Lihat
                                </a>
                                @else
                                <span class="text-ink-soft text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="lg:col-span-1 space-y-4">
        <div class="card p-6">
            <h3 class="font-semibold text-ink mb-4">Status Keputusan</h3>
            @if ($dispensasi->status_pengajuan === 'menunggu_persetujuan')
            <p class="text-sm text-ink-soft">
                <i class="fas fa-clock text-[#C8862B]"></i>
                Masih menunggu keputusan dari pihak berwenang. Anda akan mendapat notifikasi begitu ada keputusan.
            </p>
            @else
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Status</dt>
                    <dd><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Diputuskan Oleh</dt>
                    <dd class="text-ink">{{ $dispensasi->diprosesOleh?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Tanggal Keputusan</dt>
                    <dd class="text-ink">{{ $dispensasi->tanggal_keputusan?->format('d M Y, H:i') ?? '-' }}</dd>
                </div>
                @if ($dispensasi->catatan_persetujuan)
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Catatan</dt>
                    <dd class="text-ink whitespace-pre-line">{{ $dispensasi->catatan_persetujuan }}</dd>
                </div>
                @endif
            </dl>
            @endif
        </div>

        @if ($dispensasi->isSudahDicetak())
        <div class="card p-6">
            <h3 class="font-semibold text-ink mb-4">
                <i class="fas fa-file-lines text-accent"></i> Surat E-Dispensasi
            </h3>
            <dl class="space-y-3 text-sm mb-4">
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Nomor Surat</dt>
                    <dd class="mono-data text-ink">{{ $dispensasi->nomor_surat_dispensasi }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Tanggal Surat</dt>
                    <dd class="text-ink">{{ $dispensasi->tanggal_surat_dispensasi->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Diterbitkan Oleh</dt>
                    <dd class="text-ink">{{ $dispensasi->dicetakOleh?->name ?? '-' }}</dd>
                </div>
            </dl>
            <a href="{{ route('dispensasi.surat.unduh', $dispensasi) }}" class="btn btn-sm btn-outline w-full justify-center">
                <i class="fas fa-download"></i> Unduh Surat
            </a>
        </div>
        @endif
    </div>
</div>
@endsection