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
        Feedback_template::updateOrCreate([
            'name' => 'templates.feedback.target'
        ]);

        Feedback_template::updateOrCreate([
            'name' => 'templates.feedback.smiley'
        ]);

        Feedback_template::updateOrCreate([
            'name' => 'templates.feedback.checkbox'
        ]);
    }
}
