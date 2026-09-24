@extends('layouts.app')

@section('title', 'Alur Approval')
@section('page-title', 'Alur Approval')

@section('content')
    @php
        $badgeTingkat = ['divisi' => 'badge-default', 'departemen' => 'badge-disetujui', 'subdepartemen' => 'badge-menunggu'];
    @endphp

    <div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
        <div>
            <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin SDM</p>
            <h1 class="font-display text-3xl text-ink">Alur Approval</h1>
            <p class="text-sm text-ink-soft mt-1">
                Atur jabatan mana yang jadi penyetuju untuk tiap jabatan pengaju, per unit organisasi.
                Kalau sebuah unit tidak punya aturan untuk suatu jabatan, sistem otomatis naik mencari aturan di unit induknya.
            </p>
        </div>
    </div>

    <div class="card">
        <div class="table-scroll-wrapper" style="border:none; border-radius:0;">
            <table class="table-pro">
                <thead>
                    <tr>
                        <th>Nama Unit</th>
                        <th>Kode</th>
                        <th>Tingkat</th>
                        <th>Induk</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unitOrganisasis as $unit)
                        <tr>
                            <td class="font-semibold text-ink">{{ $unit->nama }}</td>
                            <td class="mono-data">{{ $unit->kode ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $badgeTingkat[$unit->tingkat] ?? 'badge-default' }}">
                                    {{ $unit->labelTingkat() }}
                                </span>
                            </td>
                            <td class="text-ink-soft">{{ $unit->parent?->nama ?? '— Mandiri —' }}</td>
                            <td class="text-right">
                                <a href="{{ route('sdm.alur-approval.edit', $unit) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-route"></i> Atur Alur
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-ink-soft py-10">
                                Belum ada unit organisasi. Tambahkan lewat menu Unit Organisasi dulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection