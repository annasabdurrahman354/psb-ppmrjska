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
        $user1 = User::create([
            'nama' => 'Ardho Masiech Firdaus',
            'nama_panggilan' => 'Ardho',
            'jenis_kelamin' => 'laki-laki',
            'nomor_telepon' => '0895708149200',
            'email' => 'ardhomasfir@gmail.com',
            'role' => UserRole::SANTRI->value,
            'status' => UsersStatus::AKTIF->value,
            'password' => Hash::make('ohdra'),
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'nama' => 'Diaz Seno Hutomo',
            'nama_panggilan' => 'Diaz',
            'jenis_kelamin' => 'laki-laki',
            'nomor_telepon' => '0895322208791',
            'email' => 'diazsenohutomo2010@gmail.com',
            'role' => UserRole::SANTRI->value,
            'status' => UsersStatus::LULUS->value,
            'password' => Hash::make('didik'),
            'email_verified_at' => now(),
            ]);


        Artisan::call('shield:super-admin', ['--user' => $user->id]);
        Artisan::call('shield:super-admin', ['--user' => $user1->id]);
        Artisan::call('shield:super-admin', ['--user' => $user2->id]);

        $this->call([
            InitialSeeder::class,
        ]);
    }
}
