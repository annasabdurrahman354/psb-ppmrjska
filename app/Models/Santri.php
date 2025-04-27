<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Santri extends User
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('santri', function (Builder $builder) {
            $builder->where('role', UserRole::SANTRI);
        });
    }

    /**
     * Get the biodata record associated with the santri user.
     * Only applicable if the user's role is 'santri'.
     */
    public function biodata(): HasOne
    {
        return $this->hasOne(BiodataSantri::class, 'user_id');
    }

    /**
     * The classes that the santri belongs to.
     * Only applicable if the user's role is 'santri'.
     */
    public function kelas(): BelongsToMany
    {
        // Specify the pivot model if it exists and has extra attributes/timestamps
        return $this->belongsToMany(Kelas::class, 'kelas_santri', 'santri_id', 'kelas_id')
            ->using(KelasSantri::class) // Specify the pivot model
            ->withPivot(['id', 'tanggal_masuk', 'tanggal_lulus']) // Include pivot columns
            ->withTimestamps(); // If pivot table has timestamps (though not in schema)
    }

    /**
     * Get the attendance records for this user (if they are a santri).
     * This relationship only applies if the user's role is 'santri'.
     */
    public function presensi(): HasMany
    {
        // Ensure this user is a santri before accessing, or handle empty collection
        return $this->hasMany(Presensi::class, 'santri_id');
    }

    /**
     * Get the applicant assessments conducted by this user (as penguji).
     */
    public function penilaianPendaftar(): HasMany
    {
        return $this->hasMany(PenilaianCalonSantri::class, 'penguji_id');
    }
}
