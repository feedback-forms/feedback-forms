<?php

namespace App\Services;

use App\Models\{Feedback, Question, Result, ResponseValue, Feedback_template, Question_template};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
                        'question_template_id' => $templateQuestion->question_template_id ?? null,
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
        try {
            return DB::transaction(function () use ($survey, $responses) {
                // Generate a unique submission ID to group all responses from this submission
                $submissionId = (string) Str::uuid();

                // Log the submission attempt
                Log::info("Processing survey submission", [
                    'survey_id' => $survey->id,
                    'accesskey' => $survey->accesskey,
                    'submission_id' => $submissionId
                ]);

                // Get all questions for this survey
                $questions = $survey->questions;
                $responseCount = 0;

                // Check if this is a JSON response (from template-specific forms like target)
                if (count($responses) === 1 && isset($responses[0]) && is_string($responses[0])) {
                    try {
                        $jsonData = json_decode($responses[0], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                            // Handle template-specific response format
                            $this->storeTemplateSpecificResponses($survey, $jsonData, $submissionId);
                            return true;
                        }
                    } catch (\Exception $e) {
                        Log::error('Error parsing JSON response: ' . $e->getMessage(), [
                            'survey_id' => $survey->id,
                            'response_data' => $responses[0],
                            'exception' => $e
                        ]);
                        return false;
                    }
                }

                // Handle regular question-by-question responses
                foreach ($responses as $questionId => $value) {
                    // Skip non-numeric keys (like 'feedback' from the form)
                    // Non-numeric keys are typically form metadata or special fields that are not directly tied to questions
                    // 'feedback' is handled separately as a special case for general survey feedback
                    if (!is_numeric($questionId) && $questionId !== 'feedback') {
                        continue;
                    }

                    // Special handling for feedback
                    if ($questionId === 'feedback' && !empty($value)) {
                        $result = Result::create([
                            'question_id' => $questions->first()->id ?? null,
                            'submission_id' => $submissionId,
                        ]);

                        ResponseValue::create([
                            'result_id' => $result->id,
                            'question_template_type' => 'textarea', // Assuming 'textarea' for general feedback
                            'text_value' => $value,
                        ]);

                        $responseCount++;
                        continue;
                    }

                    // Verify the question belongs to this survey
                    $question = $questions->firstWhere('id', $questionId);

                    if (!$question) {
                        // Try to find by index if the questionId is numeric but not an actual ID
                        $index = (int)$questionId;
                        if ($index >= 0 && $index < $questions->count()) {
                            $question = $questions[$index];
                        } else {
                            Log::warning("Question not found for survey", [
                                'survey_id' => $survey->id,
                                'question_id' => $questionId,
                                'index' => $index
                            ]);
                            continue; // Skip if question doesn't belong to this survey
                        }
                    }

                    // Create a new result for each response
                    $result = Result::create([
                        'question_id' => $question->id,
                        'submission_id' => $submissionId,
                    ]);

                    $questionTemplateType = $question->question_template->type ?? 'text'; // Default to 'text'

                    switch ($questionTemplateType) {
                        case 'range':
                            ResponseValue::create([
                                'result_id' => $result->id,
                                'question_template_type' => 'range',
                                'range_value' => intval($value), // Assuming range value is an integer
                            ]);
                            break;
                        case 'checkboxes':
                            ResponseValue::create([
                                'result_id' => $result->id,
                                'question_template_type' => 'checkboxes',
                                'json_value' => is_array($value) ? $value : [$value], // Store checkboxes as JSON array
                            ]);
                            break;
                        case 'textarea':
                            ResponseValue::create([
                                'result_id' => $result->id,
                                'question_template_type' => 'textarea',
                                'text_value' => $value,
                            ]);
                            break;
                        default: // Default case, e.g., 'text' or unknown types
                            ResponseValue::create([
                                'result_id' => $result->id,
                                'question_template_type' => $questionTemplateType,
                                'text_value' => $value, // Store as text by default
                            ]);
                            break;
                    }

                    $responseCount++;
                }

                // Only increment if we actually stored responses
                if ($responseCount > 0) {
                    // Increment the response count
                    $survey->increment('already_answered');
                    Log::info("Survey response stored successfully", [
                        'survey_id' => $survey->id,
                        'submission_id' => $submissionId,
                        'response_count' => $responseCount
                    ]);
                } else {
                    Log::warning("No responses were stored for survey", [
                        'survey_id' => $survey->id
                    ]);
                    return false;
                }

                return true;
            });
        } catch (\Exception $e) {
            Log::error('Error storing survey responses: ' . $e->getMessage(), [
                'survey_id' => $survey->id,
                'exception' => $e,
                'exception_class' => get_class($e),
                'exception_trace' => $e->getTraceAsString(),
                'responses' => $responses
            ]);
            return false;
        }
    }

    /**
     * Store responses from template-specific forms (like target diagram)
     */
    private function storeTemplateSpecificResponses(Feedback $survey, array $jsonData, string $submissionId): void
    {
        // Get the template type
        $templateName = $survey->feedback_template->name ?? '';
        $templateType = '';
        if (preg_match('/templates\.feedback\.(\w+)$/', $templateName, $matches)) {
            $templateType = $matches[1];
        }

        // Log the template type for debugging
        Log::info("Processing template-specific response", [
            'survey_id' => $survey->id,
            'template_type' => $templateType,
            'submission_id' => $submissionId
        ]);

        // Get the first question of the survey to associate the result with
        // Template-specific responses are often survey-level data rather than question-specific
        // We associate them with the first question as a convention, but this could be enhanced
        // in the future to use a more specific question association strategy if needed
        $firstQuestion = $survey->questions->first();

        // Create a single result record for the entire template response
        $result = Result::create([
            'question_id' => $firstQuestion->id ?? null,
            'submission_id' => $submissionId,
        ]);

        // Store the entire JSON data in the json_value column of response_values
        ResponseValue::create([
            'result_id' => $result->id,
            'question_template_type' => $templateType, // Store template type for context
            'json_value' => $jsonData, // Store the entire JSON response
        ]);

        // Increment the response count
        $survey->increment('already_answered');
    }
}