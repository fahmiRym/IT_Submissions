<?php

namespace Database\Seeders;

use App\Models\Manager;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class ManagerSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
                'Produksi',
                'PPIC',
                'Finance'
        ];

            foreach ($data as $name) {
                Manager::create(['name' => $name]);
        }
    } 
}
        