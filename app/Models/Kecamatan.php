<?php

namespace App\Models;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a District (Kecamatan).
 *
 * @property int $id // Note: Schema uses bigIncrements, so it's technically a bigint
 * @property string $nama
 * @property int|null $kota_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Kota|null $kota
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kelurahan> $kelurahan
 * @property-read int|null $kelurahan_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BiodataSantri> $biodataSantri
 * @property-read int|null $biodata_santri_count
 */
class Kecamatan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kecamatan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'kota_id',
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
     * Get the city (kota) that owns the district.
     */
    public function kota(): BelongsTo
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    /**
     * Get the villages (kelurahan) associated with the district.
     */
    public function kelurahan(): HasMany
    {
        return $this->hasMany(Kelurahan::class, 'kecamatan_id');
    }

    public static function getForm()
    {
        return [
            Select::make('kota_id')
                ->relationship('kota', 'nama')
                ->searchable()
                ->required(),
            TextInput::make('nama')
                ->required(),
        ];
    }
}
