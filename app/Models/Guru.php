<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends User
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    protected static function booted(): void
    {
        static::addGlobalScope('guru', function (Builder $builder) {
            $builder->where('role', UserRole::GURU);
        });
    }

    public function jurnalKelas(): HasMany
    {
        return $this->hasMany(JurnalKelas::class, 'guru_id');
    }

    public function penilaianPendaftar(): HasMany
    {
        return $this->hasMany(PenilaianCalonSantri::class, 'penguji_id');
    }
}
