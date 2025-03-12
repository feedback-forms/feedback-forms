<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feedback;
use App\Services\SurveyService;

class CheckSurveyStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-survey-statistics {survey_id : The ID of the survey to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check statistics for a specific survey';

    /**
     * Execute the console command.
     */
    public function handle(SurveyService $surveyService)
    {
        $surveyId = $this->argument('survey_id');

        $survey = Feedback::find($surveyId);

        if (!$survey) {
            $this->error("Survey with ID {$surveyId} not found!");
            return 1;
        }

        $this->info("Checking statistics for survey: {$survey->id} (Template: {$survey->feedback_template->name})");
        $this->newLine();

        // Calculate statistics
        $statistics = $surveyService->calculateStatisticsForSurvey($survey);

        if (empty($statistics)) {
            $this->warn("No statistics found for this survey.");
            return 0;
        }

        $this->info("Found " . count($statistics) . " statistics entries.");

        // Display statistics
        foreach ($statistics as $index => $stat) {
            $this->line("Statistic #" . ($index + 1));
            $this->line("  Template Type: " . $stat['template_type']);

            if ($stat['question']) {
                $this->line("  Question: " . $stat['question']->question);
            } else {
                $this->line("  Question: null (template overview)");
            }

            $this->line("  Data:");

            foreach ($stat['data'] as $key => $value) {
                if (is_array($value)) {
                    $this->line("    {$key}: " . json_encode($value));
                } else if (is_object($value)) {
                    $this->line("    {$key}: [Object]");
                } else {
                    $this->line("    {$key}: {$value}");
                }
            }

            $this->newLine();
        }

        return 0;
    }
}
