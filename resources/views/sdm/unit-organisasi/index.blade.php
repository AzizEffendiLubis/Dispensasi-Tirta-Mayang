@extends('layouts.app')

@section('title', 'Unit Organisasi')
@section('page-title', 'Unit Organisasi')

@section('content')
    @php
        $labelTingkat = ['divisi' => 'Divisi', 'departemen' => 'Departemen', 'subdepartemen' => 'Sub Departemen'];
        $badgeTingkat = ['divisi' => 'badge-default', 'departemen' => 'badge-disetujui', 'subdepartemen' => 'badge-menunggu'];
    @endphp

    <div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
        <div>
            <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin SDM</p>
            <h1 class="font-display text-3xl text-ink">Unit Organisasi</h1>
            <p class="text-sm text-ink-soft mt-1">Kelola struktur Divisi, Departemen, dan Sub Departemen.</p>
        </div>
        <a href="{{ route('sdm.unit-organisasi.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Unit
        </a>
    </div>

    <form method="GET" action="{{ route('sdm.unit-organisasi.index') }}" class="card p-4 mb-6 flex gap-3 flex-wrap items-end">
        <div class="flex-1 min-w-[220px]">
            <label class="text-xs text-ink-soft mb-1 block">Cari</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Nama atau kode unit..." class="field-input">
        </div>
        <div class="flex-1 min-w-[160px]">
            <label class="text-xs text-ink-soft mb-1 block">Tingkat</label>
            <select name="tingkat" class="field-input">
                <option value="">Semua Tingkat</option>
                @foreach ($labelTingkat as $value => $label)
                    <option value="{{ $value }}" @selected($tingkat === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[160px]">
            <label class="text-xs text-ink-soft mb-1 block">Status</label>
            <select name="status" class="field-input">
                <option value="">Semua Status</option>
                <option value="aktif" @selected($status === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
            </select>
        </div>
        <button class="btn btn-primary">Terapkan Filter</button>
        @if ($search || $tingkat || $parentId || $status)
            <a href="{{ route('sdm.unit-organisasi.index') }}" class="btn btn-outline">Reset</a>
        @endif
    </form>

    <div class="card">
        <div class="table-scroll-wrapper" style="border:none; border-radius:0;">
            <table class="table-pro">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th>Tingkat</th>
                        <th>Induk</th>
                        <th>Status</th>
                        <th>Anak Unit</th>
                        <th>Pegawai Aktif</th>
                        <th>Admin / Approver</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unitOrganisasis as $unit)
                        @php $stat = $statistik[$unit->id] ?? null; @endphp
                        <tr class="{{ $unit->is_active ? '' : 'opacity-60' }}">
                            <td class="font-semibold text-ink">
                                <a href="{{ route('sdm.unit-organisasi.show', $unit->id) }}" class="hover:text-primary hover:underline">
                                    {{ $unit->nama }}
                                </a>
                            </td>
                            <td class="mono-data">{{ $unit->kode ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $badgeTingkat[$unit->tingkat] ?? 'badge-default' }}">
                                    {{ $unit->labelTingkat() }}
                                </span>
                            </td>
                            <td class="text-ink-soft">
                                @if ($unit->parent)
                                    <a href="{{ route('sdm.unit-organisasi.show', $unit->parent->id) }}" class="hover:text-primary hover:underline">
                                        {{ $unit->parent->nama }}
                                    </a>
                                @else
                                    <span class="italic">Mandiri</span>
                                @endif
                            </td>
                            <td>
                                @if ($unit->is_active)
                                    <span class="badge badge-disetujui">Aktif</span>
                                @else
                                    <span class="badge badge-ditolak">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-ink-soft">{{ $unit->children_count }}</td>
                            <td class="text-ink-soft">{{ $stat['pegawai_aktif'] ?? 0 }}</td>
                            <td class="text-ink-soft">{{ $stat['total_admin'] ?? 0 }} / {{ $stat['total_approver'] ?? 0 }}</td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('sdm.unit-organisasi.edit', $unit) }}" class="btn btn-outline btn-sm" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>

                                @if ($unit->is_active)
                                    {{-- Tombol Nonaktifkan --}}
                                    <div class="inline-block" x-data="{ confirmOpen: false }">
                                        <button type="button" @click="confirmOpen = true" class="btn btn-outline btn-sm" title="Nonaktifkan" style="color:#C1483A;">
                                            <i class="fas fa-power-off"></i>
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
                                                        <i class="fas fa-power-off"></i>
                                                    </div>
                                                    <h3 class="font-bold text-ink mb-1">Nonaktifkan {{ $unit->nama }}?</h3>
                                                    <p class="text-sm text-ink-soft mb-5">Unit ini akan ditandai nonaktif. Anda bisa mengaktifkannya kembali kapan saja lewat tombol Aktifkan di daftar ini.</p>
                                                    <div class="flex gap-2 justify-center">
                                                        <button type="button" @click="confirmOpen = false" class="btn btn-outline">Batal</button>
                                                        <button type="submit" form="nonaktifkan-unit-{{ $unit->id }}" class="btn btn-danger">Ya, Nonaktifkan</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <form id="nonaktifkan-unit-{{ $unit->id }}" action="{{ route('sdm.unit-organisasi.destroy', $unit) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @else
                                    {{-- Tombol Aktifkan --}}
                                    <div class="inline-block" x-data="{ confirmOpen: false }">
                                        <button type="button" @click="confirmOpen = true" class="btn btn-outline btn-sm" title="Aktifkan" style="color:#007529;">
                                            <i class="fas fa-rotate-left"></i>
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
                                                    <div class="h-12 w-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: #E1F7E7; color:#007529;">
                                                        <i class="fas fa-rotate-left"></i>
                                                    </div>
                                                    <h3 class="font-bold text-ink mb-1">Aktifkan kembali {{ $unit->nama }}?</h3>
                                                    <p class="text-sm text-ink-soft mb-5">Unit ini akan aktif kembali dan muncul di daftar unit aktif, pemilihan induk, serta pilihan lain di seluruh sistem.</p>
                                                    <div class="flex gap-2 justify-center">
                                                        <button type="button" @click="confirmOpen = false" class="btn btn-outline">Batal</button>
                                                        <button type="submit" form="aktifkan-unit-{{ $unit->id }}" class="btn btn-primary">Ya, Aktifkan</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    <form id="aktifkan-unit-{{ $unit->id }}" action="{{ route('sdm.unit-organisasi.aktifkan', $unit) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('PATCH')
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-ink-soft py-10">
                                Belum ada unit organisasi yang cocok dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection