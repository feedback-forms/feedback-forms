<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurveyResponseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active feedbacks
        $feedbacks = Feedback::where('status', 'running')
            ->orWhere('status', 'expired')
            ->get();

        if ($feedbacks->isEmpty()) {
            $this->command->info('No active feedbacks found. Please create some feedbacks first.');
            return;
        }

        // For each feedback, create responses
        foreach ($feedbacks as $feedback) {
            // Generate between 1 and 5 responses per feedback
            $responseCount = rand(1, 5);

            for ($i = 0; $i < $responseCount; $i++) {
                $submissionId = Str::uuid()->toString();

                // Get all questions for this feedback
                $questions = Question::where('feedback_id', $feedback->id)->get();

                foreach ($questions as $question) {
                    // Determine the type of question
                    $type = $question->question_template->type;

                    // Create appropriate response based on question type
                    switch ($type) {
                        case 'range':
                            $min = $question->question_template->min_value;
                            $max = $question->question_template->max_value;
                            $rating = rand($min, $max);

                            Result::create([
                                'question_id' => $question->id,
                                'submission_id' => $submissionId,
                                'value_type' => 'number',
                                'rating_value' => (string)$rating
                            ]);
                            break;

                        case 'checkboxes':
                            // Randomly select 1-3 options from predefined choices
                            $options = ['Option 1', 'Option 2', 'Option 3', 'Option 4'];
                            $selectedCount = rand(1, 3);
                            $selectedOptions = array_slice($options, 0, $selectedCount);

                            foreach ($selectedOptions as $option) {
                                Result::create([
                                    'question_id' => $question->id,
                                    'submission_id' => $submissionId,
                                    'value_type' => 'checkbox',
                                    'rating_value' => $option
                                ]);
                            }
                            break;

                        case 'textarea':
                            // Create free text responses - these will be excluded from aggregation
                            $texts = [
                                'This is a feedback comment that should not be included in aggregation.',
                                'The teacher was very helpful and engaging.',
                                'I enjoyed this class and learned a lot.',
                                'More practical examples would be helpful.'
                            ];

                            Result::create([
                                'question_id' => $question->id,
                                'submission_id' => $submissionId,
                                'value_type' => 'text',
                                'rating_value' => $texts[array_rand($texts)]
                            ]);
                            break;
                    }
                }

                $this->command->info("Created submission {$submissionId} for feedback {$feedback->id}");
            }
        }

        // Create additional targeted responses to ensure threshold requirements are met
        $this->createTargetedResponses();
    }

    /**
     * Create targeted responses to ensure threshold requirements are met for specific categories
     */
    private function createTargetedResponses(): void
    {
        // Define the categories and the minimum number of responses needed
        $categories = [
            'class' => 3,
            'department' => 2,
            'subject' => 3,
            'school_year' => 2
        ];

        foreach ($categories as $category => $minResponses) {
            // Get distinct values for this category
            $values = Feedback::whereNotNull($category)
                ->distinct($category)
                ->pluck($category)
                ->toArray();

            foreach ($values as $value) {
                // Get feedbacks for this category value
                $feedbacks = Feedback::where($category, $value)
                    ->where(function($query) {
                        $query->where('status', 'running')
                              ->orWhere('status', 'expired');
                    })
                    ->get();

                if ($feedbacks->isEmpty()) {
                    continue;
                }

                // Count existing submissions for these feedbacks
                $feedbackIds = $feedbacks->pluck('id')->toArray();
                $existingSubmissions = DB::table('results')
                    ->join('questions', 'results.question_id', '=', 'questions.id')
                    ->whereIn('questions.feedback_id', $feedbackIds)
                    ->distinct('results.submission_id')
                    ->count('results.submission_id');

                // If we need more submissions to meet the threshold, create them
                $neededSubmissions = max(0, $minResponses - $existingSubmissions);

                if ($neededSubmissions > 0) {
                    $this->command->info("Creating {$neededSubmissions} additional submissions for {$category} = {$value}");

                    // Select a feedback to add responses to
                    $feedback = $feedbacks->first();
                    $questions = Question::where('feedback_id', $feedback->id)->get();

                    for ($i = 0; $i < $neededSubmissions; $i++) {
                        $submissionId = Str::uuid()->toString();

                        foreach ($questions as $question) {
                            // Only handle range and checkbox questions for aggregation
                            $type = $question->question_template->type;

                            if ($type === 'range') {
                                $min = $question->question_template->min_value;
                                $max = $question->question_template->max_value;
                                $rating = rand($min, $max);

                                Result::create([
                                    'question_id' => $question->id,
                                    'submission_id' => $submissionId,
                                    'value_type' => 'number',
                                    'rating_value' => (string)$rating
                                ]);
                            } elseif ($type === 'checkboxes') {
                                $options = ['Option 1', 'Option 2', 'Option 3', 'Option 4'];
                                $selectedCount = rand(1, 3);
                                $selectedOptions = array_slice($options, 0, $selectedCount);

                                foreach ($selectedOptions as $option) {
                                    Result::create([
                                        'question_id' => $question->id,
                                        'submission_id' => $submissionId,
                                        'value_type' => 'checkbox',
                                        'rating_value' => $option
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}