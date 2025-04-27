<?php

namespace App\Models;

use App\Enums\Sesi;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Represents a class journal entry.
 *
 * @property string $id // ULID
 * @property string $kelas_id
 * @property \Illuminate\Support\Carbon $tanggal
 * @property string $sesi
 * @property string|null $guru_id
 * @property string|null $materi_awal_type // For polymorphic relation
 * @property int|null $materi_awal_id    // For polymorphic relation
 * @property string|null $materi_akhir_type // For polymorphic relation
 * @property int|null $materi_akhir_id   // For polymorphic relation
 * @property int|null $halaman_awal
 * @property int|null $halaman_akhir
 * @property int|null $ayat_awal
 * @property int|null $ayat_akhir
 * @property string|null $link_rekaman
 * @property string|null $keterangan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Kelas $kelas
 * @property-read \App\Models\User|null $guru
 * @property-read Model|\Eloquent $materiAwal // Polymorphic relation result
 * @property-read Model|\Eloquent $materiAkhir // Polymorphic relation result
 */
class JurnalKelas extends Model
{
    use HasFactory, HasUlids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jurnal_kelas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kelas_id',
        'tanggal',
        'sesi',
        'guru_id',
        'materi_awal_type',
        'materi_awal_id',
        'materi_akhir_type',
        'materi_akhir_id',
        'halaman_awal',
        'halaman_akhir',
        'ayat_awal',
        'ayat_akhir',
        'link_rekaman',
        'keterangan',
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
            'sesi' => Sesi::class,
            'halaman_awal' => 'integer',
            'halaman_akhir' => 'integer',
            'ayat_awal' => 'integer',
            'ayat_akhir' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            // materi_awal_id and materi_akhir_id are usually integers,
            // but casting depends on the related polymorphic models' key types.
            // No explicit cast needed unless they are non-standard.
        ];
    }

    /**
     * Get the class associated with this journal entry.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Get the teacher (guru) associated with this journal entry.
     */
    public function guru(): BelongsTo
    {
        // Assuming 'guru_id' references the 'users' table
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Get the parent materi_awal model (polymorphic relationship).
     * Example: Could be a Surah, Juz, Book, etc.
     * You need corresponding models (e.g., Surah, Juz) with morphMany relationships defined.
     */
    public function materiAwal(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'materi_awal_type', 'materi_awal_id');
    }

    public function materiAkhir(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'materi_akhir_type', 'materi_akhir_id');
    }
}
