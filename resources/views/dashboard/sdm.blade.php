@extends('layouts.app')
@section('title', 'Dashboard Admin SDM')
@section('page-title', 'Dashboard Admin SDM')

@section('content')
<div class="mb-8">
    <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Dashboard</p>
    <h1 class="font-display text-3xl text-ink">Admin SDM</h1>
</div>

{{-- Filter --}}
<form method="GET" class="card p-4 mb-8 flex gap-3 flex-wrap items-end">
    <div class="flex-1 min-w-[140px]">
        <label class="text-xs text-ink-soft mb-1 block">Periode / Tahun</label>
        <select name="tahun" class="field-input">
            @foreach ($tahunTersedia as $t)
            <option value="{{ $t }}" @selected($tahun == $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="text-xs text-ink-soft mb-1 block">Unit Organisasi</label>
        <select name="unit_organisasi_id" class="field-input">
            <option value="">Semua Unit Organisasi</option>
            @foreach ($unitOrganisasis as $u)
            <option value="{{ $u->id }}" @selected($unitOrganisasiId == $u->id)>{{ $u->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[180px]">
        <label class="text-xs text-ink-soft mb-1 block">Status</label>
        <select name="status" class="field-input">
            <option value="">Semua Status</option>
            <option value="menunggu_persetujuan" @selected($status === 'menunggu_persetujuan')>Menunggu Persetujuan</option>
            <option value="disetujui" @selected($status === 'disetujui')>Disetujui</option>
            <option value="ditolak" @selected($status === 'ditolak')>Ditolak</option>
        </select>
    </div>
    <button class="btn btn-primary">Terapkan Filter</button>
    @if ($unitOrganisasiId || $status)
    <a href="{{ route('sdm.dashboard') }}" class="btn btn-outline">Reset</a>
    @endif
</form>

{{-- Total dispensasi --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="card p-5">
        <p class="text-xs text-ink-soft mb-1">Total Dispensasi</p>
        <p class="font-display text-2xl text-ink mono-data">{{ $totalSemua }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs text-ink-soft mb-1">Menunggu Persetujuan</p>
        <p class="font-display text-2xl text-[#C8862B] mono-data">{{ $totalMenunggu }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs text-ink-soft mb-1">Disetujui</p>
        <p class="font-display text-2xl text-[#2E9E6B] mono-data">{{ $totalDisetujui }}</p>
    </div>
    <div class="card p-5">
        <p class="text-xs text-ink-soft mb-1">Ditolak</p>
        <p class="font-display text-2xl text-[#C1483A] mono-data">{{ $totalDitolak }}</p>
    </div>
</div>

{{-- Chart per bulan & per unit organisasi --}}
<div class="grid lg:grid-cols-2 gap-4 mb-6">
    <div class="card p-6">
        <h3 class="font-semibold text-ink mb-4">Dispensasi per Bulan — {{ $tahun }}</h3>
        <canvas id="chartBulan" height="220"></canvas>
    </div>
    <div class="card p-6">
        <h3 class="font-semibold text-ink mb-4">Dispensasi Berdasarkan Unit Organisasi</h3>
        @if ($perUnit->isEmpty())
        <p class="text-sm text-ink-soft py-8 text-center">Belum ada data.</p>
        @else
        <canvas id="chartUnit" height="220"></canvas>
        @endif
    </div>
</div>

{{-- Pengajuan menggantung --}}
<div class="card overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-line flex items-center justify-between">
        <div>
            <h3 class="font-semibold text-ink">Perlu Perhatian — Menunggu Lebih dari 3 Hari</h3>
            <p class="text-xs text-ink-soft mt-0.5">Selalu lintas tahun, tidak mengikuti filter Periode / Tahun di atas.</p>
        </div>
        <span class="badge badge-menunggu">{{ $pengajuanMenggantung->count() }}</span>
    </div>
    <div class="table-scroll-wrapper" style="border-radius:0; border:0;">
        <table class="table-pro">
            <thead>
                <tr><th>Nomor</th><th>Pegawai</th><th>Unit Organisasi</th><th>Lama Menunggu</th></tr>
            </thead>
            <tbody>
                @forelse ($pengajuanMenggantung as $p)
                <tr>
                    <td class="mono-data text-ink-soft">{{ $p['nomor'] }}</td>
                    <td class="font-medium">{{ $p['pegawai'] }}</td>
                    <td class="text-ink-soft">{{ $p['unit'] }}</td>
                    <td class="text-[#C1483A] font-medium">{{ $p['hari_menunggu'] }} hari</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-ink-soft py-10">Tidak ada pengajuan yang menggantung lama.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Dispensasi terbaru --}}
<div class="card overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-line">
        <h3 class="font-semibold text-ink">Dispensasi Terbaru</h3>
    </div>
    <div class="table-scroll-wrapper" style="border-radius:0; border:0;">
        <table class="table-pro">
            <thead>
                <tr><th>Nomor</th><th>Pegawai</th><th>Unit Organisasi</th><th>Tanggal</th><th>Diajukan</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse ($terbaru as $d)
                @php
                    $statusLabel = match ($d->status_pengajuan) {
                        'menunggu_persetujuan' => 'Menunggu',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                        default => ucfirst($d->status_pengajuan),
                    };
                    $statusClass = match ($d->status_pengajuan) {
                        'menunggu_persetujuan' => 'badge-menunggu',
                        'disetujui' => 'badge-disetujui',
                        'ditolak' => 'badge-ditolak',
                        default => 'badge-default',
                    };
                @endphp
                <tr>
                    <td class="mono-data text-ink-soft">{{ $d->nomor_dispensasi }}</td>
                    <td class="font-medium">{{ $d->pegawai?->nama_pegawai ?? '-' }}</td>
                    <td class="text-ink-soft">{{ $d->unitOrganisasi?->nama ?? '-' }}</td>
                    <td>{{ optional($d->tanggal_dispensasi)->format('d M Y') ?? '-' }}</td>
                    <td class="text-ink-soft text-xs">{{ $d->created_at->diffForHumans() }}</td>
                    <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-ink-soft py-10">Tidak ada data sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const primary = '#163A5C';
    const accent = '#2BAFC7';
    const line = '#E2E8EC';

    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#5C6B78';

    new Chart(document.getElementById('chartBulan'), {
        type: 'bar',
        data: {
            labels: @json($perBulan->pluck('label')),
            datasets: [{
                label: 'Jumlah Pengajuan',
                data: @json($perBulan->pluck('total')),
                backgroundColor: accent,
                borderRadius: 6,
                barThickness: 22,
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: line } },
                x: { grid: { display: false } },
            }
        }
    });

    @if ($perUnit->isNotEmpty())
    new Chart(document.getElementById('chartUnit'), {
        type: 'bar',
        data: {
            labels: @json($perUnit->pluck('nama')),
            datasets: [{
                label: 'Jumlah Pengajuan',
                data: @json($perUnit->pluck('total')),
                backgroundColor: primary,
                borderRadius: 6,
                barThickness: 18,
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: line } },
                y: { grid: { display: false } },
            }
        }
    });
    @endif
</script>
@endsection