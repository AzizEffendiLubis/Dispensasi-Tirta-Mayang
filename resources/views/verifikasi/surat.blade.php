@extends('layouts.app')

@section('title', 'Verifikasi Surat E-Dispensasi')

@section('content')
<div class="max-w-2xl mx-auto py-6">

    <div class="text-center mb-6">
        <span class="badge badge-disetujui inline-flex items-center gap-1.5 px-3 py-1.5 mb-3">
            <i class="fas fa-circle-check"></i> Surat Terverifikasi Sah
        </span>
        <h1 class="font-display text-2xl text-ink">Verifikasi Surat E-Dispensasi</h1>
        <p class="text-sm text-ink-soft mt-1">Dokumen ini diterbitkan resmi melalui Sistem Dispensasi Pegawai.</p>
    </div>

    <div class="card p-6 space-y-6">

        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-soft mb-4">Data Surat</h3>
            <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Nomor Surat</dt>
                    <dd class="mono-data text-ink">{{ $dispensasi->nomor_surat_dispensasi }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Tanggal Surat</dt>
                    <dd class="text-ink">{{ \Illuminate\Support\Carbon::parse($dispensasi->tanggal_surat_dispensasi)->translatedFormat('d F Y') }}</dd>
                </div>
            </dl>
        </div>

        <div class="border-t border-gray-100 pt-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-soft mb-4">
                Data Pegawai ({{ $barisPegawai->count() }} orang)
            </h3>
            <dl class="grid sm:grid-cols-2 gap-4 text-sm mb-4">
                <div class="sm:col-span-2">
                    <dt class="text-xs text-ink-soft mb-0.5">Unit Organisasi</dt>
                    <dd class="text-ink">
                        @if ($unitBarisUtama)
                            {{ $unitBarisUtama->labelTingkat() }} {{ $unitBarisUtama->nama }}
                        @else
                            -
                        @endif
                    </dd>
                </div>
            </dl>
            <div class="table-scroll-wrapper">
                <table class="table-pro">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>NIK</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($barisPegawai as $baris)
                        <tr>
                            <td class="font-medium">{{ $baris->pegawai->nama_pegawai }}</td>
                            <td class="mono-data text-ink-soft">{{ $baris->pegawai->nik }}</td>
                            <td class="text-ink-soft">
                                @foreach ($baris->waktu as $w)
                                <span class="badge badge-default">{{ $w }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-soft mb-4">Data Persetujuan</h3>
            <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Tanggal Dispensasi</dt>
                    <dd class="text-ink">{{ $dispensasi->tanggal_dispensasi->translatedFormat('d F Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Waktu Disetujui</dt>
                    <dd class="text-ink">{{ $waktuList->implode(', ') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-ink-soft mb-0.5">Disetujui Oleh</dt>
                    <dd class="text-ink">
                        {{ $dispensasi->diprosesOleh?->name ?? '-' }}
                        <span class="text-ink-soft">— {{ $dispensasi->diprosesOleh?->jabatanLengkap() }}</span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="border-t border-gray-100 pt-6">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-ink-soft mb-4">Data Penerbitan</h3>
            <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Diterbitkan Oleh</dt>
                    <dd class="text-ink">{{ $dispensasi->dicetakOleh?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-ink-soft mb-0.5">Diterbitkan Pada</dt>
                    <dd class="text-ink">{{ $dispensasi->dicetak_pada?->translatedFormat('d F Y, H:i') }} WIB</dd>
                </div>
            </dl>
        </div>

    </div>

    <p class="text-center text-xs text-ink-soft mt-4">
        Jika ada kejanggalan pada data di atas, hubungi bagian SDM perusahaan.
    </p>

</div>
@endsection