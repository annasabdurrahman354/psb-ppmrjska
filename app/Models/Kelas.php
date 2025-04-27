<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a Class (Kelas).
 *
 * @property string $id // ULID
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $santri
 * @property-read int|null $santri_count
 */
class Kelas extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kelas';

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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * The santri (students) that belong to the class.
     * Uses the pivot table 'kelas_santri'.
     */
    public function santri(): BelongsToMany
    {
        // Specify the pivot model if it exists and has extra attributes/timestamps
        return $this->belongsToMany(User::class, 'kelas_santri', 'kelas_id', 'santri_id')
            ->using(KelasSantri::class) // Specify the pivot model
            ->withPivot(['id', 'tanggal_masuk', 'tanggal_lulus']) // Include pivot columns
            ->withTimestamps(); // If pivot table has timestamps (though not in schema)
    }
}
