@extends('layouts.app')
@section('title', 'Edit Pegawai')
@section('page-title', 'Edit Pegawai')

@section('content')
<div class="mb-8">
    <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin SDM</p>
    <h1 class="font-display text-3xl text-ink">Edit Pegawai — {{ $pegawai->nama_pegawai }}</h1>
</div>

<form method="POST" action="{{ route('sdm.pegawai.update', $pegawai) }}" class="card p-6 max-w-3xl">
    @csrf
    @method('PUT')

    <div class="grid md:grid-cols-2 gap-5 mb-5">
        <div>
            <label class="field-label" for="nik">NIK</label>
            <input type="text" id="nik" name="nik" class="field-input"
                   value="{{ old('nik', $pegawai->nik) }}" required maxlength="20">
            @error('nik') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="field-label" for="nama_pegawai">Nama Pegawai</label>
            <input type="text" id="nama_pegawai" name="nama_pegawai" class="field-input"
                   value="{{ old('nama_pegawai', $pegawai->nama_pegawai) }}" required maxlength="100">
            @error('nama_pegawai') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-5 mb-5">
        <div>
            <label class="field-label" for="jabatan_id">Jabatan</label>
            <select id="jabatan_id" name="jabatan_id" class="field-input" required>
                <option value="">— Pilih Jabatan —</option>
                @foreach ($jabatans as $j)
                <option value="{{ $j->id }}" @selected((int) old('jabatan_id', $pegawai->jabatan_id) === $j->id)>{{ $j->nama }}</option>
                @endforeach
            </select>
            @error('jabatan_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="field-label" for="unit_organisasi_id">Unit Organisasi</label>
            <select id="unit_organisasi_id" name="unit_organisasi_id" class="field-input" required>
                <option value="">— Pilih Unit —</option>
                @foreach ($unitOrganisasis->groupBy('tingkat') as $tingkat => $unitSekelompok)
                <optgroup label="{{ ucfirst($tingkat) }}">
                    @foreach ($unitSekelompok as $u)
                    <option value="{{ $u->id }}" @selected((int) old('unit_organisasi_id', $pegawai->unit_organisasi_id) === $u->id)>{{ $u->nama }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </select>
            @error('unit_organisasi_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-5 mb-5">
        <div>
            <label class="field-label" for="no_telepon">No. Telepon <span class="text-ink-soft font-normal">(opsional)</span></label>
            <input type="text" id="no_telepon" name="no_telepon" class="field-input"
                   value="{{ old('no_telepon', $pegawai->no_telepon) }}" maxlength="20">
            @error('no_telepon') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="field-label" for="email">Email <span class="text-ink-soft font-normal">(opsional)</span></label>
            <input type="email" id="email" name="email" class="field-input"
                   value="{{ old('email', $pegawai->email) }}" maxlength="100">
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mb-6">
        <label class="field-label" for="status">Status</label>
        <select id="status" name="status" class="field-input" style="max-width:220px" required>
            @foreach (['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'] as $val => $label)
            <option value="{{ $val }}" @selected(old('status', $pegawai->status) === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('sdm.pegawai.index') }}" class="btn btn-outline">Batal</a>
    </div>
</form>
@endsection