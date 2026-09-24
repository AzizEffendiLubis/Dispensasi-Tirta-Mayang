@extends('layouts.app')
@section('title', 'Cetak Surat E-Dispensasi')
@section('page-title', 'Cetak Surat E-Dispensasi')

@section('content')
<div class="mb-8">
    <a href="{{ route('dispensasi.show', $dispensasi) }}" class="text-xs text-accent font-semibold mb-2 inline-flex items-center gap-1">
        <i class="fas fa-arrow-left"></i> Kembali ke Detail Pengajuan
    </a>
    <h1 class="font-display text-3xl text-ink mt-2">Terbitkan Surat E-Dispensasi</h1>
</div>

<div class="max-w-2xl">
    <div class="card p-6">

        <div class="flex gap-3 rounded-lg p-4 text-sm mb-6" style="background: #d9f5f8; border: 1px solid rgba(10,47,92,.12);">
            <i class="fas fa-circle-info text-accent mt-0.5"></i>
            <p class="text-ink-soft">
                Surat akan mencantumkan <strong class="text-ink">semua pegawai</strong> di unit ini
                yang izinnya disetujui oleh <strong class="text-ink">{{ $dispensasi->diprosesOleh?->name ?? '-' }}</strong>
                pada tanggal <strong class="text-ink">{{ $dispensasi->tanggal_dispensasi->translatedFormat('d F Y') }}</strong>
                dan belum pernah dijadikan surat sebelumnya.
                Nomor surat tidak bisa diubah lagi setelah diterbitkan.
            </p>
        </div>

        <dl class="grid sm:grid-cols-2 gap-4 text-sm mb-5">
            <div>
                <dt class="text-xs text-ink-soft mb-0.5">Unit Organisasi</dt>
                <dd class="text-ink">{{ $dispensasi->unitOrganisasi->nama }}</dd>
            </div>
            <div>
                <dt class="text-xs text-ink-soft mb-0.5">Tanggal Dispensasi</dt>
                <dd class="text-ink">{{ $dispensasi->tanggal_dispensasi->translatedFormat('d F Y') }}</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-xs text-ink-soft mb-0.5">Disetujui Oleh</dt>
                <dd class="text-ink">
                    {{ $dispensasi->diprosesOleh?->name ?? '-' }}
                    <span class="text-ink-soft">({{ $dispensasi->diprosesOleh?->jabatanLengkap() }})</span>
                </dd>
            </div>
        </dl>

        <div class="mb-6">
            <p class="text-xs text-ink-soft mb-2">
                Pegawai yang akan tercantum dalam surat ini ({{ $previewKelompok->pluck('pegawai_id')->unique()->count() }} orang):
            </p>
            <div class="table-scroll-wrapper">
                <table class="table-pro">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($previewKelompok->groupBy('pegawai_id') as $barisPegawai)
                        @php $acuanPegawai = $barisPegawai->first(); @endphp
                        <tr>
                            <td>
                                <p class="font-medium text-ink">{{ $acuanPegawai->pegawai->nama_pegawai }}</p>
                                <p class="text-xs text-ink-soft mono-data">{{ $acuanPegawai->pegawai->nik }}</p>
                            </td>
                            <td class="text-ink-soft">
                                @foreach ($barisPegawai->pluck('waktu_dispensasi')->unique() as $w)
                                <span class="badge badge-default">{{ $w }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <form action="{{ route('dispensasi.cetak.store', $dispensasi) }}" method="POST" class="space-y-5" style="border-top: 1px solid #e4e9f0; padding-top: 1.5rem;">
            @csrf

            <div>
                <label for="nomor_surat" class="field-label">
                    Nomor Surat <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="nomor_surat"
                       id="nomor_surat"
                       class="field-input"
                       value="{{ old('nomor_surat') }}"
                       placeholder="Contoh: 123/E-DISPEN/SDM/{{ now()->format('m/Y') }}"
                       required>
                @error('nomor_surat')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="tanggal_surat" class="field-label">
                    Tanggal Surat <span class="text-red-500">*</span>
                </label>
                <input type="date"
                       name="tanggal_surat"
                       id="tanggal_surat"
                       class="field-input"
                       value="{{ old('tanggal_surat', now()->toDateString()) }}"
                       required>
                @error('tanggal_surat')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('dispensasi.show', $dispensasi) }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Terbitkan &amp; Kirim ke Admin SDM
                </button>
            </div>
        </form>

    </div>
</div>
@endsection