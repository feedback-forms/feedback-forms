<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question_template;

class QuestionTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        # Target Feedback
        Question_template::updateOrCreate([
            'type' => 'range',
            'min' => 1,
            'max' => 5,
        ]);

        Question_template::updateOrCreate([
            'type' => 'checkboxes',
            'max' => 4,
        ]);

        Question_template::updateOrCreate([
            'type' => 'textarea',
            'max' => 3,
        ])
    }
}
