@extends('layouts.app')
@section('title', 'Kelola Pengguna')
@section('page-title', 'Kelola Pengguna')

@section('content')
<div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
    <div>
        <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin SDM</p>
        <h1 class="font-display text-3xl text-ink">Kelola Pengguna</h1>
    </div>
    <a href="{{ route('sdm.pengguna.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Pengguna
    </a>
</div>

@php
    $roleLabels = [
        'admin_sdm'        => 'Admin SDM',
        'admin_departemen' => 'Admin Departemen',
        'approver'         => 'Approver (Penyetuju)',
    ];
@endphp

{{-- Filter --}}
<form method="GET" class="card p-4 mb-6 flex gap-3 flex-wrap items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="text-xs text-ink-soft mb-1 block">Cari (Nama / Email)</label>
        <input type="text" name="search" value="{{ $search }}" class="field-input" placeholder="Ketik nama atau email...">
    </div>
    <div class="flex-1 min-w-[200px]">
        <label class="text-xs text-ink-soft mb-1 block">Role</label>
        <select name="role" class="field-input">
            <option value="">Semua Role</option>
            @foreach ($roleLabels as $val => $label)
            <option value="{{ $val }}" @selected($role === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="text-xs text-ink-soft mb-1 block">Jabatan</label>
        <select name="jabatan_id" class="field-input">
            <option value="">Semua Jabatan</option>
            @foreach ($jabatans as $j)
            <option value="{{ $j->id }}" @selected($jabatanId == $j->id)>{{ $j->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="text-xs text-ink-soft mb-1 block">Unit Organisasi</label>
        <select name="unit_organisasi_id" class="field-input">
            <option value="">Semua Unit</option>
            @foreach ($unitOrganisasis as $u)
            <option value="{{ $u->id }}" @selected($unitOrganisasiId == $u->id)>{{ $u->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[140px]">
        <label class="text-xs text-ink-soft mb-1 block">Status</label>
        <select name="status" class="field-input">
            <option value="">Semua Status</option>
            <option value="aktif" @selected($status === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
        </select>
    </div>
    <button class="btn btn-primary">Terapkan</button>
    @if ($search || $role || $jabatanId || $unitOrganisasiId || $status)
    <a href="{{ route('sdm.pengguna.index') }}" class="btn btn-outline">Reset</a>
    @endif
</form>

<div class="table-scroll-wrapper">
    <table class="table-pro">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Jabatan / Unit</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $u)
            <tr>
                <td class="font-medium">{{ $u->name }}</td>
                <td class="text-ink-soft">{{ $u->email }}</td>
                <td>{{ $roleLabels[$u->role] ?? $u->role }}</td>
                <td class="text-ink-soft">{{ $u->jabatanLengkap() }}</td>
                <td>
                    <span class="badge {{ $u->is_active ? 'badge-disetujui' : 'badge-default' }}">
                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </td>
                <td class="text-right whitespace-nowrap">
                    <a href="{{ route('sdm.pengguna.edit', $u) }}" class="btn btn-sm btn-outline">
                        <i class="fas fa-pen"></i>
                    </a>
                    @if ($u->is_active)
                    <div class="inline-block" x-data="{ confirmOpen: false }">
                        <form id="nonaktifkan-pengguna-{{ $u->id }}" method="POST" action="{{ route('sdm.pengguna.destroy', $u) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>

                        <button type="button" @click="confirmOpen = true" class="btn btn-sm btn-danger">
                            <i class="fas fa-ban"></i>
                        </button>

                        {{-- Diteleport ke <body> — sama seperti modal logout di layout,
                             supaya tidak ikut kepotong/salah posisi kalau nanti dipakai
                             di dalam elemen yang ada transform/overflow (mis. wrapper tabel). --}}
                        <template x-teleport="body">
                            <div x-show="confirmOpen" x-cloak
                                 x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                 class="fixed inset-0 z-[300] flex items-center justify-center p-4"
                                 style="background: rgba(15,23,42,.48); backdrop-filter: blur(2px);">
                                <div @click.outside="confirmOpen = false"
                                     x-show="confirmOpen"
                                     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                     class="card w-full max-w-sm p-6 text-center">
                                    <div class="h-12 w-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: #FCEACB; color:#9A6011;">
                                        <i class="fas fa-user-slash"></i>
                                    </div>
                                    <h3 class="font-bold text-ink mb-1">Nonaktifkan {{ $u->name }}?</h3>
                                    <p class="text-sm text-ink-soft mb-5">Akun ini tidak akan bisa login sampai diaktifkan kembali oleh Admin SDM.</p>
                                    <div class="flex gap-2 justify-center">
                                        <button type="button" @click="confirmOpen = false" class="btn btn-outline">Batal</button>
                                        <button type="submit" form="nonaktifkan-pengguna-{{ $u->id }}" class="btn" style="background:#C8862B; color:#fff;" onmouseover="this.style.background='#a56c1f'" onmouseout="this.style.background='#C8862B'">Ya, Nonaktifkan</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center text-ink-soft py-12">
                    <i class="fas fa-magnifying-glass text-2xl mb-2 block"></i>
                    Tidak ada pengguna yang cocok dengan filter/pencarian.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($users->isNotEmpty())
<div class="flex items-center justify-between flex-wrap gap-3 mt-3">
    <p class="text-xs text-ink-soft">
        Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }}
        dari {{ $users->total() }} pengguna.
    </p>
    {{ $users->links() }}
</div>
@endif
@endsection