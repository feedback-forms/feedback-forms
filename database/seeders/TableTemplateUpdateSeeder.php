<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Feedback_template;
use App\Models\Question;
use App\Models\Question_template;
use Illuminate\Support\Facades\DB;

class TableTemplateUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tableTemplate = Feedback_template::where('name', 'templates.feedback.table')->first();

        if (!$tableTemplate) {
            $this->command->error('Table feedback template not found!');
            return;
        }

        $rangeTemplate = Question_template::where('type', 'range')->first();
        $textTemplate = Question_template::where('type', 'text')->first()
            ?? Question_template::firstOrCreate(['type' => 'text']);

        if (!$rangeTemplate) {
            $this->command->error('Range question template not found!');
            return;
        }

        // First, delete all existing questions for the table template that don't belong to a specific feedback
        DB::table('questions')
            ->where('feedback_template_id', $tableTemplate->id)
            ->whereNull('feedback_id')
            ->delete();

        $this->command->info('Deleted existing table template questions.');

        // Seed table template questions
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

        $this->command->info('Created ' . count($tableQuestions) . ' rating questions for table template.');

        // Create the text questions for feedback
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

        $this->command->info('Created ' . count($feedbackQuestions) . ' text feedback questions for table template.');
    }
}