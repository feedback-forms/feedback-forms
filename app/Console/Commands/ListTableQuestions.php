<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feedback_template;
use App\Models\Question;

class ListTableQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:list-table-questions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all questions for the table template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tableTemplate = Feedback_template::where('name', 'templates.feedback.table')->first();

        if (!$tableTemplate) {
            $this->error('Table template not found!');
            return 1;
        }

        $questions = Question::whereNull('feedback_id')
            ->where('feedback_template_id', $tableTemplate->id)
            ->orderBy('order')
            ->get();

        if ($questions->isEmpty()) {
            $this->warn('No questions found for the table template.');
            return 0;
        }

        $this->info('Found ' . $questions->count() . ' questions for the table template:');
        $this->newLine();

        $headers = ['ID', 'Question', 'Type', 'Order'];
        $rows = [];

        foreach ($questions as $question) {
            $rows[] = [
                $question->id,
                $question->question,
                $question->question_template->type ?? 'unknown',
                $question->order
            ];
        }

        $this->table($headers, $rows);

        return 0;
    }
}
