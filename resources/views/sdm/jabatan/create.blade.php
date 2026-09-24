@extends('layouts.app')

@section('title', 'Tambah Jabatan')
@section('page-title', 'Tambah Jabatan')

@section('content')
    <div class="mb-8">
        <a href="{{ route('sdm.jabatan.index') }}" class="text-sm text-ink-soft hover:text-primary">
            <i class="fas fa-arrow-left"></i> Kembali ke Jabatan
        </a>
        <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1 mt-3">Admin SDM</p>
        <h1 class="font-display text-3xl text-ink">Tambah Jabatan</h1>
    </div>

    <form method="POST" action="{{ route('sdm.jabatan.store') }}" class="card p-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="field-label">Nama Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}" class="field-input" placeholder="mis. Asisten Direktur" required>
                @error('nama') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label">Kode <span class="text-red-500">*</span></label>
                <input type="text" name="kode" value="{{ old('kode') }}" class="field-input mono-data" placeholder="mis. asisten_direktur" required>
                @error('kode') <p class="field-error">{{ $message }}</p> @enderror
                <p class="text-xs text-ink-soft mt-1.5">Dipakai sebagai referensi internal sistem — huruf kecil, tanpa spasi (pakai garis bawah).</p>
            </div>

            <div>
                <label class="field-label">Urutan</label>
                <input type="number" name="level_urutan" min="0" value="{{ old('level_urutan', 0) }}" class="field-input">
                @error('level_urutan') <p class="field-error">{{ $message }}</p> @enderror
                <p class="text-xs text-ink-soft mt-1.5">Cuma untuk urutan tampilan di dropdown (semakin besar semakin tinggi jabatannya) — tidak menentukan alur approval.</p>
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6 pt-5 border-t border-line">
            <a href="{{ route('sdm.jabatan.index') }}" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan</button>
        </div>
    </form>
@endsection