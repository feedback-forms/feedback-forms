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
            SchoolOptionsSeeder::class,
            RegisterKeySeeder::class,
            UserSeeder::class
        ]);
    }
}
