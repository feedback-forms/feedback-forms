<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['code' => 'M', 'name' => 'Metall'],
            ['code' => 'I', 'name' => 'Informatik'],
            ['code' => 'K', 'name' => 'Kaufleute'],
            ['code' => 'TAI', 'name' => 'Technischer Assistent'],
            ['code' => 'BVJ', 'name' => 'Berufsvorbereitung']
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }
    }
}