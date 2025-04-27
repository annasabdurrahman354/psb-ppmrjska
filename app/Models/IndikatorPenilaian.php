<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents an assessment indicator used during registration evaluation.
 *
 * @property string $id // ULID
 * @property string|null $pendaftaran_id
 * @property string|null $nama
 * @property float $bobot // Cast from decimal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pendaftaran|null $pendaftaran
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DetailPenilaianCalonSantri> $detailPenilaianCalonSantri
 * @property-read int|null $detail_penilaian_calon_santri_count
 */
class IndikatorPenilaian extends Model
{
    use HasFactory, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'indikator_penilaian';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pendaftaran_id',
        'nama',
        'bobot',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bobot' => 'float', // Cast decimal to float
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the registration event this indicator belongs to.
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    /**
     * Get the detailed assessment scores related to this indicator.
     */
    public function detailPenilaianCalonSantri(): HasMany
    {
        return $this->hasMany(DetailPenilaianCalonSantri::class, 'indikator_penilaian_id');
    }
}
