@extends('layouts.app')

@section('title', 'Jabatan')
@section('page-title', 'Jabatan')

@section('content')
    <div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
        <div>
            <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin SDM</p>
            <h1 class="font-display text-3xl text-ink">Jabatan</h1>
            <p class="text-sm text-ink-soft mt-1">
                Master posisi/jabatan yang dipakai di seluruh sistem — mulai dari Staf sampai Direktur.
            </p>
        </div>
        <a href="{{ route('sdm.jabatan.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Jabatan
        </a>
    </div>

    <form method="GET" action="{{ route('sdm.jabatan.index') }}" class="card p-4 mb-6 flex gap-3 flex-wrap items-end">
        <div class="flex-1 min-w-[220px]">
            <label class="text-xs text-ink-soft mb-1 block">Cari</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode atau nama jabatan..." class="field-input">
        </div>
        <button class="btn btn-primary">Terapkan Filter</button>
        @if ($search)
            <a href="{{ route('sdm.jabatan.index') }}" class="btn btn-outline">Reset</a>
        @endif
    </form>

    <div class="card">
        <div class="table-scroll-wrapper" style="border:none; border-radius:0;">
            <table class="table-pro">
                <thead>
                    <tr>
                        <th>Urutan</th>
                        <th>Nama Jabatan</th>
                        <th>Kode</th>
                        <th>Pegawai</th>
                        <th>Akun Pengguna</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jabatans as $jabatan)
                        <tr>
                            <td class="text-ink-soft">{{ $jabatan->level_urutan }}</td>
                            <td class="font-semibold text-ink">{{ $jabatan->nama }}</td>
                            <td class="mono-data">{{ $jabatan->kode }}</td>
                            <td class="text-ink-soft">{{ $jabatan->pegawais_count }}</td>
                            <td class="text-ink-soft">{{ $jabatan->users_count }}</td>
                            <td class="text-right whitespace-nowrap">
                                <a href="{{ route('sdm.jabatan.edit', $jabatan) }}" class="btn btn-outline btn-sm" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </a>

                                <div class="inline-block" x-data="{ confirmOpen: false }">
                                    <button type="button" @click="confirmOpen = true" class="btn btn-outline btn-sm" title="Hapus" style="color:#C1483A;">
                                        <i class="fas fa-trash"></i>
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
                                                    <i class="fas fa-trash"></i>
                                                </div>
                                                <h3 class="font-bold text-ink mb-1">Hapus {{ $jabatan->nama }}?</h3>
                                                <p class="text-sm text-ink-soft mb-5">Tindakan ini tidak bisa dibatalkan. Pastikan jabatan ini sudah tidak dipakai pegawai atau akun pengguna manapun.</p>
                                                <div class="flex gap-2 justify-center">
                                                    <button type="button" @click="confirmOpen = false" class="btn btn-outline">Batal</button>
                                                    <button type="submit" form="hapus-jabatan-{{ $jabatan->id }}" class="btn btn-danger">Ya, Hapus</button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <form id="hapus-jabatan-{{ $jabatan->id }}" action="{{ route('sdm.jabatan.destroy', $jabatan) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-ink-soft py-10">
                                Belum ada jabatan yang cocok dengan pencarian ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection