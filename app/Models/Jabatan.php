<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'level_urutan',
    ];

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function alurApprovalsSebagaiPengaju(): HasMany
    {
        return $this->hasMany(AlurApproval::class, 'jabatan_pengaju_id');
    }

    public function alurApprovalsSebagaiApprover(): HasMany
    {
        return $this->hasMany(AlurApproval::class, 'jabatan_approver_id');
    }

    public function scopeKode($query, string $kode)
    {
        return $query->where('kode', $kode);
    }

    public function scopeUrut($query)
    {
        return $query->orderBy('level_urutan');
    }
}