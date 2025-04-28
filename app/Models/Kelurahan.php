<?php

namespace App\Models;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a Village/Sub-district (Kelurahan).
 *
 * @property int $id // Note: Schema uses bigIncrements, so it's technically a bigint
 * @property string $nama
 * @property int|null $kecamatan_id // Note: Schema uses unsignedBigInteger
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Kecamatan|null $kecamatan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BiodataSantri> $biodataSantri
 * @property-read int|null $biodata_santri_count
 */
class Kelurahan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kelurahan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'kecamatan_id',
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
     * Get the district (kecamatan) that owns the village.
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public static function getForm()
    {
        return [
            Select::make('kecamatan_id')
                ->relationship('kecamatan', 'nama')
                ->searchable()
                ->required(),
            TextInput::make('nama')
                ->required(),
        ];
    }
}
