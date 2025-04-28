<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Represents a type of document required for registration.
 *
 * @property string $id // ULID
 * @property string|null $pendaftaran_id
 * @property string $nama
 * @property string|null $keterangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Pendaftaran|null $pendaftaran
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DokumenCalonSantri> $dokumenCalonSantri // Submitted documents of this type
 * @property-read int|null $dokumen_calon_santri_count
 */
class DokumenPendaftaran extends Model implements HasMedia
{
    use HasFactory, HasUlids, InteractsWithMedia;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dokumen_pendaftaran';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pendaftaran_id',
        'nama',
        'keterangan',
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
     * Get the registration event this document belongs to.
     */
    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    /**
     * Get the actual submitted documents by applicants corresponding to this document type.
     */
    public function dokumenCalonSantri(): HasMany
    {
        return $this->hasMany(DokumenCalonSantri::class, 'dokumen_pendaftaran_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('dokumen_pendaftaran_template')
            ->singleFile();
    }
}
