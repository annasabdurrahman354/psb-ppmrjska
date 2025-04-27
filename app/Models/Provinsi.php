<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Represents a Province.
 *
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Kota> $kota
 * @property-read int|null $kota_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BiodataSantri> $biodataSantri
 * @property-read int|null $biodata_santri_count
 */
class Provinsi extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'provinsi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
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
     * Get the cities (kota) associated with the province.
     */
    public function kota(): HasMany
    {
        return $this->hasMany(Kota::class, 'provinsi_id');
    }

    /**
     * Get all the santri (users) associated with this Daerah through their biodata's 'daerah_sambung_id'.
     */
    public function santri(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,          // The final model we want to access
            BiodataSantri::class, // The intermediate model
            'provinsi_id',  // Foreign key on the intermediate model (BiodataSantri) linking to Daerah
            'id',                 // Foreign key on the final model (User) linked by BiodataSantri
            'id',                 // Local key on the starting model (Daerah)
            'user_id'             // Local key on the intermediate model (BiodataSantri) linking to User
        );
    }
}
