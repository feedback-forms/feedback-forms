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
            ['name' => '10. Jahrgang', 'level' => 1],
            ['name' => '11. Jahrgang', 'level' => 2],
            ['name' => '12. Jahrgang', 'level' => 3],
            ['name' => '13. Jahrgang', 'level' => 4]
        ];

        foreach ($gradeLevels as $grade) {
            GradeLevel::UpdateOrcreate($grade);
        }
    }
}
