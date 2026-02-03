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
        User::create([
            'name' => 'Fahmi',
            'username' => 'fahmi',
            'email' => 'superadmin@inkalum.com',
            'password' => Hash::make('123456'),
            'role' => 'superadmin',
            'department_id' => 18
        ]);

        // ADMIN
        User::create([
            'name' => 'Admin Produksi',
            'username' => 'admin',
            'email' => 'admin@inkalum.com',
            'password' => Hash::make('admin'),
            'role' => 'admin',
            'department_id' => 1
        ]);
    }

}
        