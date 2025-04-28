<?php

namespace App\Models;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Represents a City/Regency (Kota).
 *
 * @property int $id
 * @property string $nama
 * @property int|null $provinsi_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Provinsi|null $provinsi
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kecamatan> $kecamatan
 * @property-read int|null $kecamatan_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BiodataSantri> $biodataSantri
 * @property-read int|null $biodata_santri_count
 */
class Kota extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kota';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'provinsi_id',
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
     * Get the province (provinsi) that owns the city.
     */
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    /**
     * Get the districts (kecamatan) associated with the city.
     */
    public function kecamatan(): HasMany
    {
        return $this->hasMany(Kecamatan::class, 'kota_id');
    }

    /**
     * Get all the santri (users) associated with this Daerah through their biodata's 'daerah_sambung_id'.
     */
    public function santri(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,          // The final model we want to access
            BiodataSantri::class, // The intermediate model
            'kota_id',  // Foreign key on the intermediate model (BiodataSantri) linking to Daerah
            'id',                 // Foreign key on the final model (User) linked by BiodataSantri
            'id',                 // Local key on the starting model (Daerah)
            'user_id'             // Local key on the intermediate model (BiodataSantri) linking to User
        );
    }

    public static function getForm()
    {
        return [
            Select::make('provinsi_id')
                ->relationship('provinsi', 'nama')
                ->searchable()
                ->required(),
            TextInput::make('nama')
                ->required(),
        ];
    }
}
