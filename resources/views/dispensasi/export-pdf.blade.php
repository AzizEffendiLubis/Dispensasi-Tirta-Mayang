<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #000; }
        h1 { font-size: 14pt; font-weight: bold; text-align: center; margin: 0 0 4px; color: #000; text-transform: uppercase; }
        p.subtitle { font-size: 12pt; text-align: center; margin: 0 0 16px; color: #000; }
        table { width: 100%; border-collapse: collapse; font-size: 12pt; }
        th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; vertical-align: top; color: #000; }
        th { font-weight: bold; }
        td.center { text-align: center; padding: 20px; }
    </style>
</head>
<body>
    @php
        $namaBulanIndonesia = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // Judul mengikuti filter tahun/bulan yang dipilih Admin Departemen
        // saat export (bukan tanggal cetak), dan nama unitnya sendiri
        // ($namaDepartemen dari Auth::user()->unitOrganisasi — otomatis
        // sesuai unit Admin yang sedang login, bukan teks tetap "SDM").
        if ($bulan && $tahun) {
            $judul = 'Dispensasi ' . $namaDepartemen . ' ' . strtoupper($namaBulanIndonesia[$bulan]) . ' ' . $tahun;
        } elseif ($tahun) {
            $judul = 'Dispensasi ' . $namaDepartemen . ' Tahun ' . $tahun;
        } elseif ($bulan) {
            $judul = 'Dispensasi ' . $namaDepartemen . ' ' . strtoupper($namaBulanIndonesia[$bulan]);
        } else {
            $judul = 'Dispensasi ' . $namaDepartemen . ' Seluruh Periode';
        }
    @endphp

    <h1>{{ $judul }}</h1>
    <p class="subtitle">Dicetak {{ now()->format('d') }} {{ ucfirst(substr($namaBulanIndonesia[now()->month], 0, 3)) }} {{ now()->year }}, {{ now()->format('H:i') }} WIB</p>

    <table>
        <thead>
            <tr>
                <th>Nomor</th>
                <th>Pegawai</th>
                <th>Unit Organisasi</th>
                <th>Tanggal Dispensasi</th>
                <th>Waktu</th>
                <th>Diputuskan Oleh</th>
                <th>Tanggal Keputusan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dispensasis as $d)
            <tr>
                <td>{{ $d->nomor_dispensasi }}</td>
                <td>{{ $d->pegawai->nama_pegawai }}</td>
                <td>{{ $d->unitOrganisasi->nama }}</td>
                <td>{{ $d->tanggal_dispensasi->format('d M Y') }}</td>
                <td>{{ $d->waktu_dispensasi }}</td>
                <td>{{ $d->diprosesOleh?->name ?? '-' }}</td>
                <td>{{ $d->tanggal_keputusan?->format('d M Y') ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="center">Belum ada pengajuan yang disetujui.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>