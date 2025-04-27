<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UsersStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Uid\Ulid;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class
        ]);
        $user = User::create([
            'nama' => 'Annas Abdurrahman',
            'nama_panggilan' => 'Annas',
            'jenis_kelamin' => 'laki-laki',
            'nomor_telepon' => '085786537295',
            'email' => 'annasabdurrahman354@gmail.com',
            'role' => UserRole::SANTRI->value,
            'status' => UsersStatus::LULUS->value,
            'password' => Hash::make('sanna'),
            'email_verified_at' => now(),
        ]);

        Artisan::call('shield:super-admin', ['--user' => $user->id]);
        $this->call([
            InitialSeeder::class,
        ]);
    }
}
