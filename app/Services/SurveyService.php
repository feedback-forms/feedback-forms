<?php

namespace App\Services;

use App\Models\{Feedback, Question, Result, Feedback_template, Question_template};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SurveyService
{
    /**
     * Create a new survey from template
     */
    public function createFromTemplate(array $data, int $userId): Feedback
    {
        return DB::transaction(function () use ($data, $userId) {
            // Create the feedback/survey
            $survey = Feedback::create([
                'user_id' => $userId,
                'feedback_template_id' => $data['template_id'],
                'accesskey' => $this->generateUniqueAccessKey(),
                'limit' => $data['response_limit'] ?? -1,
                'already_answered' => 0,
                'expire_date' => Carbon::parse($data['expire_date']),
                'school_year' => $data['school_year'] ?? null,
                'department' => $data['department'] ?? null,
                'grade_level' => $data['grade_level'] ?? null,
                'class' => $data['class'] ?? null,
                'subject' => $data['subject'] ?? null,
            ]);

            // Get the template and its associated question templates
            $template = Feedback_template::with('questions.question_template')->findOrFail($data['template_id']);

            // If the template has predefined questions, use those
            if ($template->questions->count() > 0) {
                foreach ($template->questions as $templateQuestion) {
                    Question::create([
                        'feedback_template_id' => $data['template_id'],
                        'feedback_id' => $survey->id,
                        'question_template_id' => $templateQuestion->question_template_id,
                        'question' => $templateQuestion->question,
                    ]);
                }
            }
            // Otherwise, create default questions based on the template type
            else {
                // Get all question templates
                $questionTemplates = Question_template::all();

                // Create a default question for each question template
                foreach ($questionTemplates as $questionTemplate) {
                    Question::create([
                        'feedback_template_id' => $data['template_id'],
                        'feedback_id' => $survey->id,
                        'question_template_id' => $questionTemplate->id,
                        'question' => "Default question for {$template->name} using {$questionTemplate->type} format",
                    ]);
                }
            }

            return $survey;
        });
    }

    /**
     * Generate a unique 8-character access key
     */
    private function generateUniqueAccessKey(): string
    {
        do {
            $key = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Feedback::where('accesskey', $key)->exists());

        return $key;
    }

    /**
     * Validate if survey can be answered (not expired, within limits)
     */
    public function canBeAnswered(Feedback $survey): bool
    {
        if ($survey->expire_date < Carbon::now()) {
            return false;
        }

        if ($survey->limit > 0 && $survey->already_answered >= $survey->limit) {
            return false;
        }

        return true;
    }

    /**
     * Store survey responses
     */
    public function storeResponses(Feedback $survey, array $responses): bool
    {
        return DB::transaction(function () use ($survey, $responses) {
            // Get all questions for this survey
            $questions = $survey->questions;

            // Store each response
            foreach ($responses as $questionId => $value) {
                // Verify the question belongs to this survey
                $question = $questions->firstWhere('id', $questionId);

                if (!$question) {
                    continue; // Skip if question doesn't belong to this survey
                }

                // Create or update the result
                Result::updateOrCreate(
                    ['question_id' => $questionId],
                    ['value' => $value]
                );
            }

            // Increment the response count
            $survey->increment('already_answered');

            return true;
        });
    }
}