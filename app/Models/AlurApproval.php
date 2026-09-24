<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlurApproval extends Model
{
    use HasFactory;

    protected $table = 'alur_approvals';

    protected $fillable = [
        'unit_organisasi_id',
        'jabatan_pengaju_id',
        'jabatan_approver_id',
        'urutan',
    ];

    public function unitOrganisasi(): BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class);
    }

    public function jabatanPengaju(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_pengaju_id');
    }

    public function jabatanApprover(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_approver_id');
    }

    public function scopeUntukUnit($query, int $unitOrganisasiId)
    {
        return $query->where('unit_organisasi_id', $unitOrganisasiId);
    }

    public function scopeUntukJabatanPengaju($query, int $jabatanId)
    {
        return $query->where('jabatan_pengaju_id', $jabatanId);
    }

    public function scopeUrutan($query, int $urutan)
    {
        return $query->where('urutan', $urutan);
    }

    public static function carikanApprover(
        UnitOrganisasi $unit,
        Jabatan $jabatanPengaju,
        int $urutan = 1
    ): ?User {
        foreach ($unit->selfAndAncestors() as $unitAcuan) {
            $aturan = self::query()
                ->untukUnit($unitAcuan->id)
                ->untukJabatanPengaju($jabatanPengaju->id)
                ->urutan($urutan)
                ->first();

            if (! $aturan) {
                continue;
            }

            $user = User::approverUntuk($aturan->jabatan_approver_id, $unit);

            if ($user) {
                return $user;
            }
        }

        return null;
    }
}