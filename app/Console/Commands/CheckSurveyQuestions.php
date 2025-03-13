<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feedback;
use App\Models\Question;

class CheckSurveyQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-survey-questions {survey_id : The ID of the survey to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check questions for a specific survey';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $surveyId = $this->argument('survey_id');

        $survey = Feedback::find($surveyId);

        if (!$survey) {
            $this->error("Survey with ID {$surveyId} not found!");
            return 1;
        }

        $this->info("Checking survey: {$survey->id} (Template: {$survey->feedback_template->name})");
        $this->newLine();

        // Check template questions
        $templateQuestions = Question::whereNull('feedback_id')
            ->where('feedback_template_id', $survey->feedback_template_id)
            ->orderBy('order')
            ->get();

        $this->info("Template questions: " . $templateQuestions->count());

        $headers = ['ID', 'Question', 'Type', 'Order'];
        $rows = [];

        foreach ($templateQuestions as $question) {
            $rows[] = [
                $question->id,
                $question->question,
                $question->question_template->type ?? 'unknown',
                $question->order
            ];
        }

        if (!empty($rows)) {
            $this->table($headers, $rows);
        }

        $this->newLine();

        // Check survey-specific questions
        $surveyQuestions = Question::where('feedback_id', $survey->id)
            ->orderBy('order')
            ->get();

        $this->info("Survey-specific questions: " . $surveyQuestions->count());

        $rows = [];

        foreach ($surveyQuestions as $question) {
            $rows[] = [
                $question->id,
                $question->question,
                $question->question_template->type ?? 'unknown',
                $question->order
            ];
        }

        if (!empty($rows)) {
            $this->table($headers, $rows);
        }

        return 0;
    }
}
