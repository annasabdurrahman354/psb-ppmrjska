<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model; // Changed from Pivot
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Represents a specific document file submitted by an applicant.
 * Connects an applicant (CalonSantri) with a required document type (DokumenPendaftaran).
 *
 * @property string $id // ULID Primary Key
 * @property string $calon_santri_id
 * @property string $dokumen_pendaftaran_id
 * @property string $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CalonSantri $calonSantri
 * @property-read \App\Models\DokumenPendaftaran $dokumenPendaftaran
 */
class DokumenCalonSantri extends Model implements HasMedia // Implement HasMedia
{
    use HasFactory, HasUlids, InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dokumen_calon_santri';

    /**
     * Indicates if the IDs are auto-incrementing.
     * Set to false because we are using ULIDs.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'calon_santri_id',
        'dokumen_pendaftaran_id',
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the applicant who submitted this document.
     */
    public function calonSantri(): BelongsTo
    {
        return $this->belongsTo(CalonSantri::class, 'calon_santri_id');
    }

    /**
     * Get the type of document that was required.
     */
    public function dokumenPendaftaran(): BelongsTo
    {
        return $this->belongsTo(DokumenPendaftaran::class, 'dokumen_pendaftaran_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('dokumen_calon_santri_berkas')
            ->singleFile();
    }
}
