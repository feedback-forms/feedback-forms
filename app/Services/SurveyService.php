<?php

namespace App\Services;

use App\Models\{Feedback, Question, Result, Feedback_template, Question_template};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SurveyService
{
    /**
     * @var Templates\TemplateStrategyFactory
     */
    protected $templateStrategyFactory;
    /**
     * Create a new survey from template
     */
    public function createFromTemplate(array $data, int $userId): Feedback
    {
        return DB::transaction(function () use ($data, $userId) {
            // Create the feedback/survey
            $survey = Feedback::create([
                'name' => $data['name'] ?? null,
                'user_id' => $userId,
                'feedback_template_id' => $data['template_id'],
                'accesskey' => $this->generateUniqueAccessKey(),
                'limit' => $data['response_limit'] ?? -1,
                'expire_date' => Carbon::parse($data['expire_date']),
                'school_year_id' => $data['school_year_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'grade_level_id' => $data['grade_level_id'] ?? null,
                'school_class_id' => $data['school_class_id'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
            ]);

            // Get the template
            $template = Feedback_template::findOrFail($data['template_id']);
            $templateName = $template->name ?? '';

            // Get the appropriate template strategy for this template
            $templateStrategy = $this->templateStrategyFactory->getStrategy($templateName);

            // Use the strategy to create questions
            $templateStrategy->createQuestions($survey, $data);

            // If the template has predefined questions and the strategy didn't create any,
            // create them from the template (this handles the case where template questions
            // are defined but we don't have a specialized strategy for this template type)
            if ($survey->questions()->count() === 0) {
                // Reload template with questions to ensure we have the latest data
                $template = Feedback_template::with('questions.question_template')->findOrFail($data['template_id']);

                if ($template->questions->count() > 0) {
                    foreach ($template->questions as $index => $templateQuestion) {
                        Question::create([
                            'feedback_template_id' => $data['template_id'],
                            'feedback_id' => $survey->id,
                            'question_template_id' => $templateQuestion->question_template_id ?? null,
                            'question' => $templateQuestion->question,
                            'order' => $templateQuestion->order ?? ($index + 1),
                        ]);
                    }
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
        $formattedKey = substr($key, 0, 4) . '-' . substr($key, 4, 4);

    } while (Feedback::where('accesskey', $formattedKey)->exists());

    return $formattedKey;
}

    /**
     * Validate if survey can be answered (not expired, within limits)
     *
     * @param Feedback $survey The survey to check
     * @return bool True if the survey can be answered, false otherwise
     */
    public function canBeAnswered(Feedback $survey): bool
    {
        if ($survey->expire_date < Carbon::now()) {
            return false;
        }

        if ($survey->limit > 0 && $survey->submission_count >= $survey->limit) {
            return false;
        }

        return true;
    }

    /**
     * Store survey responses
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $responses The responses to store
     * @return bool True if responses were stored successfully, false otherwise
     * @throws \Exception If there's an error during response storage
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

                // Check if this is a JSON data structure (from template-specific forms like target)
                if (isset($responses['json_data']) && is_array($responses['json_data'])) {
                    try {
                        // Get the appropriate template strategy for this template
                        $templateName = $survey->feedback_template->name ?? '';
                        $templateStrategy = $this->templateStrategyFactory->getStrategy($templateName);

                        // Use the strategy to store responses
                        $templateStrategy->storeResponses($survey, $responses['json_data'], $submissionId);
                        return true;
                    } catch (\Exception $e) {
                        Log::error('Error processing structured JSON data: ' . $e->getMessage(), [
                            'survey_id' => $survey->id,
                            'response_data' => $responses['json_data'],
                            'exception' => $e
                        ]);
                        return false;
                    }
                // Legacy check for JSON string (can be removed after frontend updates)
                } else if (count($responses) === 1 && isset($responses[0]) && is_string($responses[0])) {
                    try {
                        $jsonData = json_decode($responses[0], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                            // Get the appropriate template strategy for this template
                            $templateName = $survey->feedback_template->name ?? '';
                            $templateStrategy = $this->templateStrategyFactory->getStrategy($templateName);

                            // Use the strategy to store responses
                            $templateStrategy->storeResponses($survey, $jsonData, $submissionId);
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
                            'value_type' => 'text',
                            'rating_value' => $value,
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

                    // Get the question template type
                    $questionTemplateType = $question->question_template->type ?? 'text'; // Default to 'text'

                    // Set the appropriate value_type based on question template type
                    $valueType = 'text'; // Default
                    if ($questionTemplateType === 'range') {
                        $valueType = 'number';
                    } else if (in_array($questionTemplateType, ['checkboxes', 'checkbox'])) {
                        $valueType = 'checkbox';
                    }

                    // Log info about the response being processed
                    Log::info("Processing response for question", [
                        'survey_id' => $survey->id,
                        'question_id' => $question->id,
                        'template_type' => $questionTemplateType,
                        'value_type' => $valueType,
                        'response_type' => is_array($value) ? 'array' : gettype($value)
                    ]);

                    // Data validation based on value_type
                    $isValidValue = true;
                    switch ($valueType) {
                        case 'number':
                            if (!is_numeric($value)) {
                                $isValidValue = false;
                                Log::warning("Invalid rating_value for number type", [
                                    'survey_id' => $survey->id,
                                    'question_id' => $question->id,
                                    'value_type' => $valueType,
                                    'provided_value' => $value,
                                ]);
                            }
                            break;
                        // For 'text' and 'checkbox', any string value is considered valid for this basic example.
                        default:
                            break;
                    }

                    if (!$isValidValue) {
                        Log::warning("Skipping invalid response value", [
                            'survey_id' => $survey->id,
                            'question_id' => $question->id,
                            'value' => $value,
                        ]);
                        continue;
                    }

                    // Handle different question types
                    switch ($questionTemplateType) {
                        case 'range':
                            // Create a new result record with a scalar rating value
                            $result = Result::create([
                                'question_id' => $question->id,
                                'submission_id' => $submissionId,
                                'value_type' => $valueType,
                                'rating_value' => $value,
                            ]);
                            break;

                        case 'checkboxes':
                        case 'checkbox':
                            // If the value is an array of checkbox options
                            if (is_array($value)) {
                                // Validate the array values
                                $validValues = array_filter($value, function($optionValue) {
                                    return is_string($optionValue) || is_numeric($optionValue);
                                });

                                if (empty($validValues)) {
                                    Log::warning("No valid values found in checkbox response", [
                                        'survey_id' => $survey->id,
                                        'question_id' => $question->id,
                                        'values' => $value
                                    ]);
                                    continue 2; // Skip to next response
                                }

                                // For each selected checkbox option, create a separate result
                                try {
                                    foreach ($validValues as $optionValue) {
                                        Result::create([
                                            'question_id' => $question->id,
                                            'submission_id' => $submissionId,
                                            'value_type' => $valueType,
                                            'rating_value' => (string)$optionValue, // Ensure it's a string
                                        ]);

                                        Log::info("Stored checkbox option", [
                                            'survey_id' => $survey->id,
                                            'question_id' => $question->id,
                                            'option' => $optionValue
                                        ]);
                                    }

                                    $responseCount += count($validValues);
                                } catch (\Exception $e) {
                                    Log::error("Failed to store checkbox options", [
                                        'survey_id' => $survey->id,
                                        'question_id' => $question->id,
                                        'values' => $validValues,
                                        'error' => $e->getMessage()
                                    ]);
                                }

                                // Since we've already created the results above, continue to next response
                                continue 2; // Skip the rest of the switch and outer loop iteration
                            } else if (is_string($value) || is_numeric($value)) {
                                // Handle case where a single value is submitted instead of an array
                                Log::info("Converting single checkbox value to array", [
                                    'survey_id' => $survey->id,
                                    'question_id' => $question->id,
                                    'value' => $value
                                ]);
                                // Continue normal processing with the single value
                            } else {
                                Log::warning("Invalid checkbox value type", [
                                    'survey_id' => $survey->id,
                                    'question_id' => $question->id,
                                    'value_type' => gettype($value)
                                ]);
                                continue 2; // Skip to next response
                            }

                            // Create a single result for a single checkbox value
                            $result = Result::create([
                                'question_id' => $question->id,
                                'submission_id' => $submissionId,
                                'value_type' => $valueType,
                                'rating_value' => (string)$value,
                            ]);
                            break;

                        default: // Default case, e.g., 'text' or unknown types
                            // Create a new result with default text value_type
                            $result = Result::create([
                                'question_id' => $question->id,
                                'submission_id' => $submissionId,
                                'value_type' => $valueType,
                                'rating_value' => $value,
                            ]);
                            break;
                    }

                    $responseCount++;
                }

                // Only increment if we actually stored responses
                if ($responseCount > 0) {
                    // Set the status to update if it's a draft or running
                    if (in_array($survey->status, ['draft', 'running'])) {
                        $survey->update(['status' => 'running']);
                    }

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
     * @var StatisticsService
     */
    protected $statisticsService;

    /**
     * Constructor to initialize dependencies
     *
     * @param StatisticsService $statisticsService
     * @param Templates\TemplateStrategyFactory $templateStrategyFactory
     */
    public function __construct(
        StatisticsService $statisticsService,
        Templates\TemplateStrategyFactory $templateStrategyFactory
    ) {
        $this->statisticsService = $statisticsService;
        $this->templateStrategyFactory = $templateStrategyFactory;
    }

    /**
     * Calculate statistics for a survey
     *
     * Delegates statistics calculation to the StatisticsService.
     *
     * @param Feedback $survey The survey to calculate statistics for
     * @return array An array of statistics data for each question
     */
    public function calculateStatisticsForSurvey(Feedback $survey): array
    {
        return $this->statisticsService->calculateStatisticsForSurvey($survey);
    }
}
