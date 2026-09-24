@extends('layouts.app')
@section('title', 'Ajukan Dispensasi')
@section('page-title', 'Ajukan Dispensasi')

@section('content')
<div class="mb-8">
    <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin Departemen</p>
    <h1 class="font-display text-3xl text-ink">Ajukan Dispensasi</h1>
</div>

<form method="POST" action="{{ route('dispensasi.store') }}" enctype="multipart/form-data" class="card p-6 max-w-2xl">
    @csrf

    <div class="mb-5">
        <label class="field-label" for="pegawai_id">Pegawai</label>
        <select id="pegawai_id" name="pegawai_id" class="field-input" required>
            <option value="">— Pilih Pegawai —</option>
            @forelse ($pegawais as $p)
            <option value="{{ $p->id }}" @selected((int) old('pegawai_id') === $p->id)>
                {{ $p->nama_pegawai }} — {{ $p->nik }}
            </option>
            @empty
            <option value="" disabled>Tidak ada pegawai aktif di departemen Anda</option>
            @endforelse
        </select>
        @error('pegawai_id') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div class="mb-5">
        <label class="field-label" for="tanggal_dispensasi">Tanggal Dispensasi</label>
        <input type="date" id="tanggal_dispensasi" name="tanggal_dispensasi" class="field-input" style="max-width:220px"
               value="{{ old('tanggal_dispensasi') }}" required>
        @error('tanggal_dispensasi') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div class="mb-6">
        <label class="field-label">Waktu Dispensasi, Keterangan &amp; Bukti</label>
        <p class="text-xs text-ink-soft mb-3">
            Centang waktu yang diajukan, lalu isi keterangan dan (opsional) unggah bukti pendukung
            masing-masing — tiap waktu akan jadi pengajuan terpisah dengan bukti sendiri-sendiri.
        </p>

        <div class="space-y-3">
            @foreach (['T', 'TBO', 'TBI', 'CP'] as $val)
            @php $dicentang = in_array($val, old('waktu_dispensasi', [])); @endphp
            <div class="border border-line rounded-lg p-3">
                <label class="inline-flex items-center gap-2 text-sm font-medium text-ink mb-2">
                    <input type="checkbox"
                           name="waktu_dispensasi[]"
                           value="{{ $val }}"
                           class="h-4 w-4 js-waktu-checkbox"
                           data-target="detail-field-{{ $val }}"
                           @checked($dicentang)>
                    {{ $val }}
                </label>

                <div id="detail-field-{{ $val }}" class="js-waktu-detail space-y-2 {{ $dicentang ? '' : 'hidden' }}">
                    <div>
                        <textarea name="keterangan[{{ $val }}]"
                                  rows="2"
                                  class="field-input"
                                  placeholder="Keterangan untuk waktu {{ $val }}">{{ old("keterangan.$val") }}</textarea>
                        @error('keterangan.' . $val) <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs text-ink-soft mb-1 block" for="bukti-field-{{ $val }}">
                            Bukti Pendukung {{ $val }}
                            <span class="font-normal">(opsional, PDF/JPG/PNG maks 2MB)</span>
                        </label>
                        <input type="file"
                               id="bukti-field-{{ $val }}"
                               name="bukti_pendukung[{{ $val }}]"
                               class="field-input js-bukti-field"
                               data-persist-key="bukti_pendukung_{{ $val }}"
                               accept=".pdf,.jpg,.jpeg,.png">
                        @error('bukti_pendukung.' . $val) <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @error('waktu_dispensasi') <p class="field-error">{{ $message }}</p> @enderror
    </div>

    <div class="flex gap-2">
        <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
        <a href="{{ route('dispensasi.index') }}" class="btn btn-outline">Batal</a>
    </div>
</form>

<script>
    document.querySelectorAll('.js-waktu-checkbox').forEach(function (checkbox) {
        var field = document.getElementById(checkbox.dataset.target);
        if (!field) return;

        function sync() {
            field.classList.toggle('hidden', !checkbox.checked);
        }

        checkbox.addEventListener('change', sync);
        sync();
    });

    (function () {
        var hasErrors = @json($errors->any());
        var fileInputs = document.querySelectorAll('.js-bukti-field[data-persist-key]');

        function readAsDataUrl(file) {
            return new Promise(function (resolve, reject) {
                var reader = new FileReader();
                reader.onload = function () { resolve(reader.result); };
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }

        function dataUrlToFile(dataUrl, filename, mime) {
            return fetch(dataUrl)
                .then(function (res) { return res.blob(); })
                .then(function (blob) { return new File([blob], filename, { type: mime }); });
        }

        fileInputs.forEach(function (input) {
            var key = 'dispensasi_' + input.dataset.persistKey;

            if (hasErrors) {
                var saved = sessionStorage.getItem(key);
                if (saved) {
                    try {
                        var parsed = JSON.parse(saved);
                        dataUrlToFile(parsed.data, parsed.name, parsed.type).then(function (file) {
                            var dt = new DataTransfer();
                            dt.items.add(file);
                            input.files = dt.files;
                        });
                    } catch (e) {
                        sessionStorage.removeItem(key);
                    }
                }
            } else {
                sessionStorage.removeItem(key);
            }

            input.addEventListener('change', function () {
                var file = input.files[0];
                if (!file) {
                    sessionStorage.removeItem(key);
                    return;
                }
                readAsDataUrl(file).then(function (dataUrl) {
                    try {
                        sessionStorage.setItem(key, JSON.stringify({
                            name: file.name,
                            type: file.type,
                            data: dataUrl,
                        }));
                    } catch (e) {
                        sessionStorage.removeItem(key);
                    }
                });
            });
        });
    })();
</script>
@endsection