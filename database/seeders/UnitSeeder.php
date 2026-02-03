<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class UnitSeeder extends Seeder
{
    public function run(): void
{
    $data = [
        'Unit 1',
        'Unit 2',
        'Unit 3A',
        'Unit 3B',
        'Unit 3C',
        'Unit 4A',
        'Unit 4B',
        'Unit 5'
    ];

    foreach ($data as $name) {
        Unit::create(['name' => $name]);
    }
}
}
        