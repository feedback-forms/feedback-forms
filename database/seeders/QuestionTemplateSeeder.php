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
            'min_value' => 1,
            'max_value' => 5,
        ]);

        Question_template::updateOrCreate([
            'type' => 'checkboxes',
            'max_value' => 4,
        ]);

        Question_template::updateOrCreate([
            'type' => 'textarea',
<<<<<<< Updated upstream
            'max_value' => 3,
=======
            'max' => 3,
>>>>>>> Stashed changes
        ]);
    }
}
