<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik',
        'nama_pegawai',
        'jabatan_id',
        'unit_organisasi_id',
        'no_telepon',
        'email',
        'status',
    ];

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function unitOrganisasi(): BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class);
    }

    public function dispensasis(): HasMany
    {
        return $this->hasMany(Dispensasi::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeNonaktif($query)
    {
        return $query->where('status', 'nonaktif');
    }

    public function scopeUnit($query, int $unitOrganisasiId)
    {
        return $query->where('unit_organisasi_id', $unitOrganisasiId);
    }

    public function scopeJabatan($query, int $jabatanId)
    {
        return $query->where('jabatan_id', $jabatanId);
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function isNonaktif(): bool
    {
        return $this->status === 'nonaktif';
    }

    public function carikanApprover(int $urutan = 1): ?User
    {
        if (! $this->unit_organisasi_id || ! $this->jabatan_id) {
            return null;
        }

        return AlurApproval::carikanApprover(
            $this->unitOrganisasi ?? $this->unitOrganisasi()->firstOrFail(),
            $this->jabatan ?? $this->jabatan()->firstOrFail(),
            $urutan
        );
    }
}