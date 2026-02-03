<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Anodize',
            'Extrusion',
            'Painting',
            'VPC',
            'HPC',
            'Oven Aging',
            'Receh',
            'QC',
            'Export',
            'Wood Grain',
            'WorkShop',
            'Dies',
            'Melting',
            'GBB',
            'GBJ',
            'PPIC',
            'Purchasing',
            'IT',
            'Accounting',
            'Gudang Jatake',
        ];

        foreach ($departments as $name) {
            Department::create([
                'name' => $name
            ]);
        }
    }
}
