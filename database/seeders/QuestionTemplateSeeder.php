<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuestionTemplate;

class QuestionTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        # Target Feedback
        QuestionTemplate::updateOrCreate([
            'type' => 'range',
            'min_value' => 1,
            'max_value' => 5,
        ]);

        QuestionTemplate::updateOrCreate([
            'type' => 'checkboxes',
            'max_value' => 4,
        ]);

        # Single checkbox for yes/no/na type questions
        QuestionTemplate::updateOrCreate([
            'type' => 'checkbox',
            'max_value' => 3, // Yes=1, No=2, N/A=3
        ]);

        QuestionTemplate::updateOrCreate([
            'type' => 'textarea',
            'max_value' => 3,
        ]);
    }
}
