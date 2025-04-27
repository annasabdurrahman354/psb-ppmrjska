<?php

namespace App\Models;

use App\Enums\StatusPenerimaan; // Import the Enum
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents the overall assessment result for an applicant.
 *
 * @property string $id // ULID
 * @property string $calon_santri_id
 * @property string $penguji_id // User ID of the assessor
 * @property string $catatan // Assessment notes
 * @property int $rekomendasi_penguji // Assessor's recommendation (tinyInteger)
 * @property StatusPenerimaan $status_penerimaan // Use the Enum type hint
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CalonSantri $calonSantri
 * @property-read \App\Models\User $penguji // Assessor user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DetailPenilaianCalonSantri> $detailPenilaian
 * @property-read int|null $detail_penilaian_count
 */
class PenilaianCalonSantri extends Model
{
    use HasFactory, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'penilaian_calon_santri';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'calon_santri_id',
        'penguji_id',
        'catatan',
        'rekomendasi_penguji',
        'status_penerimaan',
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
            'rekomendasi_penguji' => 'integer', // Cast tinyInteger to integer
            'status_penerimaan' => StatusPenerimaan::class, // Cast to Enum
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the applicant being assessed.
     */
    public function calonSantri(): BelongsTo
    {
        return $this->belongsTo(CalonSantri::class, 'calon_santri_id');
    }

    /**
     * Get the user (assessor) who performed the assessment.
     */
    public function penguji(): BelongsTo
    {
        // Assuming 'penguji_id' references the 'users' table
        return $this->belongsTo(User::class, 'penguji_id');
    }

    /**
     * Get the detailed scores for this assessment.
     */
    public function detailPenilaian(): HasMany
    {
        return $this->hasMany(DetailPenilaianCalonSantri::class, 'penilaian_calon_santri_id');
    }
}
