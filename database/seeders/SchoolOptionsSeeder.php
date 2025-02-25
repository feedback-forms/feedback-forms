<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{SchoolYear, Department, GradeLevel, SchoolClass, Subject};

class SchoolOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // School Years
        $schoolYears = [
            ['name' => '2023/24'],
            ['name' => '2024/25'],
        ];

        foreach ($schoolYears as $year) {
            SchoolYear::create($year);
        }

        // Departments
        $departments = [
            ['code' => 'AIT', 'name' => 'Automatisierungstechnik'],
            ['code' => 'IT', 'name' => 'Informationstechnologie'],
            ['code' => 'ET', 'name' => 'Elektronik'],
            ['code' => 'MB', 'name' => 'Maschinenbau'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Grade Levels
        $gradeLevels = [
            ['name' => '1. Jahrgang', 'level' => 1],
            ['name' => '2. Jahrgang', 'level' => 2],
            ['name' => '3. Jahrgang', 'level' => 3],
            ['name' => '4. Jahrgang', 'level' => 4],
            ['name' => '5. Jahrgang', 'level' => 5],
        ];

        foreach ($gradeLevels as $grade) {
            GradeLevel::create($grade);
        }

        // School Classes
        $schoolClasses = [
            ['name' => '5a', 'grade_level_id' => 5],
            ['name' => '5b', 'grade_level_id' => 5],
            ['name' => '6a', 'grade_level_id' => 5],
            ['name' => '6b', 'grade_level_id' => 5],
        ];

        foreach ($schoolClasses as $class) {
            SchoolClass::create($class);
        }

        // Subjects
        $subjects = [
            ['code' => 'math', 'name' => 'Mathematik'],
            ['code' => 'english', 'name' => 'Englisch'],
            ['code' => 'science', 'name' => 'Naturwissenschaften'],
            ['code' => 'history', 'name' => 'Geschichte'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}