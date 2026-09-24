@extends('layouts.app')
@section('title', 'Detail Surat E-Dispensasi')
@section('page-title', 'Detail Surat E-Dispensasi')

@push('styles')
<style>
    .surat-kertas {
        background: #fff;
        max-width: 794px; /* ~A4 width at 96dpi */
        margin: 0 auto;
        padding: 48px 56px;
        font-family: 'Helvetica', 'Arial', sans-serif;
        font-size: 13px;
        color: #1a1a1a;
        line-height: 1.5;
    }

    table.kop-memo {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    table.kop-memo td {
        border: 1px solid #000;
        padding: 5px 12px;
        line-height: 1.3;
        vertical-align: middle;
    }
    table.kop-memo td.label-box {
        width: 120px;
        white-space: nowrap;
        background: #000;
        color: #fff;
        font-weight: bold;
        font-size: 13px;
        letter-spacing: 0.3px;
        text-align: center;
        text-transform: uppercase;
    }
    table.kop-memo td.isi-box {
        font-weight: bold;
        font-size: 13px;
        letter-spacing: 0.2px;
        text-transform: uppercase;
    }

    .surat-kertas .nomor-plain { font-size: 13px; margin: 6px 0 10px; }

    table.header-memo {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
        font-size: 13px;
    }
    table.header-memo td {
        border: 1px solid #000;
        padding: 5px 10px;
        line-height: 1.3;
        vertical-align: top;
    }
    table.header-memo td.label { width: 100px; font-weight: bold; text-transform: uppercase; }

    .surat-kertas .isi-surat p { text-align: justify; margin: 0 0 8px; }

    table.tabel-unit-organisasi { border-collapse: collapse; margin: 0 0 8px; }
    table.tabel-unit-organisasi td { border: none; padding: 0; line-height: 1.5; vertical-align: top; }
    table.tabel-unit-organisasi td.label-unit { width: 120px; white-space: nowrap; }

    table.tabel-pegawai {
        width: 100%;
        border-collapse: collapse;
        margin: 16px 0 20px;
        font-size: 13px;
    }
    table.tabel-pegawai th, table.tabel-pegawai td {
        border: 1px solid #000;
        padding: 5px 10px;
        line-height: 1.3;
        text-align: center;
        vertical-align: middle;
    }
    table.tabel-pegawai th { font-weight: bold; background: #F6FAFD; }
    table.tabel-pegawai td.kolom-no { width: 30px; }
    table.tabel-pegawai td.kolom-nama { text-align: left; }
    table.tabel-pegawai td.kolom-nama .nik { display: block; font-size: 11px; color: #667085; }
    table.tabel-pegawai td.kolom-cek { width: 36px; }
    table.tabel-pegawai td.kolom-ket { text-align: left; }

    table.blok-tanda-tangan {
        width: 100%;
        table-layout: fixed;
        border-collapse: collapse;
        margin-top: 44px;
        font-size: 13px;
    }
    table.blok-tanda-tangan td { border: none; padding: 0; vertical-align: top; }
    table.blok-tanda-tangan td.kolom-tanda-tangan {
        width: 60%;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    table.blok-tanda-tangan td.kolom-qr { width: 40%; text-align: right; }
    table.blok-tanda-tangan .nama-penandatangan { font-weight: bold; margin-top: 55px; }

    .halaman-lampiran { margin-top: 32px; padding-top: 24px; border-top: 2px dashed #e4e9f0; }
    .judul-lampiran { font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }

    @media print {
        .no-print { display: none !important; }
        body { background: #fff !important; }
        .surat-kertas { max-width: none; padding: 0; }
    }
</style>
@endpush

@section('content')
<div class="mb-6 no-print">
    <a href="{{ route('sdm.arsip-e-dispensasi.index') }}" class="text-sm text-ink-soft hover:text-primary">
        <i class="fas fa-arrow-left"></i> Kembali ke Arsip E-Dispensasi
    </a>
    <div class="flex items-start justify-between gap-4 flex-wrap mt-3">
        <div>
            <p class="text-xs font-semibold tracking-widest text-accent uppercase mb-1">Admin SDM</p>
            <h1 class="font-display text-3xl text-ink">Detail Surat E-Dispensasi</h1>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-outline">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('dispensasi.surat.unduh', $dispensasi->id) }}" class="btn btn-primary">
                <i class="fas fa-file-pdf"></i> Unduh PDF
            </a>
        </div>
    </div>
</div>

<div class="card p-0 overflow-visible">
    <div class="surat-kertas">

        <table class="kop-memo">
            <tr>
                <td class="label-box">MEMO INTERNAL</td>
                <td class="isi-box">{{ $labelInstansi }}</td>
            </tr>
        </table>

        <p class="nomor-plain">Nomor: {{ $nomorSurat }}</p>

        <table class="header-memo">
            <tr>
                <td class="label">Dari</td>
                <td>{{ $jabatanPenyetuju }}</td>
            </tr>
            <tr>
                <td class="label">Kepada</td>
                <td>{{ $jabatanTujuan }}</td>
            </tr>
            <tr>
                <td class="label">Tembusan</td>
                <td>Arsip</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td>{{ \Carbon\Carbon::parse($tanggalSurat)->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Lampiran</td>
                <td>{{ $adaLampiran ? '1 (rincian terlampir)' : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Perihal</td>
                <td>Dispensasi Absen</td>
            </tr>
        </table>

        <div class="isi-surat">
            <p>
                Berdasarkan Peraturan Direksi Nomor 03 Tahun 2018 Tanggal 24 April 2018 Tentang Disiplin
                Pegawai Perumda Air Minum Tirta Mayang Kota Jambi, dikarenakan petugas pada:
            </p>
            <table class="tabel-unit-organisasi">
                @if ($unitBarisUtama)
                    <tr>
                        <td class="label-unit">{{ $unitBarisUtama->labelTingkat() }}</td>
                        <td>: {{ $unitBarisUtama->nama }}</td>
                    </tr>
                @endif
            </table>
            <p>
                Maka kami mengajukan Dispensasi/izin tidak melaksanakan absen pada tanggal
                {{ $dispensasi->tanggal_dispensasi->translatedFormat('d F Y') }}, atas nama sebagai berikut:
            </p>
        </div>

        <table class="tabel-pegawai">
            <tr>
                <th class="kolom-no">No</th>
                <th class="kolom-nama">Nama</th>
                <th class="kolom-cek">T</th>
                <th class="kolom-cek">TBO</th>
                <th class="kolom-cek">TBI</th>
                <th class="kolom-cek">CP</th>
                <th class="kolom-ket">Keterangan</th>
            </tr>
            @foreach ($barisHalamanUtama as $i => $baris)
                <tr>
                    <td class="kolom-no">{{ $i + 1 }}</td>
                    <td class="kolom-nama">
                        {{ $baris->pegawai->nama_pegawai }}
                        <span class="nik">{{ $baris->pegawai->nik }}</span>
                    </td>
                    @foreach (['T', 'TBO', 'TBI', 'CP'] as $kode)
                        <td class="kolom-cek">{{ in_array($kode, $baris->waktu, true) ? 'v' : '-' }}</td>
                    @endforeach
                    <td class="kolom-ket">{{ $baris->keterangan }}</td>
                </tr>
            @endforeach
        </table>

        @if($adaLampiran)
            <p><em>Rincian pegawai selebihnya tercantum pada lampiran di bawah.</em></p>
        @endif

        <p>Demikian disampaikan dan diucapkan terima kasih.</p>

        <table class="blok-tanda-tangan">
            <tr>
                <td class="kolom-tanda-tangan">
                    Perumda Air Minum Tirta Mayang<br>
                    Kota Jambi

                    <div class="nama-penandatangan">{{ $penyetuju?->name ?? '-' }}</div>
                    <div>{{ $jabatanTandaTangan }}</div>
                </td>
                <td class="kolom-qr">
                    <img src="data:image/svg+xml;base64,{{ base64_encode($qrSvg) }}" width="120" height="120">
                </td>
            </tr>
        </table>

        @if($adaLampiran)
            <div class="halaman-lampiran">
                <p class="judul-lampiran">Lampiran — Rincian Dispensasi</p>
                <p>Nomor Surat: {{ $nomorSurat }}</p>
                <table class="tabel-pegawai">
                    <tr>
                        <th class="kolom-no">No</th>
                        <th class="kolom-nama">Nama</th>
                        <th class="kolom-cek">T</th>
                        <th class="kolom-cek">TBO</th>
                        <th class="kolom-cek">TBI</th>
                        <th class="kolom-cek">CP</th>
                        <th class="kolom-ket">Keterangan</th>
                    </tr>
                    @foreach ($barisLampiran as $i => $baris)
                        <tr>
                            <td class="kolom-no">{{ count($barisHalamanUtama) + $i + 1 }}</td>
                            <td class="kolom-nama">
                                {{ $baris->pegawai->nama_pegawai }}
                                <span class="nik">{{ $baris->pegawai->nik }}</span>
                            </td>
                            @foreach (['T', 'TBO', 'TBI', 'CP'] as $kode)
                                <td class="kolom-cek">{{ in_array($kode, $baris->waktu, true) ? 'v' : '-' }}</td>
                            @endforeach
                            <td class="kolom-ket">{{ $baris->keterangan }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

    </div>
</div>
@endsection