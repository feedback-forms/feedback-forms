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
            ['name' => '5a', 'grade_level_id' => 3],
            ['name' => '5b', 'grade_level_id' => 3],
            ['name' => '6a', 'grade_level_id' => 3],
            ['name' => '6b', 'grade_level_id' => 3],
        ];

        foreach ($schoolClasses as $class) {
            SchoolClass::UpdateOrcreate($class);
        }
    }
}
