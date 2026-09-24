<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Route;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'jabatan_id',
        'unit_organisasi_id',
        'is_plt',
        'is_active',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_active'            => 'boolean',
            'is_plt'               => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function unitOrganisasi(): BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class);
    }

    public function dispensasiDiinput(): HasMany
    {
        return $this->hasMany(Dispensasi::class, 'admin_departemen_id');
    }

    public function dispensasiDiproses(): HasMany
    {
        return $this->hasMany(Dispensasi::class, 'diproses_oleh_id');
    }

    public function dispensasiMenungguSaya(): HasMany
    {
        return $this->hasMany(Dispensasi::class, 'approver_saat_ini_id')
            ->where('status_pengajuan', 'menunggu_persetujuan');
    }

    public function suratDiterbitkan(): HasMany
    {
        return $this->hasMany(Dispensasi::class, 'dicetak_oleh_id');
    }

    public function dashboardRoute(): string
    {
        $routeName = match ($this->role) {
            'admin_sdm'        => 'sdm.dashboard',
            'admin_departemen' => 'dispensasi.index',
            'approver'         => 'dashboard.approver',
            default            => null,
        };

        if ($routeName && Route::has($routeName)) {
            return route($routeName);
        }

        return '/';
    }

    public function isAdminSdm(): bool
    {
        return $this->role === 'admin_sdm';
    }

    public function isAdminDepartemen(): bool
    {
        return $this->role === 'admin_departemen';
    }

    public function isApprover(): bool
    {
        return $this->role === 'approver';
    }

    public function isPemberiKeputusan(): bool
    {
        return $this->isApprover();
    }

    public function jabatanLengkap(): string
    {
        if ($this->role === 'admin_sdm') {
            return 'Admin SDM';
        }

        if ($this->role === 'admin_departemen') {
            return 'Admin Departemen';
        }

        $namaJabatan = $this->jabatan?->nama ?? $this->name;
        $namaUnit = $this->unitOrganisasi?->nama;

        $label = $namaUnit ? "{$namaJabatan} {$namaUnit}" : $namaJabatan;

        return $this->is_plt ? "Plt. {$label}" : $label;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function hasJabatanKode(string $kode): bool
    {
        return $this->jabatan?->kode === $kode;
    }

    public function scopeJabatanKode($query, string $kode)
    {
        return $query->whereHas('jabatan', fn ($q) => $q->where('kode', $kode));
    }

  
    public static function approverUntuk(int $jabatanId, UnitOrganisasi $unit): ?self
    {
        foreach ($unit->selfAndAncestors() as $unitAcuan) {
            $user = self::query()
                ->active()
                ->where('jabatan_id', $jabatanId)
                ->where('unit_organisasi_id', $unitAcuan->id)
                ->first();

            if ($user) {
                return $user;
            }
        }

        return self::query()
            ->active()
            ->where('jabatan_id', $jabatanId)
            ->whereNull('unit_organisasi_id')
            ->first();
    }
}