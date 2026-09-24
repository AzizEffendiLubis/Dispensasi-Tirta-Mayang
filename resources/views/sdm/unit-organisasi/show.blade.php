@extends('layouts.app')

@section('title', $unitOrganisasi->nama)
@section('page-title', 'Detail Unit Organisasi')

@section('content')
    @php
        $badgeTingkat = ['divisi' => 'badge-default', 'departemen' => 'badge-disetujui', 'subdepartemen' => 'badge-menunggu'];
        $statUnitIni = $statistik[$unitOrganisasi->id] ?? null;
    @endphp

    <div class="mb-6">
        <a href="{{ route('sdm.unit-organisasi.index') }}" class="text-sm text-ink-soft hover:text-primary">
            <i class="fas fa-arrow-left"></i> Kembali ke Unit Organisasi
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-2">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="font-display text-2xl font-semibold text-ink">{{ $unitOrganisasi->nama }}</h1>
                    <span class="badge {{ $badgeTingkat[$unitOrganisasi->tingkat] ?? 'badge-default' }}">
                        {{ $unitOrganisasi->labelTingkat() }}
                    </span>
                </div>
                <p class="text-sm text-ink-soft mt-1">
                    Kode: <span class="mono-data">{{ $unitOrganisasi->kode ?? '-' }}</span>
                    &middot; Induk:
                    @if ($unitOrganisasi->parent)
                        <a href="{{ route('sdm.unit-organisasi.show', $unitOrganisasi->parent->id) }}" class="text-primary hover:underline">
                            {{ $unitOrganisasi->parent->nama }}
                        </a>
                    @else
                        <span class="italic">Mandiri (langsung ke Direktur)</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('sdm.unit-organisasi.edit', $unitOrganisasi) }}" class="btn btn-outline">
                <i class="fas fa-pen"></i> Edit Unit
            </a>
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card p-4">
            <p class="text-xs text-ink-soft font-medium">Pegawai Aktif</p>
            <p class="text-2xl font-bold text-ink mt-1">{{ $statUnitIni['pegawai_aktif'] ?? 0 }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-ink-soft font-medium">Unit Anak</p>
            <p class="text-2xl font-bold text-ink mt-1">{{ $unitOrganisasi->children->count() }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-ink-soft font-medium">Dispensasi Tahun Ini</p>
            <p class="text-2xl font-bold text-ink mt-1">{{ $statistikDispensasi['total_tahun_ini'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-ink-soft font-medium">Bulan Ini</p>
            <p class="text-2xl font-bold text-ink mt-1">{{ $statistikDispensasi['bulan_ini'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="card p-4">
            <p class="text-xs text-ink-soft font-medium mb-1">Menunggu Persetujuan</p>
            <p class="text-xl font-bold" style="color:#9A6011;">{{ $statistikDispensasi['per_status']['menunggu_persetujuan'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-ink-soft font-medium mb-1">Disetujui</p>
            <p class="text-xl font-bold" style="color:#007529;">{{ $statistikDispensasi['per_status']['disetujui'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-ink-soft font-medium mb-1">Ditolak</p>
            <p class="text-xl font-bold" style="color:#A3392C;">{{ $statistikDispensasi['per_status']['ditolak'] }}</p>
        </div>
    </div>

    {{-- Unit anak --}}
    @if ($unitOrganisasi->children->isNotEmpty())
        <div class="card mb-6">
            <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                <h2 class="font-semibold text-ink">Unit di Bawahnya</h2>
                <a href="{{ route('sdm.unit-organisasi.create', ['tingkat' => 'departemen', 'parent_id' => $unitOrganisasi->id]) }}" class="text-xs text-primary hover:underline font-semibold">
                    <i class="fas fa-plus"></i> Tambah unit anak
                </a>
            </div>
            <div class="table-scroll-wrapper" style="border:none; border-radius:0;">
                <table class="table-pro">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Kode</th>
                            <th>Tingkat</th>
                            <th>Pegawai Aktif</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unitOrganisasi->children as $anak)
                            @php $statAnak = $statistik[$anak->id] ?? null; @endphp
                            <tr>
                                <td class="font-semibold text-ink">{{ $anak->nama }}</td>
                                <td class="mono-data">{{ $anak->kode ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $badgeTingkat[$anak->tingkat] ?? 'badge-default' }}">
                                        {{ $anak->labelTingkat() }}
                                    </span>
                                </td>
                                <td class="text-ink-soft">{{ $statAnak['pegawai_aktif'] ?? 0 }}</td>
                                <td class="text-right">
                                    <a href="{{ route('sdm.unit-organisasi.show', $anak->id) }}" class="btn btn-outline btn-sm">Lihat</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card p-5 mb-6 text-sm text-ink-soft">
            Unit ini belum punya unit anak.
            <a href="{{ route('sdm.unit-organisasi.create', ['parent_id' => $unitOrganisasi->id]) }}" class="text-primary font-semibold hover:underline">Tambah sekarang</a>.
        </div>
    @endif

    {{-- Pejabat / akun di unit ini --}}
    @if ($unitOrganisasi->users->isNotEmpty())
        <div class="card mb-6">
            <div class="px-5 py-4 border-b border-line">
                <h2 class="font-semibold text-ink">Akun di Unit Ini</h2>
            </div>
            <div class="table-scroll-wrapper" style="border:none; border-radius:0;">
                <table class="table-pro">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>Jabatan</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($unitOrganisasi->users as $user)
                            <tr>
                                <td class="font-semibold text-ink">{{ $user->name }} @if ($user->is_plt) <span class="badge badge-menunggu">Plt</span> @endif</td>
                                <td class="text-ink-soft capitalize">{{ str_replace('_', ' ', $user->role) }}</td>
                                <td class="text-ink-soft">{{ $user->jabatan?->nama ?? '-' }}</td>
                                <td class="text-ink-soft">{{ $user->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Pegawai di unit ini --}}
    <div class="card">
        <div class="px-5 py-4 border-b border-line flex items-center justify-between">
            <h2 class="font-semibold text-ink">Pegawai Aktif di Unit Ini</h2>
            <a href="{{ route('sdm.pegawai.index', ['unit_organisasi_id' => $unitOrganisasi->id]) }}" class="text-xs text-primary hover:underline font-semibold">
                Lihat semua di Kelola Pegawai
            </a>
        </div>
        <p class="px-5 pt-3 text-xs text-ink-soft italic">Hanya pegawai yang tercatat langsung di unit ini — tidak termasuk pegawai di sub-unit di bawahnya.</p>
        <div class="table-scroll-wrapper" style="border:none; border-radius:0;">
            <table class="table-pro">
                <thead>
                    <tr>
                        <th>NIK</th>
                        <th>Nama Pegawai</th>
                        <th>Jabatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unitOrganisasi->pegawais as $pegawai)
                        <tr>
                            <td class="mono-data">{{ $pegawai->nik }}</td>
                            <td class="font-semibold text-ink">{{ $pegawai->nama_pegawai }}</td>
                            <td class="text-ink-soft">{{ $pegawai->jabatan?->nama ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-ink-soft py-8">Belum ada pegawai aktif tercatat langsung di unit ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection