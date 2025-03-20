<?php

namespace App\Services;

use App\Models\{Feedback, Question, Result};
use App\Exceptions\ServiceException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SurveyResponseService
{
    /**
     * @var Templates\TemplateStrategyFactory
     */
    protected $templateStrategyFactory;

    /**
     * Constructor to initialize dependencies
     *
     * @param Templates\TemplateStrategyFactory $templateStrategyFactory
     */
    public function __construct(Templates\TemplateStrategyFactory $templateStrategyFactory)
    {
        $this->templateStrategyFactory = $templateStrategyFactory;
    }

    /**
     * Store survey responses
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $responses The responses to store
     * @return bool True if responses were stored successfully
     * @throws ServiceException If there's an error during response storage
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

                // Try to process template-specific JSON data first
                if ($this->processTemplateSpecificData($survey, $responses, $submissionId)) {
                    return true;
                }

                // Process regular question-by-question responses
                $responseCount = $this->processRegularResponses($survey, $responses, $questions, $submissionId);

                // Only update if we actually stored responses
                if ($responseCount > 0) {
                    $this->updateSurveyStatus($survey);

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
            throw ServiceException::fromException(
                $e,
                ServiceException::CATEGORY_DATABASE,
                [
                    'survey_id' => $survey->id,
                    'responses' => $responses
                ]
            );
        }
    }

    /**
     * Process template-specific JSON data
     *
     * @param Feedback $survey The survey
     * @param array $responses The responses
     * @param string $submissionId The submission ID
     * @return bool True if processing was successful, false otherwise
     */
    private function processTemplateSpecificData(Feedback $survey, array $responses, string $submissionId): bool
    {
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
                throw ServiceException::businessLogic(
                    'Error processing structured JSON data',
                    [
                        'survey_id' => $survey->id,
                        'template_name' => $templateName,
                        'response_data' => $responses['json_data']
                    ],
                    $e
                );
            }
        }

        // Legacy check for JSON string (can be removed after frontend updates)
        if (count($responses) === 1 && isset($responses[0]) && is_string($responses[0])) {
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
                throw ServiceException::businessLogic(
                    'Error parsing legacy JSON response',
                    [
                        'survey_id' => $survey->id,
                        'template_name' => $templateName ?? 'unknown',
                        'json_error' => json_last_error_msg(),
                        'response_data' => $responses[0]
                    ],
                    $e
                );
            }
        }

        return false;
    }

    /**
     * Process regular question-by-question responses
     *
     * @param Feedback $survey The survey
     * @param array $responses The responses
     * @param \Illuminate\Database\Eloquent\Collection $questions The survey questions
     * @param string $submissionId The submission ID
     * @return int The number of responses processed
     */
    private function processRegularResponses(Feedback $survey, array $responses, $questions, string $submissionId): int
    {
        $responseCount = 0;

        foreach ($responses as $questionId => $responseValue) {
            // Skip non-numeric keys (like 'feedback' from the form) except 'feedback' special case
            if (!is_numeric($questionId) && $questionId !== 'feedback') {
                continue;
            }

            // Special handling for feedback
            if ($questionId === 'feedback' && !empty($responseValue)) {
                Result::create([
                    'question_id' => $questions->first()->id ?? null,
                    'submission_id' => $submissionId,
                    'value_type' => 'text',
                    'rating_value' => $responseValue,
                ]);

                $responseCount++;
                continue;
            }

            // Find the question in the survey
            $question = $this->findQuestionInSurvey($survey, $questions, $questionId);
            if (!$question) {
                continue; // Skip if question not found
            }

            // Determine the value type based on question template
            $questionTemplateType = $question->question_template->type ?? 'text';
            $valueType = $this->determineValueType($questionTemplateType);

            // Log processing info
            Log::info("Processing response for question", [
                'survey_id' => $survey->id,
                'question_id' => $question->id,
                'template_type' => $questionTemplateType,
                'value_type' => $valueType,
                'response_type' => is_array($responseValue) ? 'array' : gettype($responseValue)
            ]);

            // Validate and store the response based on question type
            $processedCount = $this->processQuestionResponse(
                $survey,
                $question,
                $questionTemplateType,
                $valueType,
                $responseValue,
                $submissionId
            );

            $responseCount += $processedCount;
        }

        return $responseCount;
    }

    /**
     * Find a question in the survey
     *
     * @param Feedback $survey The survey
     * @param \Illuminate\Database\Eloquent\Collection $questions The survey questions
     * @param string|int $questionId The question ID or index
     * @return Question|null The question if found, null otherwise
     */
    private function findQuestionInSurvey(Feedback $survey, $questions, $questionId)
    {
        // Try to find by ID first
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
                return null;
            }
        }

        return $question;
    }

    /**
     * Determine the value type based on question template type
     *
     * @param string $questionTemplateType The question template type
     * @return string The value type
     */
    private function determineValueType(string $questionTemplateType): string
    {
        if ($questionTemplateType === 'range') {
            return 'number';
        } else if (in_array($questionTemplateType, ['checkboxes', 'checkbox'])) {
            return 'checkbox';
        }

        return 'text'; // Default
    }

    /**
     * Process a response for a specific question
     *
     * @param Feedback $survey The survey
     * @param Question $question The question
     * @param string $questionTemplateType The question template type
     * @param string $valueType The value type
     * @param mixed $value The response value
     * @param string $submissionId The submission ID
     * @return int The number of responses processed
     */
    private function processQuestionResponse(
        Feedback $survey,
        $question,
        string $questionTemplateType,
        string $valueType,
        $responseValue,
        string $submissionId
    ): int {
        // Validate the value
        if (!$this->validateResponseValue($survey, $question, $valueType, $responseValue)) {
            return 0;
        }

        // Handle different question types
        switch ($questionTemplateType) {
            case 'range':
                $this->storeRangeResponse($question, $valueType, $responseValue, $submissionId);
                return 1;

            case 'checkboxes':
            case 'checkbox':
                return $this->storeCheckboxResponse($survey, $question, $valueType, $responseValue, $submissionId);

            default: // Default case, e.g., 'text' or unknown types
                $this->storeTextResponse($question, $valueType, $responseValue, $submissionId);
                return 1;
        }
    }

    /**
     * Validate a response value
     *
     * @param Feedback $survey The survey
     * @param Question $question The question
     * @param string $valueType The value type
     * @param mixed $value The response value
     * @return bool True if valid, false otherwise
     */
    private function validateResponseValue(Feedback $survey, $question, string $valueType, $responseValue): bool
    {
        if ($valueType === 'number' && !is_numeric($responseValue)) {
            throw ServiceException::validation(
                "Invalid rating_value for number type",
                [
                    'survey_id' => $survey->id,
                    'question_id' => $question->id,
                    'value_type' => $valueType,
                    'provided_value' => $responseValue,
                ]
            );
        }

        return true;
    }

    /**
     * Store a range type response
     *
     * @param Question $question The question
     * @param string $valueType The value type
     * @param mixed $value The response value
     * @param string $submissionId The submission ID
     */
    private function storeRangeResponse($question, string $valueType, $rangeValue, string $submissionId): void
    {
        Result::create([
            'question_id' => $question->id,
            'submission_id' => $submissionId,
            'value_type' => $valueType,
            'rating_value' => $rangeValue,
        ]);
    }

    /**
     * Store a text type response
     *
     * @param Question $question The question
     * @param string $valueType The value type
     * @param mixed $value The response value
     * @param string $submissionId The submission ID
     */
    private function storeTextResponse($question, string $valueType, $textValue, string $submissionId): void
    {
        Result::create([
            'question_id' => $question->id,
            'submission_id' => $submissionId,
            'value_type' => $valueType,
            'rating_value' => $textValue,
        ]);
    }

    /**
     * Store a checkbox type response
     *
     * @param Feedback $survey The survey
     * @param Question $question The question
     * @param string $valueType The value type
     * @param mixed $value The response value
     * @param string $submissionId The submission ID
     * @return int The number of checkbox options stored
     */
    private function storeCheckboxResponse(Feedback $survey, $question, string $valueType, $checkboxValues, string $submissionId): int
    {
        // If the value is an array of checkbox options
        if (is_array($checkboxValues)) {
            // Validate the array values
            $validCheckboxOptions = array_filter($checkboxValues, function($selectedOption) {
                return is_string($selectedOption) || is_numeric($selectedOption);
            });

            if (empty($validCheckboxOptions)) {
                throw ServiceException::validation(
                    "No valid values found in checkbox response",
                    [
                        'survey_id' => $survey->id,
                        'question_id' => $question->id,
                        'values' => $checkboxValues
                    ]
                );
            }

            // For each selected checkbox option, create a separate result
            try {
                foreach ($validCheckboxOptions as $selectedOption) {
                    Result::create([
                        'question_id' => $question->id,
                        'submission_id' => $submissionId,
                        'value_type' => $valueType,
                        'rating_value' => (string)$selectedOption, // Ensure it's a string
                    ]);

                    Log::info("Stored checkbox option", [
                        'survey_id' => $survey->id,
                        'question_id' => $question->id,
                        'option' => $selectedOption
                    ]);
                }

                return count($validCheckboxOptions);
            } catch (\Exception $e) {
                throw ServiceException::database(
                    "Failed to store checkbox options",
                    [
                        'survey_id' => $survey->id,
                        'question_id' => $question->id,
                        'values' => $validCheckboxOptions
                    ],
                    $e
                );
            }
        } else if (is_string($checkboxValues) || is_numeric($checkboxValues)) {
            // Handle case where a single value is submitted instead of an array
            Log::info("Converting single checkbox value to array", [
                'survey_id' => $survey->id,
                'question_id' => $question->id,
                'value' => $checkboxValues
            ]);

            // Create a single result for a single checkbox value
            Result::create([
                'question_id' => $question->id,
                'submission_id' => $submissionId,
                'value_type' => $valueType,
                'rating_value' => (string)$checkboxValues,
            ]);

            return 1;
        } else {
            Log::warning("Invalid checkbox value type", [
                'survey_id' => $survey->id,
                'question_id' => $question->id,
                'value_type' => gettype($checkboxValues)
            ]);
            return 0;
        }
    }

    /**
     * Update the survey status if necessary
     *
     * @param Feedback $survey The survey
     */
    private function updateSurveyStatus(Feedback $survey): void
    {
        // Set the status to update if it's a draft or running
        if (in_array($survey->status, ['draft', 'running'])) {
            $survey->update(['status' => 'running']);
        }
    }
}