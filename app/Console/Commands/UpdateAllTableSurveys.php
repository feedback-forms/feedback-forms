<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\Feedback_template;
use Illuminate\Support\Facades\DB;

class UpdateAllTableSurveys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-all-table-surveys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all table surveys to match the template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find the table template
        $tableTemplate = Feedback_template::where('name', 'templates.feedback.table')->first();

        if (!$tableTemplate) {
            $this->error("Table template not found!");
            return 1;
        }

        $this->info("Found table template with ID: {$tableTemplate->id}");

        // Get all surveys using the table template
        $surveys = Feedback::where('feedback_template_id', $tableTemplate->id)->get();

        if ($surveys->isEmpty()) {
            $this->info("No table surveys found.");
            return 0;
        }

        $this->info("Found {$surveys->count()} table surveys to update.");

        // Get template questions
        $templateQuestions = Question::whereNull('feedback_id')
            ->where('feedback_template_id', $tableTemplate->id)
            ->orderBy('order')
            ->get();

        if ($templateQuestions->isEmpty()) {
            $this->error("No template questions found for table template!");
            return 1;
        }

        $this->info("Found {$templateQuestions->count()} template questions.");

        // Update each survey
        $successCount = 0;
        $errorCount = 0;

        foreach ($surveys as $survey) {
            $this->line("Updating survey ID: {$survey->id}");

            // Begin transaction
            DB::beginTransaction();

            try {
                // Delete existing survey questions
                $deletedCount = Question::where('feedback_id', $survey->id)->delete();

                // Create new survey questions based on template
                $createdCount = 0;

                foreach ($templateQuestions as $templateQuestion) {
                    Question::create([
                        'feedback_template_id' => $tableTemplate->id,
                        'feedback_id' => $survey->id,
                        'question_template_id' => $templateQuestion->question_template_id,
                        'question' => $templateQuestion->question,
                        'order' => $templateQuestion->order,
                    ]);

                    $createdCount++;
                }

                // Commit transaction
                DB::commit();

                $this->info("  - Deleted {$deletedCount} questions, created {$createdCount} questions.");
                $successCount++;

            } catch (\Exception $e) {
                // Rollback transaction on error
                DB::rollBack();

                $this->error("  - Error updating survey: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info("Update complete: {$successCount} surveys updated successfully, {$errorCount} errors.");

        return 0;
    }
}
