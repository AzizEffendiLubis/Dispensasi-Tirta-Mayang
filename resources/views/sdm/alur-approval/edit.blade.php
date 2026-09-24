@extends('layouts.app')

@section('title', 'Alur Approval — ' . $unitOrganisasi->nama)
@section('page-title', 'Alur Approval')

@section('content')
    <div class="mb-6">
        <a href="{{ route('sdm.alur-approval.index') }}" class="text-sm text-ink-soft hover:text-primary">
            <i class="fas fa-arrow-left"></i> Kembali ke Alur Approval
        </a>
        <h1 class="font-display text-2xl font-semibold text-ink mt-2">{{ $unitOrganisasi->nama }}</h1>
        <p class="text-sm text-ink-soft mt-1">
            Untuk tiap jabatan pengaju di unit ini, pilih jabatan yang jadi penyetuju.
            Baris yang belum diatur otomatis <strong>mengikuti aturan unit induk</strong> (ditandai di bawah dropdown) —
            kamu cuma perlu mengisi baris yang aturannya beda dari induknya.
        </p>
    </div>

    <form method="POST" action="{{ route('sdm.alur-approval.update', $unitOrganisasi) }}" class="card">
        @csrf
        @method('PUT')

        <div class="table-scroll-wrapper" style="border:none; border-radius:0;">
            <table class="table-pro">
                <thead>
                    <tr>
                        <th>Jabatan Pengaju</th>
                        <th>Jabatan Penyetuju</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jabatans as $jabatanPengaju)
                        @php
                            $aturanSaatIni = $aturanPerJabatanPengaju->get($jabatanPengaju->id);
                            $approverIdSaatIni = old("jabatan_pengaju.{$jabatanPengaju->id}", $aturanSaatIni?->jabatan_approver_id);
                            $warisan = $warisanPerJabatanPengaju[$jabatanPengaju->id] ?? null;
                        @endphp
                        <tr>
                            <td class="font-semibold text-ink align-top">{{ $jabatanPengaju->nama }}</td>
                            <td>
                                <select name="jabatan_pengaju[{{ $jabatanPengaju->id }}]" class="field-input">
                                    <option value="">— Tidak ada aturan sendiri —</option>
                                    @foreach ($jabatans as $jabatanApprover)
                                        @continue($jabatanApprover->id === $jabatanPengaju->id)
                                        <option value="{{ $jabatanApprover->id }}" @selected((string) $approverIdSaatIni === (string) $jabatanApprover->id)>
                                            {{ $jabatanApprover->nama }}
                                        </option>
                                    @endforeach
                                </select>

                                @if ($warisan)
                                    <p class="text-xs text-ink-soft mt-1.5">
                                        <i class="fas fa-arrow-turn-up fa-rotate-90 opacity-60"></i>
                                        Saat ini mengikuti <strong>{{ $warisan['unit']->nama }}</strong>:
                                        disetujui oleh <strong>{{ $warisan['jabatanApprover']->nama }}</strong>.
                                        Pilih jabatan di atas kalau unit ini butuh aturan berbeda.
                                    </p>
                                @elseif (! $aturanSaatIni)
                                    <p class="text-xs text-ink-soft mt-1.5 italic">
                                        Belum ada aturan di unit ini maupun unit induknya.
                                    </p>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end gap-2 p-5 border-t border-line">
            <a href="{{ route('sdm.alur-approval.index') }}" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Simpan Alur Approval</button>
        </div>
    </form>
@endsection