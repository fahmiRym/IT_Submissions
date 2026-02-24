<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    public function run(): void
    {
        // SUPERADMIN
        User::updateOrCreate(
            ['username' => 'fahmi'],
            [
                'name' => 'Fahmi',
                'email' => 'superadmin@inkalum.com',
                'password' => Hash::make('bismillah'),
                'role' => 'superadmin',
                'department_id' => 18,
                'is_active' => true
            ]
        );

        // ADMIN
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Produksi',
                'email' => 'admin@inkalum.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'department_id' => 1,
                'is_active' => true
            ]
        );
    }

}
        