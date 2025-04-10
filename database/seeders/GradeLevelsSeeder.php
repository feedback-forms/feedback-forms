<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\GradeLevel;
use Illuminate\Database\Seeder;

class GradeLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gradeLevels = [
            ['name' => '10. Jahrgang'],
            ['name' => '11. Jahrgang'],
            ['name' => '12. Jahrgang'],
            ['name' => '13. Jahrgang']
        ];

        foreach ($gradeLevels as $grade) {
            GradeLevel::updateOrCreate(['name' => $grade['name']], $grade);
        }
    }
}
