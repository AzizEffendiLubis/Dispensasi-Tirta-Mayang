@extends('layouts.app')

@section('title', 'Tambah Unit Organisasi')
@section('page-title', 'Tambah Unit Organisasi')

@section('content')
    @php
        $tingkatSaatIni = old('tingkat', $tingkatDiminta ?? 'divisi');
        $parentIdSaatIni = old('parent_id', $parentIdTerpilih ?? null);
    @endphp

    <div class="mb-6">
        <a href="{{ route('sdm.unit-organisasi.index') }}" class="text-sm text-ink-soft hover:text-primary">
            <i class="fas fa-arrow-left"></i> Kembali ke Unit Organisasi
        </a>
        <h1 class="font-display text-2xl font-semibold text-ink mt-2">Tambah Unit Organisasi</h1>
    </div>

    <form method="POST" action="{{ route('sdm.unit-organisasi.store') }}" class="card p-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="field-label">Tingkat <span class="text-red-500">*</span></label>
                <select name="tingkat" id="tingkat" class="field-input" required>
                    <option value="divisi" @selected($tingkatSaatIni === 'divisi')>Divisi</option>
                    <option value="departemen" @selected($tingkatSaatIni === 'departemen')>Departemen</option>
                    <option value="subdepartemen" @selected($tingkatSaatIni === 'subdepartemen')>Sub Departemen</option>
                </select>
                @error('tingkat') <p class="field-error">{{ $message }}</p> @enderror
                <p class="text-xs text-ink-soft mt-1.5">Tingkat menentukan pilihan Induk Unit di sebelah: Divisi tidak punya induk, Departemen induknya Divisi, Sub Departemen induknya Departemen atau Divisi.</p>
            </div>

            <div>
                <label class="field-label">Induk Unit</label>
                <select name="parent_id" id="parent_id" class="field-input">
                    <option value="">— Tidak ada (mandiri) —</option>
                    @foreach ($parents as $unit)
                        <option value="{{ $unit->id }}" data-tingkat="{{ $unit->tingkat }}" @selected((string) $parentIdSaatIni === (string) $unit->id)>
                            {{ $unit->nama }} ({{ $unit->labelTingkat() }})
                        </option>
                    @endforeach
                </select>
                @error('parent_id') <p class="field-error">{{ $message }}</p> @enderror
                <p class="text-xs text-ink-soft mt-1.5">Kosongkan kalau unit ini mandiri (langsung ke Direktur).</p>
            </div>

            <div>
                <label class="field-label">Nama Unit <span class="text-red-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}"
                       class="field-input" placeholder="mis. Divisi Bisnis" required>
                @error('nama') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label">Kode</label>
                <input type="text" name="kode" value="{{ old('kode') }}"
                       class="field-input mono-data" placeholder="mis. BSN1 (opsional)">
                @error('kode') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="field-label">Urutan Tampilan</label>
                <input type="number" name="urutan" min="0" value="{{ old('urutan', 0) }}" class="field-input">
                @error('urutan') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end gap-2 mt-6 pt-5 border-t border-line">
            <a href="{{ route('sdm.unit-organisasi.index') }}" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan</button>
        </div>
    </form>

    <script>
        (function () {
            const urutanTingkat = { divisi: 1, departemen: 2, subdepartemen: 3 };
            const tingkatSelect = document.getElementById('tingkat');
            const parentSelect = document.getElementById('parent_id');

            function filterIndukUnit() {
                const tingkatSekarang = urutanTingkat[tingkatSelect.value] ?? 99;
                let opsiTerpilihMasihValid = false;

                Array.from(parentSelect.options).forEach(function (opt) {
                    if (!opt.value) {
                        return; // opsi "Tidak ada (mandiri)" selalu tampil
                    }

                    const urutanOpsi = urutanTingkat[opt.dataset.tingkat] ?? 99;
                    const valid = urutanOpsi < tingkatSekarang;

                    opt.hidden = !valid;
                    opt.disabled = !valid;

                    if (valid && opt.selected) {
                        opsiTerpilihMasihValid = true;
                    }
                });

                if (!opsiTerpilihMasihValid) {
                    parentSelect.value = '';
                }

                // Divisi berada di tingkat paling atas, tidak pernah punya induk.
                parentSelect.disabled = (tingkatSelect.value === 'divisi');
            }

            filterIndukUnit();
            tingkatSelect.addEventListener('change', filterIndukUnit);
        })();
    </script>
@endsection