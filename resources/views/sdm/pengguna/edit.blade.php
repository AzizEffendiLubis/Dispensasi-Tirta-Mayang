@extends('layouts.app')
@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna')

@section('content')
<div class="mb-8">
    <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin SDM</p>
    <h1 class="font-display text-3xl text-ink">Edit Pengguna — {{ $user->name }}</h1>
</div>

@php
    $roleLabels = [
        'admin_sdm'        => 'Admin SDM',
        'admin_departemen' => 'Admin Departemen',
        'approver'         => 'Approver (Penyetuju)',
    ];
@endphp

<form method="POST" action="{{ route('sdm.pengguna.update', $user) }}" class="card p-6 max-w-3xl"
      x-data="{ role: '{{ old('role', $user->role) }}' }">
    @csrf
    @method('PUT')

    <div class="grid md:grid-cols-2 gap-5 mb-5">
        <div>
            <label class="field-label" for="name">Nama</label>
            <input type="text" id="name" name="name" class="field-input"
                   value="{{ old('name', $user->name) }}" required maxlength="100">
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="field-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="field-input"
                   value="{{ old('email', $user->email) }}" required maxlength="100">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-5 mb-5">
        <div>
            <label class="field-label" for="password">Password <span class="text-ink-soft font-normal">(kosongkan jika tidak diubah)</span></label>
            <input type="password" id="password" name="password" class="field-input" minlength="8">
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="field-label" for="password_confirmation">Konfirmasi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="field-input" minlength="8">
        </div>
    </div>

    <div class="mb-5">
        <label class="field-label" for="role">Role</label>
        <select id="role" name="role" class="field-input" x-model="role" required>
            <option value="">— Pilih Role —</option>
            @foreach ($roleLabels as $val => $label)
            <option value="{{ $val }}" @selected(old('role', $user->role) === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid md:grid-cols-2 gap-5 mb-5">
        <div x-show="role === 'approver'" x-cloak>
            <label class="field-label" for="jabatan_id">Jabatan</label>
            <select id="jabatan_id" name="jabatan_id" class="field-input">
                <option value="">— Pilih Jabatan —</option>
                @foreach ($jabatans as $j)
                <option value="{{ $j->id }}" @selected((int) old('jabatan_id', $user->jabatan_id) === $j->id)>{{ $j->nama }}</option>
                @endforeach
            </select>
            @error('jabatan_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div x-show="role === 'admin_departemen' || role === 'approver'" x-cloak>
            <label class="field-label" for="unit_organisasi_id">Unit Organisasi</label>
            <select id="unit_organisasi_id" name="unit_organisasi_id" class="field-input">
                <option value="">— Pilih Unit —</option>
                @foreach ($unitOrganisasis->groupBy('tingkat') as $tingkat => $unitSekelompok)
                <optgroup label="{{ ucfirst($tingkat) }}">
                    @foreach ($unitSekelompok as $u)
                    <option value="{{ $u->id }}" @selected((int) old('unit_organisasi_id', $user->unit_organisasi_id) === $u->id)>{{ $u->nama }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
            <p class="text-xs text-ink-soft mt-1">
                Wajib untuk Admin Departemen. Untuk Approver boleh dikosongkan
                hanya jika jabatannya lintas-unit (mis. Direktur).
            </p>
            @error('unit_organisasi_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mb-5" x-show="role === 'approver'" x-cloak>
        <div class="flex items-center gap-2">
            <input type="hidden" name="is_plt" value="0">
            <input type="checkbox" id="is_plt" name="is_plt" value="1"
                   @checked(old('is_plt', $user->is_plt)) class="h-4 w-4">
            <label for="is_plt" class="text-sm text-ink">Sedang menjabat sebagai Plt (Pelaksana Tugas)</label>
        </div>
        <p class="text-xs text-ink-soft mt-1">
            Jika dicentang, jabatan orang ini akan tertulis "Plt. ..." pada surat E-Dispensasi dan tempat lain yang menampilkan jabatannya.
        </p>
        @error('is_plt') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div class="mb-6 flex items-center gap-2">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1"
               @checked(old('is_active', $user->is_active)) class="h-4 w-4">
        <label for="is_active" class="text-sm text-ink">Akun aktif (bisa login)</label>
    </div>

    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('sdm.pengguna.index') }}" class="btn btn-outline">Batal</a>
    </div>
</form>
@endsection