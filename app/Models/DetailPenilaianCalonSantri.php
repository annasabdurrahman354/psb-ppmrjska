<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents the score given for a specific assessment indicator during an applicant's evaluation.
 *
 * @property string $id // ULID
 * @property string $penilaian_calon_santri_id
 * @property string $indikator_penilaian_id
 * @property int $nilai // Score given
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PenilaianCalonSantri $penilaian
 * @property-read \App\Models\IndikatorPenilaian $indikatorPenilaian
 */
class DetailPenilaianCalonSantri extends Model
{
    use HasFactory, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'detail_penilaian_calon_santri';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'penilaian_calon_santri_id',
        'indikator_penilaian_id',
        'nilai',
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
            'nilai' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the overall assessment this detail belongs to.
     */
    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(PenilaianCalonSantri::class, 'penilaian_calon_santri_id');
    }

    /**
     * Get the specific indicator being scored.
     */
    public function indikatorPenilaian(): BelongsTo
    {
        return $this->belongsTo(IndikatorPenilaian::class, 'indikator_penilaian_id');
    }
}
