<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FeedbackTemplate;

class FeedbackTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FeedbackTemplate::updateOrCreate(
            ['name' => 'templates.feedback.target'],
            ['title' => 'Target Survey']
        );

        FeedbackTemplate::updateOrCreate(
            ['name' => 'templates.feedback.smiley'],
            ['title' => 'Smiley Survey']
        );

        FeedbackTemplate::updateOrCreate(
            ['name' => 'templates.feedback.checkbox'],
            ['title' => 'Checkbox Survey']
        );

        FeedbackTemplate::updateOrCreate(
            ['name' => 'templates.feedback.table'],
            ['title' => 'Table Survey']
        );
    }
}
