@extends('layouts.app')

@section('title', 'Kelola Pegawai')
@section('page-title', 'Kelola Pegawai')

@section('content')
    <div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
        <div>
            <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin SDM</p>
            <h1 class="font-display text-3xl text-ink">Kelola Pegawai</h1>
            <p class="text-sm text-ink-soft mt-1">Dikelompokkan mengikuti struktur Divisi → Departemen → Sub Departemen.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('sdm.pegawai.import.form') }}" class="btn btn-outline">
                <i class="fas fa-file-import"></i> Import
            </a>
            <a href="{{ route('sdm.pegawai.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Pegawai
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('sdm.pegawai.index') }}" class="card p-4 mb-6 flex gap-3 flex-wrap items-end">
        <div class="flex-1 min-w-[220px]">
            <label class="text-xs text-ink-soft mb-1 block">Cari</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="NIK atau nama pegawai..." class="field-input">
        </div>
        <div class="flex-1 min-w-[220px]">
            <label class="text-xs text-ink-soft mb-1 block">Unit Organisasi</label>
            <select name="unit_organisasi_id" class="field-input">
                <option value="">Semua Unit</option>
                @foreach ($unitFlatIndent as $item)
                    <option value="{{ $item['unit']->id }}" @selected((string) $unitOrganisasiId === (string) $item['unit']->id)>
                        {{ str_repeat('— ', $item['depth']) }}{{ $item['unit']->nama }}
                    </option>
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
        <button class="btn btn-primary">Terapkan Filter</button>
        @if ($search || $unitOrganisasiId || $status)
            <a href="{{ route('sdm.pegawai.index') }}" class="btn btn-outline">Reset</a>
        @endif
    </form>

    @php
        $labelTingkat = ['divisi' => 'Divisi', 'departemen' => 'Departemen', 'subdepartemen' => 'Sub Departemen'];
        $badgeTingkat = ['divisi' => 'badge-default', 'departemen' => 'badge-disetujui', 'subdepartemen' => 'badge-menunggu'];

        $renderNode = function (array $node, int $level) use (&$renderNode, $labelTingkat, $badgeTingkat) {
            $unit = $node['unit'];

            $childrenHtml = '';
            foreach ($node['children'] as $anak) {
                $childrenHtml .= $renderNode($anak, $level + 1);
            }

            return \Illuminate\Support\Facades\Blade::render(<<<'BLADE'
                <div class="{{ $level > 0 ? 'ml-4 sm:ml-6 pl-4 sm:pl-5 border-l-2 border-line' : '' }} mb-5">
                    <div class="flex items-center gap-2.5 mb-3 flex-wrap">
                        <span class="badge {{ $badgeClass }}">{{ $labelText }}</span>
                        <h3 class="font-display font-semibold text-ink {{ $level === 0 ? 'text-lg' : 'text-base' }}">
                            <a href="{{ route('sdm.unit-organisasi.show', $unit->id) }}" class="hover:text-primary hover:underline">
                                {{ $unit->nama }}
                            </a>
                        </h3>
                        <span class="text-xs text-ink-soft">{{ $node['total_pegawai'] }} pegawai</span>
                    </div>

                    @if ($node['pegawai']->isNotEmpty())
                        <div class="card mb-4">
                            <div class="table-scroll-wrapper" style="border:none; border-radius:0;">
                                <table class="table-pro">
                                    <thead>
                                        <tr>
                                            <th>NIK</th>
                                            <th>Nama Pegawai</th>
                                            <th>Jabatan</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($node['pegawai'] as $pegawai)
                                            <tr>
                                                <td class="mono-data">{{ $pegawai->nik }}</td>
                                                <td class="font-semibold text-ink">{{ $pegawai->nama_pegawai }}</td>
                                                <td class="text-ink-soft">{{ $pegawai->jabatan?->nama ?? '-' }}</td>
                                                <td>
                                                    <span class="badge {{ $pegawai->status === 'aktif' ? 'badge-disetujui' : 'badge-ditolak' }}">
                                                        {{ ucfirst($pegawai->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-right whitespace-nowrap">
                                                    <a href="{{ route('sdm.pegawai.edit', $pegawai) }}" class="btn btn-outline btn-sm" title="Edit">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                    @if ($pegawai->status === 'aktif')
                                                        <span x-data="{ confirmOpen: false }" class="inline-block">
                                                            <button type="button" @click="confirmOpen = true" class="btn btn-outline btn-sm" title="Nonaktifkan" style="color:#C1483A;">
                                                                <i class="fas fa-power-off"></i>
                                                            </button>

                                                            <form id="nonaktifkan-form-{{ $pegawai->id }}" action="{{ route('sdm.pegawai.destroy', $pegawai) }}" method="POST" class="hidden">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>

                                                            <template x-teleport="body">
                                                                <div x-show="confirmOpen" x-cloak
                                                                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                                                     class="fixed inset-0 z-[300] flex items-center justify-center p-4"
                                                                     style="background: rgba(15,23,42,.48); backdrop-filter: blur(2px);">
                                                                    <div @click.outside="confirmOpen = false"
                                                                         x-show="confirmOpen"
                                                                         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                                                         class="card w-full max-w-sm p-6 text-center">
                                                                        <div class="h-12 w-12 rounded-full flex items-center justify-center mx-auto mb-3" style="background: #FBE7E4; color:#C1483A;">
                                                                            <i class="fas fa-power-off"></i>
                                                                        </div>
                                                                        <h3 class="font-bold text-ink mb-1">Nonaktifkan {{ $pegawai->nama_pegawai }}?</h3>
                                                                        <p class="text-sm text-ink-soft mb-5">
                                                                            Pegawai ini akan ditandai nonaktif dan tidak lagi tampil di daftar pegawai aktif.
                                                                        </p>
                                                                        <div class="flex gap-2 justify-center">
                                                                            <button type="button" @click="confirmOpen = false" class="btn btn-outline">Batal</button>
                                                                            <button type="submit" form="nonaktifkan-form-{{ $pegawai->id }}" class="btn btn-danger">Ya, Nonaktifkan</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {!! $childrenHtml !!}
                </div>
                BLADE, [
                'level' => $level,
                'unit' => $unit,
                'node' => $node,
                'badgeClass' => $badgeTingkat[$unit->tingkat] ?? 'badge-default',
                'labelText' => $labelTingkat[$unit->tingkat] ?? $unit->tingkat,
                'childrenHtml' => $childrenHtml,
            ]);
        };
    @endphp

    @forelse ($pohonUnit as $node)
        {!! $renderNode($node, 0) !!}
    @empty
        <div class="card p-10 text-center text-ink-soft">
            @if ($search || $unitOrganisasiId || $status)
                Tidak ada pegawai yang cocok dengan filter ini.
            @else
                Belum ada pegawai. <a href="{{ route('sdm.pegawai.create') }}" class="text-primary font-semibold hover:underline">Tambah pegawai pertama</a>.
            @endif
        </div>
    @endforelse
@endsection