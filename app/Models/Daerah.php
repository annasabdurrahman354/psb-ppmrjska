<?php

namespace App\Models;

use App\Enums\Wilayah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Filament\Forms;
use Filament\Tables;

/**
 * Represents a Region/Area (Daerah).
 *
 * @property int $id // Primary Key (int) - Assuming it's the PK
 * @property string|null $nama
 * @property string|null $provinsi // Province name within the region data itself
 * @property string|null $wilayah // Region identifier (e.g., 'barat', 'timur')
 *
 * Relationships:
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BiodataSantri> $biodataSantriSambung
 * @property-read int|null $biodata_santri_sambung_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BiodataSantri> $biodataAyahSambung
 * @property-read int|null $biodata_ayah_sambung_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BiodataSantri> $biodataIbuSambung
 * @property-read int|null $biodata_ibu_sambung_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BiodataSantri> $biodataWaliSambung
 * @property-read int|null $biodata_wali_sambung_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $santri // Santri associated via BiodataSantri
 * @property-read int|null $santri_count
 */
class Daerah extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daerah';

    /**
     * Indicates if the model should be timestamped.
     * Set to false because the table lacks created_at/updated_at columns.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'provinsi',
        'wilayah',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * (Optional, add any fields you want to hide)
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Add fields here if needed, e.g., 'provinsi' if redundant
    ];

    /**
     * Get the attributes that should be cast.
     * (Optional, add casts if needed, e.g., for specific data types)
     *
     * @return array<string, string>
     */
    protected $casts = [
        'wilayah' => Wilayah::class
    ];

    // =========================================================================
    // Relationships (from previous discussions)
    // =========================================================================

    /**
     * Get all the santri (users) associated with this Daerah through their biodata's 'daerah_sambung_id'.
     */
    public function santri(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,          // The final model we want to access
            BiodataSantri::class, // The intermediate model
            'daerah_sambung_id',  // Foreign key on the intermediate model (BiodataSantri) linking to Daerah
            'id',                 // Foreign key on the final model (User) linked by BiodataSantri
            'id',                 // Local key on the starting model (Daerah)
            'user_id'             // Local key on the intermediate model (BiodataSantri) linking to User
        );
    }

    public static function getForm(): array
    {
        return [
            Forms\Components\TextInput::make('nama')
                ->label('Nama Daerah')
                ->required()
                ->formatStateUsing(fn (string $state): string => proper($state))
                ->dehydrateStateUsing(fn (string $state): string => proper($state)),

            Forms\Components\TextInput::make('provinsi')
                ->label('Nama Provinsi')
                ->required(),

            Forms\Components\ToggleButtons::make('wilayah')
                ->label('Wilayah')
                ->options(Wilayah::class)
                ->required(),
        ];
    }

    public static function getColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('nama')
                ->label('Nama Daerah')
                ->sortable()
                ->searchable()
                ->formatStateUsing(fn (string $state) => proper($state)),

            Tables\Columns\TextColumn::make('provinsi')
                ->label('Nama Provinsi')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('wilayah')
                ->label('Wilayah')
                ->badge()
                ->sortable()
                ->searchable(),
        ];
    }
}
