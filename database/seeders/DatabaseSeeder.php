<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FeedbackTemplateSeeder::class,
            QuestionTemplateSeeder::class,
            RegisterKeySeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            GradeLevelsSeeder::class,
            SchoolClassSeeder::class,
            SchoolYearSeeder::class,
            SubjectSeeder::class,
            QuestionSeeder::class,
            SurveyResponseSeeder::class,
            CategoryAssignmentSeeder::class,
        ]);
    }
}
