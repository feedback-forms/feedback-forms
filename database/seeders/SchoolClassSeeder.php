<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolClasses = [
            ['name' => 'IFA 12 B', 'grade_level_id' => 3],
            ['name' => 'IFA 12 A', 'grade_level_id' => 3],
            ['name' => 'ITT 11 E', 'grade_level_id' => 2],
            ['name' => 'ITT 10 C', 'grade_level_id' => 1],
        ];

        foreach ($schoolClasses as $class) {
            SchoolClass::updateOrCreate(['name' => $class['name']], $class);
        }
    }
}
