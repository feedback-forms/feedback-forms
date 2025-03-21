<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FeedbackTemplate;
use App\Models\QuestionTemplate;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckboxSurveySetupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update required question templates
        $checkboxTemplate = QuestionTemplate::updateOrCreate(
            ['type' => 'checkbox'],
            ['max_value' => 3] // Yes=1, No=2, N/A=3
        );

        // Create or update feedback template
        $feedbackTemplate = FeedbackTemplate::updateOrCreate(
            ['name' => 'templates.feedback.checkbox'],
            ['title' => 'Checkbox Survey']
        );

        // Clear existing questions for this template
        Question::where('feedback_template_id', $feedbackTemplate->id)
            ->whereNull('feedback_id')
            ->delete();

        $this->command->info('Deleted existing checkbox template questions');

        // Add single checkbox (Yes/No/N/A) questions
        $yesNoQuestions = [
            'Der Unterricht ist gut vorbereitet.',
            'Die Aufgaben sind klar formuliert.',
            'Die Lehrkraft erklärt verständlich.',
            'Die Lehrkraft geht auf Fragen ein.',
            'Die Lehrkraft gibt konstruktives Feedback.',
            'Die Lehrkraft ist fair und respektvoll.',
            'Die Unterrichtsmaterialien sind hilfreich.',
            'Der Unterricht ist interessant gestaltet.',
            'Ich konnte dem Unterricht gut folgen.',
            'Ich habe neue Fähigkeiten erworben.',
            'Die Lernziele wurden klar kommuniziert.',
            'Die Leistungsanforderungen sind angemessen.'
        ];

        foreach ($yesNoQuestions as $index => $question) {
            Question::create([
                'feedback_template_id' => $feedbackTemplate->id,
                'question_template_id' => $checkboxTemplate->id,
                'feedback_id' => null,
                'question' => $question,
                'order' => $index + 1,
                'category' => 'checkbox_feedback'
            ]);
        }

        $this->command->info('Created ' . count($yesNoQuestions) . ' checkbox template questions');
    }
}