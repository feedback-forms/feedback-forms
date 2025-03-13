<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all existing questions to process their results
        $questions = DB::table('questions')->get();

        // Group questions by feedback_id
        $questionsByFeedback = [];
        foreach ($questions as $question) {
            if (!isset($questionsByFeedback[$question->feedback_id])) {
                $questionsByFeedback[$question->feedback_id] = [];
            }
            $questionsByFeedback[$question->feedback_id][] = $question->id;
        }

        // For each feedback, process all question results as a single submission
        foreach ($questionsByFeedback as $feedbackId => $questionIds) {
            // For existing results, check if any results exist for these questions
            $resultsExist = DB::table('results')
                ->whereIn('question_id', $questionIds)
                ->exists();

            if ($resultsExist) {
                // Generate a single submission ID for all results from this feedback
                $submissionId = Str::uuid()->toString();

                // Update all results for these questions with the submission ID
                DB::table('results')
                    ->whereIn('question_id', $questionIds)
                    ->update([
                        'submission_id' => $submissionId,
                        'value_type' => 'text' // Default type
                    ]);

                // Update existing records based on question template types
                // Get question template types
                $questions = DB::table('questions')
                    ->join('question_templates', 'questions.question_template_id', '=', 'question_templates.id')
                    ->whereIn('questions.id', $questionIds)
                    ->select('questions.id', 'question_templates.type')
                    ->get();

                foreach ($questions as $question) {
                    if ($question->type === 'range') {
                        DB::table('results')
                            ->where('question_id', $question->id)
                            ->update(['value_type' => 'number']);
                    } else if (in_array($question->type, ['checkboxes', 'checkbox'])) {
                        DB::table('results')
                            ->where('question_id', $question->id)
                            ->update(['value_type' => 'checkbox']);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it only populates default values
        // The columns will be dropped by the other migrations if needed
    }
};
