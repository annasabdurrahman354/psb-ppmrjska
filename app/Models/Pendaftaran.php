<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a registration period or event.
 *
 * @property string $id // ULID
 * @property int $tahun_pendaftaran
 * @property array $kontak_panitia // Cast from JSON
 * @property array $kontak_pengurus // Cast from JSON
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DokumenPendaftaran> $dokumenPendaftaran
 * @property-read int|null $dokumen_pendaftaran_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\IndikatorPenilaian> $indikatorPenilaian
 * @property-read int|null $indikator_penilaian_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GelombangPendaftaran> $gelombangPendaftaran
 * @property-read int|null $gelombang_pendaftaran_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CalonSantri> $calonSantri
 */
class Pendaftaran extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pendaftaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tahun_pendaftaran',
        'kontak_panitia',
        'kontak_pengurus',
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
            'tahun_pendaftaran' => 'integer',
            'kontak_panitia' => 'array', // Cast JSON to array
            'kontak_pengurus' => 'array', // Cast JSON to array
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the required documents for this registration.
     */
    public function dokumenPendaftaran(): HasMany
    {
        return $this->hasMany(DokumenPendaftaran::class, 'pendaftaran_id');
    }

    /**
     * Get the assessment indicators for this registration.
     */
    public function indikatorPenilaian(): HasMany
    {
        return $this->hasMany(IndikatorPenilaian::class, 'pendaftaran_id');
    }

    /**
     * Get the registration waves (batches) for this registration event.
     */
    public function gelombangPendaftaran(): HasMany
    {
        return $this->hasMany(GelombangPendaftaran::class, 'pendaftaran_id');
    }

    /**
     * Get all applicants (calon santri) registered under this pendaftaran through gelombang pendaftaran.
     */
    public function calonSantri(): HasManyThrough
    {
        return $this->hasManyThrough(
            CalonSantri::class,        // Final model
            GelombangPendaftaran::class, // Intermediate model
            'pendaftaran_id',           // Foreign key on GelombangPendaftaran table
            'gelombang_pendaftaran_id', // Foreign key on CalonSantri table
            'id',                       // Local key on Pendaftaran table
            'id'                        // Local key on GelombangPendaftaran table
        );
    }
}
