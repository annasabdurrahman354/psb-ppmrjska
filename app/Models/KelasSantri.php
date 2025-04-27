<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Represents the pivot table connecting Kelas and User (Santri).
 * Includes additional pivot data like entry and graduation dates.
 *
 * @property string $id // ULID Primary Key on Pivot
 * @property string $santri_id // Foreign ULID to users
 * @property string $kelas_id // Foreign ULID to kelas
 * @property \Illuminate\Support\Carbon $tanggal_masuk
 * @property \Illuminate\Support\Carbon|null $tanggal_lulus
 * // Laravel automatically handles created_at/updated_at if using ->withTimestamps() in BelongsToMany
 */
class KelasSantri extends Pivot
{
    use HasUlids; // Use HasUlids if the pivot 'id' is a ULID

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
     * The table associated with the pivot model.
     *
     * @var string
     */
    protected $table = 'kelas_santri';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_lulus' => 'date',
        // Add timestamps if they exist on the pivot table and you use withTimestamps()
        // 'created_at' => 'datetime',
        // 'updated_at' => 'datetime',
    ];

    public function santri() {
        return $this->belongsTo(User::class, 'santri_id');
    }
    public function kelas() {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}

