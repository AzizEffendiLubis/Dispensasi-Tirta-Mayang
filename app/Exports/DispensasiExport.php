<?php

namespace App\Exports;

use App\Models\Dispensasi;
use App\Models\Pegawai;
use App\Models\UnitOrganisasi;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DispensasiExport implements WithEvents, WithTitle
{
    private const URUTAN_WAKTU = ['T', 'TBO', 'TBI', 'CP'];

    private const NAMA_HARI = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
    ];

    private ?array $unitIdsCache = null;

    public function __construct(
        private readonly ?string $tahun,
        private readonly ?string $bulan,
        private readonly ?string $unitOrganisasiId,
        private readonly array $namaBulan = [],
    ) {
    }

    public function title(): string
    {
        $judul = 'Rekap Absensi';

        if ($this->bulan && isset($this->namaBulan[(int) $this->bulan])) {
            $judul .= ' - ' . $this->namaBulan[(int) $this->bulan];
        }

        if ($this->tahun) {
            $judul .= ' ' . $this->tahun;
        }

        return substr($judul, 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->tulisLaporan($event->sheet->getDelegate());
            },
        ];
    }

    private function tulisLaporan(Worksheet $sheet): void
    {
        $dispensasis = $this->ambilDispensasi();

        $sheet->getColumnDimension('A')->setWidth(13.86);
        foreach (['B', 'C', 'D', 'E'] as $kolom) {
            $sheet->getColumnDimension($kolom)->setWidth(10);
        }

        $blokList = $this->unitOrganisasiId
            ? $this->susunBlokSemuaPegawaiUnit($dispensasis)
            : $this->susunBlokDariDataSaja($dispensasis);

        if ($blokList->isEmpty()) {
            $sheet->setCellValue('A1', 'Tidak ada data untuk periode/filter ini.');
            $sheet->getStyle('A1')->getFont()->setBold(true);
            return;
        }

        $tanggalAwal = $blokList->map(fn ($b) => Carbon::createFromDate($b['tahun'], $b['bulan'], 1))->min();
        $tanggalAkhir = $blokList->map(fn ($b) => Carbon::createFromDate($b['tahun'], $b['bulan'], 1)->endOfMonth())->max();

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI ABSENSI PERIODE');

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', $this->labelPeriode($tanggalAwal, $tanggalAkhir));

        foreach (['A1', 'A2'] as $coord) {
            $sheet->getStyle($coord)->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle($coord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $baris = 4;
        $totalBlok = $blokList->count();

        foreach ($blokList as $index => $blok) {
            $baris = $this->tulisBlokPegawai($sheet, $baris, $blok['pegawai'], $blok['tahun'], $blok['bulan'], $blok['dispensasi']);

            if ($index < $totalBlok - 1) {
                $baris += 2;
            }
        }
    }

    private function susunBlokSemuaPegawaiUnit(Collection $dispensasis): Collection
    {
        $pegawais = Pegawai::query()
            ->aktif()
            ->whereIn('unit_organisasi_id', $this->unitIds())
            ->orderBy('nama_pegawai')
            ->get();

        $targetBulan = $this->tentukanTargetBulan($dispensasis);
        $dispensasiPerPegawai = $dispensasis->groupBy('pegawai_id');

        $blok = collect();
        foreach ($pegawais as $pegawai) {
            $milikPegawai = $dispensasiPerPegawai->get($pegawai->id, collect());

            foreach ($targetBulan as [$tahun, $bulan]) {
                $bulanIni = $milikPegawai->filter(
                    fn ($d) => (int) $d->tanggal_dispensasi->format('Y') === $tahun
                        && (int) $d->tanggal_dispensasi->format('n') === $bulan
                );

                $blok->push([
                    'pegawai' => $pegawai,
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'dispensasi' => $bulanIni,
                ]);
            }
        }

        return $blok;
    }

    private function susunBlokDariDataSaja(Collection $dispensasis): Collection
    {
        return $dispensasis
            ->groupBy('pegawai_id')
            ->sortBy(fn ($grup) => $grup->first()->pegawai->nama_pegawai)
            ->flatMap(fn ($barisPegawai) => $barisPegawai
                ->groupBy(fn ($d) => $d->tanggal_dispensasi->format('Y-m'))
                ->sortKeys()
                ->map(fn ($barisBulanIni) => [
                    'pegawai' => $barisBulanIni->first()->pegawai,
                    'tahun' => (int) $barisBulanIni->first()->tanggal_dispensasi->format('Y'),
                    'bulan' => (int) $barisBulanIni->first()->tanggal_dispensasi->format('n'),
                    'dispensasi' => $barisBulanIni,
                ])
                ->values())
            ->values();
    }

    private function tulisBlokPegawai(Worksheet $sheet, int $baris, Pegawai $pegawai, int $tahun, int $bulan, Collection $dispensasiBulanIni): int
    {
        $awalBulan = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();

        $sheet->mergeCells("A{$baris}:E{$baris}");
        $sheet->setCellValue("A{$baris}", 'NIK : ' . $pegawai->nik);
        $sheet->getStyle("A{$baris}")->getFont()->setBold(true);
        $sheet->getStyle("A{$baris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $baris++;

        $sheet->mergeCells("A{$baris}:E{$baris}");
        $sheet->setCellValue("A{$baris}", 'NAMA : ' . $pegawai->nama_pegawai);
        $sheet->getStyle("A{$baris}")->getFont()->setBold(true);
        $sheet->getStyle("A{$baris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $baris++;

        $sheet->mergeCells("A{$baris}:E{$baris}");
        $baris++;

        $headerBaris = $baris;
        foreach (['Tanggal', 'Pagi', 'Istirahat', 'Siang', 'Sore'] as $i => $judulKolom) {
            $sheet->setCellValueByColumnAndRow($i + 1, $headerBaris, $judulKolom);
        }
        $sheet->getStyle("A{$headerBaris}:E{$headerBaris}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerBaris}:E{$headerBaris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $baris++;

        $perTanggal = $dispensasiBulanIni->groupBy(fn ($d) => $d->tanggal_dispensasi->format('Y-m-d'));

        $jumlahHari = $awalBulan->daysInMonth;
        for ($h = 1; $h <= $jumlahHari; $h++) {
            $tanggal = $awalBulan->copy()->day($h);
            $barisTanggalIni = $perTanggal->get($tanggal->format('Y-m-d'), collect());

            $sheet->setCellValue("A{$baris}", $tanggal->toDateString());
            $sheet->getStyle("A{$baris}")->getNumberFormat()->setFormatCode('dd-mm-yyyy');

            foreach (self::URUTAN_WAKTU as $i => $waktu) {
                $kolom = chr(66 + $i); // B, C, D, E
                $dataWaktu = $barisTanggalIni->firstWhere('waktu_dispensasi', $waktu);
                if ($dataWaktu) {
                    $sheet->setCellValue("{$kolom}{$baris}", 'DIS');
                    if (! empty($dataWaktu->keterangan)) {
                        $this->tulisKomentar($sheet, "{$kolom}{$baris}", $dataWaktu->keterangan);
                    }
                }
            }

            $baris++;
        }

        $sheet->getStyle("A{$headerBaris}:E" . ($baris - 1))
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        return $baris;
    }

    private function tulisKomentar(Worksheet $sheet, string $koordinat, string $teks): void
    {
        [$kolom, $baris] = Coordinate::coordinateFromString($koordinat);

        $komentar = $sheet->getComment($koordinat);
        $komentar->getText()->createTextRun($teks);
        $komentar->setMarginLeft($this->jarakDariKiri($sheet, $kolom) . 'pt');
        $komentar->setMarginTop(max(0, $this->jarakDariAtas($sheet, (int) $baris) - 4) . 'pt');
        $komentar->setWidth('140pt');
        $komentar->setHeight('48pt');
    }

    /** Total lebar kolom A s/d kolom sel (dalam point), supaya kotak muncul di sisi kanan sel. */
    private function jarakDariKiri(Worksheet $sheet, string $kolom): float
    {
        $indeks = Coordinate::columnIndexFromString($kolom);
        $total = 0.0;

        for ($i = 1; $i <= $indeks; $i++) {
            $lebar = $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->getWidth();
            if ($lebar < 0) {
                $lebar = 8.43; // lebar default Excel jika belum diset
            }
            $total += ($lebar * 7 + 5) * 0.75; // konversi karakter -> piksel -> point
        }

        return round($total, 2);
    }

    /** Total tinggi baris di atas baris sel (dalam point). */
    private function jarakDariAtas(Worksheet $sheet, int $baris): float
    {
        $total = 0.0;

        for ($r = 1; $r < $baris; $r++) {
            $tinggi = $sheet->getRowDimension($r)->getRowHeight();
            if ($tinggi < 0) {
                $tinggi = 15.0; // tinggi default Excel jika belum diset
            }
            $total += $tinggi;
        }

        return round($total, 2);
    }

    private function labelPeriode(Carbon $tanggalAwal, Carbon $tanggalAkhir): string
    {
        $format = fn (Carbon $t) => (self::NAMA_HARI[$t->format('l')] ?? $t->format('l'))
            . ', ' . $t->translatedFormat('d F Y');

        return $format($tanggalAwal) . ' s/d ' . $format($tanggalAkhir);
    }

    private function tentukanTargetBulan(Collection $dispensasis): Collection
    {
        if ($this->tahun && $this->bulan) {
            return collect([[(int) $this->tahun, (int) $this->bulan]]);
        }

        return $dispensasis
            ->map(fn ($d) => [(int) $d->tanggal_dispensasi->format('Y'), (int) $d->tanggal_dispensasi->format('n')])
            ->unique(fn ($p) => sprintf('%04d%02d', $p[0], $p[1]))
            ->sortBy(fn ($p) => sprintf('%04d%02d', $p[0], $p[1]))
            ->values();
    }

    private function ambilDispensasi(): Collection
    {
        $query = Dispensasi::where('status_pengajuan', 'disetujui')
            ->with('pegawai');

        if ($this->tahun) {
            $query->whereYear('tanggal_dispensasi', $this->tahun);
        }
        if ($this->bulan) {
            $query->whereMonth('tanggal_dispensasi', $this->bulan);
        }
        if ($this->unitOrganisasiId) {
            $query->whereIn('unit_organisasi_id', $this->unitIds());
        }

        return $query->orderBy('tanggal_dispensasi')->get();
    }

    private function unitIds(): array
    {
        if ($this->unitIdsCache !== null) {
            return $this->unitIdsCache;
        }

        $unit = UnitOrganisasi::find($this->unitOrganisasiId);

        return $this->unitIdsCache = $unit ? $unit->selfAndDescendantIds() : [$this->unitOrganisasiId];
    }
}