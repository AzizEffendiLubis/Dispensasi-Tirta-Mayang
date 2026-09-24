<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat E-Dispensasi {{ $nomorSurat }}</title>
    <style>
        @page {
            size: A4;
            margin: 2.54cm 2.54cm 2.54cm 2.54cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            line-height: 1.5;
        }

        table.kop-memo {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
            margin-bottom: 0;
        }
        table.kop-memo td {
            border: 1px solid #000;
            padding: 4px 10px;
            line-height: 1.3;
            vertical-align: middle;
        }
        table.kop-memo td.label-box {
            width: 100px;
            white-space: nowrap;
            background: #000;
            color: #fff;
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 0.3px;
            text-align: center;
            text-transform: uppercase;
        }
        table.kop-memo td.isi-box {
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .nomor-plain {
            font-size: 12px;
            margin: 4px 0 8px;
        }

        table.header-memo {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 12px;
        }
        table.header-memo td {
            border: 1px solid #000;
            padding: 4px 8px;
            line-height: 1.3;
            vertical-align: top;
        }
        table.header-memo td.label {
            width: 90px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .isi-surat p { text-align: justify; margin: 0 0 6px; }

        table.tabel-unit-organisasi {
            border-collapse: collapse;
            margin: 0 0 6px;
        }
        table.tabel-unit-organisasi td {
            border: none;
            padding: 0;
            line-height: 1.5;
            vertical-align: top;
        }
        table.tabel-unit-organisasi td.label-unit {
            width: 110px;
            white-space: nowrap;
        }

        table.tabel-pegawai {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0 18px;
            font-size: 12px;
        }
        table.tabel-pegawai th,
        table.tabel-pegawai td {
            border: 1px solid #000;
            padding: 4px 8px;
            line-height: 1.3;
            text-align: center;
            vertical-align: middle;
        }
        table.tabel-pegawai th { font-weight: bold; }
        table.tabel-pegawai td.kolom-no { width: 28px; }
        table.tabel-pegawai td.kolom-nama { text-align: left; }
        table.tabel-pegawai td.kolom-nama .nik { display: block; font-size: 12px; color: #666; }
        table.tabel-pegawai td.kolom-cek { width: 34px; }
        table.tabel-pegawai td.kolom-ket { text-align: left; }

        table.blok-tanda-tangan {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
            font-size: 12px;
        }
        table.blok-tanda-tangan td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        table.blok-tanda-tangan td.kolom-tanda-tangan {
            width: 60%;
        }
        table.blok-tanda-tangan td.kolom-qr {
            width: 40%;
            text-align: right;
        }
        table.blok-tanda-tangan .nama-penandatangan {
            font-weight: bold;
            margin-top: 55px;
        }

        .halaman-lampiran {
            page-break-before: always;
        }
        .judul-lampiran {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

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
        <p><em>Rincian pegawai selebihnya tercantum pada lampiran (halaman berikutnya).</em></p>
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

</body>
</html>