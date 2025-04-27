<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a specific wave or batch within a registration period.
 *
 * @property string $id // ULID
 * @property string|null $pendaftaran_id
 * @property int $nomor_gelombang
 * @property \Illuminate\Support\Carbon $awal_pendaftaran
 * @property \Illuminate\Support\Carbon $akhir_pendaftaran
 * @property array $timeline // Cast from JSON
 * @property string|null $link_grup
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Pendaftaran|null $pendaftaran
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CalonSantri> $calonSantri
 * @property-read int|null $calon_santri_count
 */
class GelombangPendaftaran extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gelombang_pendaftaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pendaftaran_id',
        'nomor_gelombang',
        'awal_pendaftaran',
        'akhir_pendaftaran',
        'timeline',
        'link_grup',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nomor_gelombang' => 'integer',
            'awal_pendaftaran' => 'datetime',
            'akhir_pendaftaran' => 'datetime',
            'timeline' => 'array', // Cast JSON to array
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the main registration event this wave belongs to.
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    /**
     * Get the applicants registered in this wave.
     */
    public function calonSantri(): HasMany
    {
        return $this->hasMany(CalonSantri::class, 'gelombang_pendaftaran_id');
    }
}
