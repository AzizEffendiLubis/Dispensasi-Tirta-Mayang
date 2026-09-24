@extends('layouts.app')

@section('title', 'Edit Jabatan')
@section('page-title', 'Edit Jabatan')

@section('content')
    <div class="mb-8">
        <a href="{{ route('sdm.jabatan.index') }}" class="text-sm text-ink-soft hover:text-primary">
            <i class="fas fa-arrow-left"></i> Kembali ke Jabatan
        </a>
        <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1 mt-3">Admin SDM</p>
        <h1 class="font-display text-3xl text-ink">Edit {{ $jabatan->nama }}</h1>
    </div>

    <form method="POST" action="{{ route('sdm.jabatan.update', $jabatan) }}" class="card p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="field-label">Nama Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama', $jabatan->nama) }}" class="field-input" required>
                @error('nama') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="kode" value="{{ old('kode', $jabatan->kode) }}" class="field-input mono-data" required>
                @error('kode') <p class="field-error">{{ $message }}</p> @enderror
                <p class="text-xs text-ink-soft mt-1.5">
                    Hati-hati mengubah kode ini — beberapa bagian sistem (format cetak surat, label memo) mencocokkan jabatan lewat kode tertentu
                    (mis. kode yang diawali <span class="mono-data">direktur</span>). Kalau tidak yakin, sebaiknya jangan diubah.
                </p>
            </div>

            <div>
                <label class="field-label">Urutan</label>
                <input type="number" name="level_urutan" min="0" value="{{ old('level_urutan', $jabatan->level_urutan) }}" class="field-input">
                @error('level_urutan') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6 pt-5 border-t border-line">
            <a href="{{ route('sdm.jabatan.index') }}" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan Perubahan</button>
        </div>
    </form>
@endsection