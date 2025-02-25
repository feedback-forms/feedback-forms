<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Feedback_template;

class FeedbackTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Feedback_template::updateOrCreate(
            ['name' => 'templates.feedback.target'],
            ['title' => 'Target Survey']
        );

        Feedback_template::updateOrCreate(
            ['name' => 'templates.feedback.smiley'],
            ['title' => 'Smiley Survey']
        );

        Feedback_template::updateOrCreate(
            ['name' => 'templates.feedback.checkbox'],
            ['title' => 'Checkbox Survey']
        );

        Feedback_template::updateOrCreate(
            ['name' => 'templates.feedback.table'],
            ['title' => 'Table Survey']
        );
    }
}
