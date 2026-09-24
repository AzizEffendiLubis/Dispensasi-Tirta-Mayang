<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Dispensasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_dispensasi',
        'pegawai_id',
        'unit_organisasi_id',
        'admin_departemen_id',
        'tanggal_pengajuan',
        'tanggal_dispensasi',
        'waktu_dispensasi',
        'keterangan',
        'bukti_pendukung',
        'status_pengajuan',
        'approver_saat_ini_id',
        'diproses_oleh_id',
        'catatan_persetujuan',
        'tanggal_keputusan',
        'nomor_surat_dispensasi',
        'tanggal_surat_dispensasi',
        'dicetak_oleh_id',
        'ditujukan_kepada_id',
        'token_verifikasi',
        'dicetak_pada',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan'        => 'date',
            'tanggal_dispensasi'       => 'date',
            'tanggal_keputusan'        => 'datetime',
            'tanggal_surat_dispensasi' => 'date',
            'dicetak_pada'             => 'datetime',
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function unitOrganisasi(): BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class);
    }

    public function adminDepartemen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_departemen_id');
    }

    public function approverSaatIni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_saat_ini_id');
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh_id');
    }

    public function dicetakOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicetak_oleh_id');
    }

    public function ditujukanKepada(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditujukan_kepada_id');
    }

    public function scopeMenungguPersetujuan($query)
    {
        return $query->where('status_pengajuan', 'menunggu_persetujuan');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status_pengajuan', 'disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status_pengajuan', 'ditolak');
    }

    public function scopeUnit($query, int $unitOrganisasiId)
    {
        return $query->where('unit_organisasi_id', $unitOrganisasiId);
    }

    public function scopePegawai($query, int $pegawaiId)
    {
        return $query->where('pegawai_id', $pegawaiId);
    }

    public function scopeBulan($query, int $bulan, int $tahun)
    {
        return $query->whereMonth('tanggal_dispensasi', $bulan)
                     ->whereYear('tanggal_dispensasi', $tahun);
    }

    public function scopeBulanPengajuan($query, int $bulan, int $tahun)
    {
        return $query->whereMonth('tanggal_pengajuan', $bulan)
                     ->whereYear('tanggal_pengajuan', $tahun);
    }

    public function scopePeriode($query, string $start, string $end)
    {
        return $query->whereBetween('tanggal_dispensasi', [$start, $end]);
    }

    public function scopeBelumDijadikanSurat($query)
    {
        return $query->where('status_pengajuan', 'disetujui')
            ->whereNull('nomor_surat_dispensasi');
    }

    /**
     * Daftar pengajuan yang saat ini menunggu keputusan dari user
     * tertentu. Sumber kebenarannya kolom approver_saat_ini_id, yang
     * diisi resolver AlurApproval saat pengajuan dibuat/dieskalasi —
     * bukan dihitung ulang dari departemen + role seperti dulu, karena
     * alur approval sekarang bisa diubah admin kapan saja per unit.
     */
    public function scopeUntukPemberiKeputusan($query, User $user)
    {
        return $query
            ->where('approver_saat_ini_id', $user->id)
            ->where('status_pengajuan', 'menunggu_persetujuan');
    }

    public function scopeSatuKelompokSurat($query, self $acuan)
    {
        return $query->where('unit_organisasi_id', $acuan->unit_organisasi_id)
            ->where('diproses_oleh_id', $acuan->diproses_oleh_id)
            ->where('tanggal_dispensasi', $acuan->tanggal_dispensasi)
            ->where('status_pengajuan', 'disetujui')
            ->whereNull('nomor_surat_dispensasi');
    }

    public function scopeSatuSurat($query, string $nomorSurat)
    {
        return $query->where('nomor_surat_dispensasi', $nomorSurat);
    }

    public function scopeSatuPengajuanMenunggu($query, self $acuan)
    {
        return $query->where('pegawai_id', $acuan->pegawai_id)
            ->where('tanggal_dispensasi', $acuan->tanggal_dispensasi)
            ->where('status_pengajuan', 'menunggu_persetujuan');
    }

    public function scopeSatuKelompokPegawaiTanggal($query, self $acuan)
    {
        return $query->where('pegawai_id', $acuan->pegawai_id)
            ->where('tanggal_dispensasi', $acuan->tanggal_dispensasi);
    }

    public function isMenungguPersetujuan(): bool
    {
        return $this->status_pengajuan === 'menunggu_persetujuan';
    }

    public function isDisetujui(): bool
    {
        return $this->status_pengajuan === 'disetujui';
    }

    public function isDitolak(): bool
    {
        return $this->status_pengajuan === 'ditolak';
    }

    public function sudahDiputuskan(): bool
    {
        return in_array($this->status_pengajuan, ['disetujui', 'ditolak']);
    }

    public function isSudahDicetak(): bool
    {
        return $this->nomor_surat_dispensasi !== null;
    }

    public function keteranganStatusSurat(): ?string
    {
        if (! $this->isDisetujui()) {
            return null;
        }

        return $this->isSudahDicetak() ? null : 'Belum dijadikan e-dispensasi';
    }

    public function tentukanApproverAwal(): ?User
    {
        $pegawai = $this->pegawai ?? $this->pegawai()->first();
        $approver = $pegawai?->carikanApprover(1);

        $this->approver_saat_ini_id = $approver?->id;

        return $approver;
    }

    /**
     * Eskalasi pengajuan ke tahap approval berikutnya (kalau ada
     * baris alur_approvals dengan urutan lebih tinggi untuk unit +
     * jabatan pengaju yang sama). Dipakai mis. saat approver saat ini
     * berhalangan/nonaktif.
     */
    public function eskalasi(int $urutanBerikutnya): ?User
    {
        $pegawai = $this->pegawai ?? $this->pegawai()->first();
        $approver = $pegawai?->carikanApprover($urutanBerikutnya);

        if ($approver) {
            $this->approver_saat_ini_id = $approver->id;
        }

        return $approver;
    }

    public function labelInstansiPenyetuju(): string
    {
        $unit = $this->unitOrganisasi ?? $this->unitOrganisasi()->first();

        if (! $unit) {
            return '-';
        }

        return trim(Str::upper($unit->labelTingkat()) . ' ' . Str::upper($unit->nama));
    }

    public static function generateNomor(): string
    {
        return DB::transaction(function () {
            $tahun = now()->format('Y');
            $bulan = now()->format('m');
            $last = self::whereYear('tanggal_pengajuan', $tahun)
                        ->whereMonth('tanggal_pengajuan', $bulan)
                        ->lockForUpdate()
                        ->orderBy('id', 'desc')
                        ->first();
            $urutan = $last ? intval(substr($last->nomor_dispensasi, -5)) + 1 : 1;
            return sprintf('DISP/%s/%s/%05d', $tahun, $bulan, $urutan);
        });
    }
}