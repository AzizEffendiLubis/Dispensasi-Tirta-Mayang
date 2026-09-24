@extends('layouts.app')
@section('title', 'Detail Pengajuan — ' . $dispensasi->nomor_dispensasi)
@section('page-title', 'Detail Pengajuan')

@section('content')
@php
    $kelompokTampil = $kelompokPengajuan->isNotEmpty() ? $kelompokPengajuan : collect([$dispensasi]);
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

<div class="mb-8">
    <a href="{{ route('approval.index') }}" class="text-xs text-accent font-semibold mb-2 inline-flex items-center gap-1">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Persetujuan
    </a>
    <div class="flex items-center gap-3 flex-wrap mt-2">
        <h1 class="font-display text-3xl text-ink">{{ $dispensasi->nomor_dispensasi }}</h1>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
        @if ($kelompokTampil->count() > 1)
        <span class="badge badge-default">{{ $kelompokTampil->count() }} waktu dalam satu pengajuan</span>
        @endif
    </div>
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

    {{-- Aksi / status keputusan --}}
    <div class="lg:col-span-1">
        <div class="card p-6">
            @if ($dispensasi->status_pengajuan === 'menunggu_persetujuan')
            <h3 class="font-semibold text-ink mb-1">Keputusan</h3>
            @if ($kelompokTampil->count() > 1)
            <p class="text-xs text-ink-soft mb-4">
                Keputusan ini berlaku untuk seluruh {{ $kelompokTampil->count() }} waktu di atas sekaligus.
            </p>
            @else
            <div class="mb-4"></div>
            @endif

            <div class="mb-4" x-data="{ confirmOpen: false }">
                <form id="approve-form" method="POST" action="{{ route('approval.approve', $dispensasi) }}">
                    @csrf
                    <label class="field-label" for="catatan_approve">Catatan <span class="text-ink-soft font-normal">(opsional)</span></label>
                    <textarea id="catatan_approve" name="catatan" rows="3" class="field-input">{{ old('catatan') }}</textarea>
                </form>
                <button type="button" @click="confirmOpen = true" class="btn btn-primary w-full mt-3">
                    <i class="fas fa-check"></i> Setujui
                </button>

                <template x-teleport="body">
                    <div x-show="confirmOpen" x-cloak
                         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         class="fixed inset-0 z-[300] flex items-center justify-center p-4"
                         style="background: rgba(15,23,42,.48); backdrop-filter: blur(2px);">
                        <div @click.outside="confirmOpen = false"
                             x-show="confirmOpen"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="card w-full max-w-sm p-6 text-center">
                            <div class="h-12 w-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: #D7F5E0; color:#007529;">
                                <i class="fas fa-check"></i>
                            </div>
                            <h3 class="font-bold text-ink mb-1">Setujui {{ $dispensasi->nomor_dispensasi }}?</h3>
                            <p class="text-sm text-ink-soft mb-5">
                                @if ($kelompokTampil->count() > 1)
                                Seluruh {{ $kelompokTampil->count() }} waktu dalam pengajuan ini akan ikut disetujui dan pemohon akan diberi tahu.
                                @else
                                Keputusan ini akan langsung tercatat dan pemohon akan diberi tahu.
                                @endif
                            </p>
                            <div class="flex gap-2 justify-center">
                                <button type="button" @click="confirmOpen = false" class="btn btn-outline">Batal</button>
                                <button type="submit" form="approve-form" class="btn btn-primary">Ya, Setujui</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-data="{ confirmOpen: false }">
                <form id="reject-form" method="POST" action="{{ route('approval.reject', $dispensasi) }}">
                    @csrf
                    <label class="field-label" for="catatan_reject">Alasan Penolakan</label>
                    <textarea id="catatan_reject" name="catatan" rows="3" class="field-input" required minlength="5">{{ old('catatan') }}</textarea>
                    @error('catatan') <p class="field-error">{{ $message }}</p> @enderror
                </form>
                <button type="button" @click="confirmOpen = true" class="btn btn-danger w-full mt-3">
                    <i class="fas fa-xmark"></i> Tolak
                </button>

                <template x-teleport="body">
                    <div x-show="confirmOpen" x-cloak
                         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         class="fixed inset-0 z-[300] flex items-center justify-center p-4"
                         style="background: rgba(15,23,42,.48); backdrop-filter: blur(2px);">
                        <div @click.outside="confirmOpen = false"
                             x-show="confirmOpen"
                             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             class="card w-full max-w-sm p-6 text-center">
                            <div class="h-12 w-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: #FBE7E4; color:#C1483A;">
                                <i class="fas fa-xmark"></i>
                            </div>
                            <h3 class="font-bold text-ink mb-1">Tolak {{ $dispensasi->nomor_dispensasi }}?</h3>
                            <p class="text-sm text-ink-soft mb-5">
                                @if ($kelompokTampil->count() > 1)
                                Seluruh {{ $kelompokTampil->count() }} waktu dalam pengajuan ini akan ikut ditolak dan pemohon akan diberi tahu.
                                @else
                                Keputusan ini akan langsung tercatat dan pemohon akan diberi tahu.
                                @endif
                            </p>
                            <div class="flex gap-2 justify-center">
                                <button type="button" @click="confirmOpen = false" class="btn btn-outline">Batal</button>
                                <button type="submit" form="reject-form" class="btn btn-danger">Ya, Tolak</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            @else
            <h3 class="font-semibold text-ink mb-4">Keputusan</h3>
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
    </div>
</div>
@endsection