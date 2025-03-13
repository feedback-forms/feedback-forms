<?php

namespace App\Console\Commands;

use App\Models\Result;
use App\Models\ResponseValue;
use App\Models\Question;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateSurveyResultsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-survey-results-data {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing survey results from JSON to the new entity relationship model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of survey results data...');

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Running in dry-run mode. No changes will be made.');
        }

        // Get all results that have a value (JSON data)
        $results = Result::whereNotNull('value')->get();

        $this->info("Found {$results->count()} results to migrate.");

        $bar = $this->output->createProgressBar($results->count());
        $bar->start();

        $migratedCount = 0;
        $errorCount = 0;

        foreach ($results as $result) {
            try {
                // Begin a database transaction
                DB::beginTransaction();

                // Get the question to determine the template type
                $question = $result->question;
                $questionTemplateType = $question ? ($question->question_template->type ?? 'text') : 'text';

                // Determine the appropriate column based on the question template type
                $value = $result->value;

                // Check if a ResponseValue already exists for this Result to prevent re-migration
                if (!$dryRun && !ResponseValue::where('result_id', $result->id)->exists()) {
                    // Create a new ResponseValue record
                    $responseValue = new ResponseValue();
                    $responseValue->result_id = $result->id;
                    $responseValue->question_template_type = $questionTemplateType;

                    // Store the value in the appropriate column based on the question template type
                    if (is_array($value)) {
                        // Handle array values (likely from JSON)
                        if ($questionTemplateType === 'range' && isset($value['rating'])) {
                            // For range questions with a rating value
                            $responseValue->range_value = (int) $value['rating'];
                        } elseif (isset($value['feedback'])) {
                            // For feedback responses
                            $responseValue->text_value = $value['feedback'];
                            $responseValue->question_template_type = 'textarea';
                        } else {
                            // For other complex data
                            $responseValue->json_value = $value;
                        }
                    } elseif (is_numeric($value)) {
                        // For numeric values (likely range questions)
                        $responseValue->range_value = (int) $value;
                    } elseif (is_string($value)) {
                        // For text values
                        $responseValue->text_value = $value;
                    } else {
                        // For any other type, store as JSON
                        $responseValue->json_value = $value;
                    }

                    $responseValue->save();
                } else if (ResponseValue::where('result_id', $result->id)->exists()) {
                    $this->line("Skipping result ID {$result->id} as it already has a ResponseValue.");
                }

                // Commit the transaction
                DB::commit();
                $migratedCount++;
            } catch (\Exception $e) {
                // Rollback the transaction in case of error
                DB::rollBack();
                $errorCount++;

                Log::error('Error migrating result data: ' . $e->getMessage(), [
                    'result_id' => $result->id,
                    'exception' => $e
                ]);

                $this->error("Error migrating result ID {$result->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Migration completed:");
        $this->info("- Total results processed: {$results->count()}");
        $this->info("- Successfully migrated: {$migratedCount}");
        $this->info("- Errors: {$errorCount}");

        if ($dryRun) {
            $this->warn('This was a dry run. No changes were made to the database.');
            $this->info('Run the command without --dry-run to perform the actual migration.');
        } else {
            $this->info('You may now run a migration to remove the value column from the results table if desired.');
        }

        return Command::SUCCESS;
    }
}
