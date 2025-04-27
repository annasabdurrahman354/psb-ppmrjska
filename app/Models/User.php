<?php

namespace App\Models;

use App\Enums\UsersStatus;
use App\Enums\UserRole;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Represents a User (can be Guru or Santri).
 *
 * @property string $id // ULID
 * @property string $nama
 * @property string $nama_panggilan
 * @property string $jenis_kelamin
 * @property string $nomor_telepon
 * @property string $email
 * @property UserRole $role // Use the Enum type hint
 * @property string $status
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 */

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasFactory, Notifiable, HasUlids, HasApiTokens, HasPanelShield, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'nama_panggilan',
        'jenis_kelamin',
        'nomor_telepon',
        'email',
        'role',
        'status',
        'password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'email_verified_at' => 'datetime', // Default Laravel cast
            'password' => 'hashed',          // Default Laravel cast
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'status' => UsersStatus::class, // Cast the 'role' column to the UserRole Enum
            'role' => UserRole::class, // Cast the 'role' column to the UserRole Enum
        ];
    }

    public function getFilamentName(): string
    {
        return $this->nama;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return
            "https://ui-avatars.com/api/?background=random&size=256&rounded=true&name=".str_replace(" ", "+", $this->nama);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
