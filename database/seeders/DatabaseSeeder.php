<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            ManagerSeeder::class,
            UnitSeeder::class,
            UserSeeder::class,
            ArsipSeeder::class,
        ]);
    }
}
