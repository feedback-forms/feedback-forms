<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\FeedbackTemplate;
use App\Models\QuestionTemplate;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the feedback templates
        $targetTemplate = FeedbackTemplate::where('name', 'templates.feedback.target')->first();
        $smileyTemplate = FeedbackTemplate::where('name', 'templates.feedback.smiley')->first();
        $checkboxTemplate = FeedbackTemplate::where('name', 'templates.feedback.checkbox')->first();
        $tableTemplate = FeedbackTemplate::where('name', 'templates.feedback.table')->first();

        // Find question templates
        $rangeTemplate = QuestionTemplate::where('type', 'range')->first();
        $textTemplate = QuestionTemplate::where('type', 'textarea')->first()
            ?? QuestionTemplate::firstOrCreate(['type' => 'text']);
        $checkboxTemplate = QuestionTemplate::where('type', 'checkboxes')->first();

        // Clear existing questions that aren't linked to a feedback (survey)
        Question::whereNull('feedback_id')->delete();

        // Seed target template questions
        if ($targetTemplate && $rangeTemplate) {
            $targetStatements = [
                'Ich lerne im Unterricht viel.',
                'Die Lehrkraft hat ein großes Hintergrundwissen.',
                'Die Lehrkraft ist immer gut vorbereitet.',
                'Die Lehrkraft zeigt Interesse an ihren Schülern.',
                'Die Lehrkraft sorgt für ein gutes Lernklima in der Klasse.',
                'Die Notengebung ist fair und nachvollziehbar.',
                'Ich konnte dem Unterricht immer gut folgen.',
                'Der Unterricht wird vielfältig gestaltet.'
            ];

            foreach ($targetStatements as $index => $statement) {
                Question::create([
                    'feedback_template_id' => $targetTemplate->id,
                    'question_template_id' => $rangeTemplate->id,
                    'feedback_id' => null, // Template questions not linked to a specific feedback
                    'question' => $statement,
                    'order' => $index + 1,
                ]);
            }
        }

        // Seed smiley template questions
        if ($smileyTemplate && $textTemplate) {
            Question::create([
                'feedback_template_id' => $smileyTemplate->id,
                'question_template_id' => $textTemplate->id,
                'feedback_id' => null,
                'question' => 'Positive Feedback',
                'order' => 1,
            ]);

            Question::create([
                'feedback_template_id' => $smileyTemplate->id,
                'question_template_id' => $textTemplate->id,
                'feedback_id' => null,
                'question' => 'Negative Feedback',
                'order' => 2,
            ]);
        }

        // Seed checkbox template questions
        if ($checkboxTemplate && $checkboxTemplate) {
            $checkboxQuestions = [
                'What do you like about this course?',
                'Which topics would you like to see covered in more detail?',
                'What resources were most helpful to you?'
            ];

            foreach ($checkboxQuestions as $index => $question) {
                Question::create([
                    'feedback_template_id' => $checkboxTemplate->id,
                    'question_template_id' => $checkboxTemplate->id,
                    'feedback_id' => null,
                    'question' => $question,
                    'order' => $index + 1,
                ]);
            }
        }

        // Seed table template questions
        if ($tableTemplate && $rangeTemplate) {
            $tableQuestions = [
                // Behavior questions
                '... ungeduldig',
                '... sicher im Auftreten',
                '... freundlich',
                '... energisch und aufbauend',
                '... tatkräftig, aktiv',
                '... aufgeschlossen',

                // Fairness questions
                '... bevorzugt manche Schülerinnen oder Schüler.',
                '... nimmt die Schülerinnen und Schüler ernst.',
                '... ermutigt und lobt viel.',
                '... entscheidet immer allein.',
                '... gesteht eigene Fehler ein.',

                // Class quality questions
                'Die Ziele des Unterrichts sind klar erkennbar.',
                'Der Lehrer redet zu viel.',
                'Der Lehrer schweift oft vom Thema ab.',
                'Die Fragen und Beiträge der Schülerinnen und Schüler werden ernst genommen.',
                'Die Sprache des Lehrers ist gut verständlich.',
                'Der Lehrer achtet auf Ruhe und Disziplin im Unterricht.',
                'Der Unterricht ist abwechslungsreich.',
                'Unterrichtsmaterialien sind ansprechend und gut verständlich gestaltet.',
                'Der Stoff wird ausreichend wiederholt und geübt.',

                // Evaluation questions
                'Die Themen der Schulaufgaben werden rechtzeitig vorher bekannt gegeben.',
                'Der Schwierigkeitsgrad der Leistungsnachweise entspricht dem der Unterrichtsinhalte.',
                'Die Bewertungen sind nachvollziehbar und verständlich.'
            ];

            foreach ($tableQuestions as $index => $question) {
                Question::create([
                    'feedback_template_id' => $tableTemplate->id,
                    'question_template_id' => $rangeTemplate->id,
                    'feedback_id' => null,
                    'question' => $question,
                    'order' => $index + 1,
                ]);
            }

            // Create the text questions for feedback
            $textTemplate = QuestionTemplate::where('type', 'text')->first()
                ?? QuestionTemplate::firstOrCreate(['type' => 'text']);

            $feedbackQuestions = [
                'Das hat mir besonders gut gefallen',
                'Das hat mir nicht gefallen',
                'Verbesserungsvorschläge'
            ];

            foreach ($feedbackQuestions as $index => $question) {
                Question::create([
                    'feedback_template_id' => $tableTemplate->id,
                    'question_template_id' => $textTemplate->id,
                    'feedback_id' => null,
                    'question' => $question,
                    'order' => count($tableQuestions) + $index + 1,
                ]);
            }
        }
    }
}
