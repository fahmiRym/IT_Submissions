<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    public function run(): void
    {
        // SUPERADMIN — firstOrCreate: hanya buat jika belum ada, TIDAK pernah update password
        User::firstOrCreate(
            ['username' => 'fahmi'],
            [
                'name'          => 'Fahmi',
                'email'         => 'superadmin@inkalum.com',
                'password'      => Hash::make('bismillah'),
                'role'          => 'superadmin',
                'department_id' => 18,
                'is_active'     => true
            ]
        );

        // ADMIN DEFAULT — firstOrCreate: hanya buat jika belum ada
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name'          => 'Admin Produksi',
                'email'         => 'admin@inkalum.com',
                'password'      => Hash::make('admin123'),
                'role'          => 'admin',
                'department_id' => 1,
                'is_active'     => true
            ]
        );
    }
}