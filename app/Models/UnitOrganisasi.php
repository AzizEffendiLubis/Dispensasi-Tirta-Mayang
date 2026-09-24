<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOrganisasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'tingkat',
        'kode',
        'nama',
        'is_active',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('urutan');
    }

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function dispensasis(): HasMany
    {
        return $this->hasMany(Dispensasi::class);
    }

    public function alurApprovals(): HasMany
    {
        return $this->hasMany(AlurApproval::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTingkat($query, string $tingkat)
    {
        return $query->where('tingkat', $tingkat);
    }

    public function isDivisi(): bool
    {
        return $this->tingkat === 'divisi';
    }

    public function isDepartemen(): bool
    {
        return $this->tingkat === 'departemen';
    }

    public function isSubdepartemen(): bool
    {
        return $this->tingkat === 'subdepartemen';
    }

    /**
     * @return array<int, self>
     */
    public function ancestors(): array
    {
        $hasil = [];
        $unit = $this->parent;

        while ($unit) {
            $hasil[] = $unit;
            $unit = $unit->parent;
        }

        return $hasil;
    }

    /**
     * @return array<int, self>
     */
    public function selfAndAncestors(): array
    {
        return array_merge([$this], $this->ancestors());
    }

    /**
     * @return array<int, self>
     */
    public function descendants(): array
    {
        $hasil = [];

        foreach ($this->children as $anak) {
            $hasil[] = $anak;
            $hasil = array_merge($hasil, $anak->descendants());
        }

        return $hasil;
    }

    /**
     * @return array<int, int>
     */
    public function selfAndDescendantIds(): array
    {
        return array_merge(
            [$this->id],
            array_map(fn (self $unit) => $unit->id, $this->descendants())
        );
    }

    public function labelTingkat(): string
    {
        return match ($this->tingkat) {
            'divisi' => 'Divisi',
            'departemen' => 'Departemen',
            'subdepartemen' => 'Sub Departemen',
            default => ucfirst($this->tingkat),
        };
    }
}