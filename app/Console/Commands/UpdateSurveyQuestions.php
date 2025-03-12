<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feedback;
use App\Models\Question;
use Illuminate\Support\Facades\DB;

class UpdateSurveyQuestions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-survey-questions {survey_id : The ID of the survey to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update survey questions to match the template';

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

        $this->info("Updating survey: {$survey->id} (Template: {$survey->feedback_template->name})");

        // Get template questions
        $templateQuestions = Question::whereNull('feedback_id')
            ->where('feedback_template_id', $survey->feedback_template_id)
            ->orderBy('order')
            ->get();

        if ($templateQuestions->isEmpty()) {
            $this->error("No template questions found for template ID {$survey->feedback_template_id}!");
            return 1;
        }

        $this->info("Found {$templateQuestions->count()} template questions.");

        // Begin transaction
        DB::beginTransaction();

        try {
            // Delete existing survey questions
            $deletedCount = Question::where('feedback_id', $survey->id)->delete();
            $this->info("Deleted {$deletedCount} existing survey questions.");

            // Create new survey questions based on template
            $createdCount = 0;

            foreach ($templateQuestions as $templateQuestion) {
                Question::create([
                    'feedback_template_id' => $survey->feedback_template_id,
                    'feedback_id' => $survey->id,
                    'question_template_id' => $templateQuestion->question_template_id,
                    'question' => $templateQuestion->question,
                    'order' => $templateQuestion->order,
                ]);

                $createdCount++;
            }

            $this->info("Created {$createdCount} new survey questions based on template.");

            // Commit transaction
            DB::commit();

            $this->info("Survey questions updated successfully!");

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            $this->error("Error updating survey questions: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
