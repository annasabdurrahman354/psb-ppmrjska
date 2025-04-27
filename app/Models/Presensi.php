<?php

namespace App\Models;

use App\Enums\StatusKehadiran; // Import the Enum
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a student's attendance record for a class session.
 *
 * @property string $id // ULID
 * @property string $santri_id
 * @property string $kelas_id
 * @property \Illuminate\Support\Carbon $tanggal
 * @property string $sesi
 * @property StatusKehadiran $status_kehadiran // Use the Enum type hint
 * @property string|null $perekap_id // User who recorded the attendance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $santri
 * @property-read \App\Models\Kelas $kelas
 * @property-read \App\Models\User|null $perekap // User who recorded
 */
class Presensi extends Model
{
    use HasFactory, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'presensi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'santri_id',
        'kelas_id',
        'tanggal',
        'sesi',
        'status_kehadiran',
        'perekap_id',
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
            'tanggal' => 'date',
            'status_kehadiran' => StatusKehadiran::class, // Cast to the Enum
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the student (santri) associated with this attendance record.
     */
    public function santri(): BelongsTo
    {
        // Assuming 'santri_id' references the 'users' table
        return $this->belongsTo(User::class, 'santri_id');
    }

    /**
     * Get the class associated with this attendance record.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Get the user who recorded this attendance (perekap).
     */
    public function perekap(): BelongsTo
    {
        // Assuming 'perekap_id' references the 'users' table
        return $this->belongsTo(User::class, 'perekap_id');
    }
}
