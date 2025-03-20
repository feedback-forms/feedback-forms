<?php

namespace App\Services;

use App\Models\{Feedback, Question, Result, Feedback_template, Question_template};
use App\Exceptions\ServiceException;
use App\Exceptions\SurveyNotAvailableException;
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
        try {
            return DB::transaction(function () use ($data, $userId) {
                try {
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
                } catch (\Exception $e) {
                    throw ServiceException::database(
                        'Failed to create survey from template',
                        ['user_id' => $userId, 'template_id' => $data['template_id'] ?? null],
                        $e
                    );
                }

                try {
                    // Get the template
                    $template = Feedback_template::findOrFail($data['template_id']);
                    $templateName = $template->name ?? '';

                    // Get the appropriate template strategy for this template
                    $templateStrategy = $this->templateStrategyFactory->getStrategy($templateName);

                    // Use the strategy to create questions
                    $templateStrategy->createQuestions($survey, $data);
                } catch (\Exception $e) {
                    throw ServiceException::businessLogic(
                        'Failed to initialize template strategy or create template questions',
                        [
                            'survey_id' => $survey->id ?? null,
                            'template_id' => $data['template_id'] ?? null,
                            'template_name' => $templateName ?? 'unknown'
                        ],
                        $e
                    );
                }

                // If the template has predefined questions and the strategy didn't create any,
                // create them from the template (this handles the case where template questions
                // are defined but we don't have a specialized strategy for this template type)
                if ($survey->questions()->count() === 0) {
                    try {
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
                    } catch (\Exception $e) {
                        throw ServiceException::database(
                            'Failed to create default questions from template',
                            [
                                'survey_id' => $survey->id,
                                'template_id' => $data['template_id'],
                                'questions_count' => $template->questions->count() ?? 0
                            ],
                            $e
                        );
                    }
                }

                return $survey;
            });
        } catch (ServiceException $e) {
            // Re-throw ServiceExceptions as they're already properly formatted
            throw $e;
        } catch (\Exception $e) {
            // Wrap any other exceptions in our ServiceException
            throw ServiceException::fromException(
                $e,
                ServiceException::CATEGORY_UNEXPECTED,
                ['user_id' => $userId, 'template_id' => $data['template_id'] ?? null]
            );
        }
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
     * @return bool True if the survey can be answered
     * @throws SurveyNotAvailableException If the survey cannot be answered due to expiration or limits
     * @throws ServiceException If there's an unexpected error during validation
     */
    public function canBeAnswered(Feedback $survey): bool
    {
        try {
            if ($survey->expire_date < Carbon::now()) {
                throw new SurveyNotAvailableException(
                    __('surveys.survey_expired')
                );
            }

            if ($survey->limit > 0 && $survey->submission_count >= $survey->limit) {
                throw new SurveyNotAvailableException(
                    __('surveys.survey_limit_reached')
                );
            }

            return true;
        } catch (SurveyNotAvailableException $e) {
            // Log the exception with additional context
            Log::warning($e->getMessage(), [
                'survey_id' => $survey->id,
                'expire_date' => $survey->expire_date,
                'limit' => $survey->limit,
                'submission_count' => $survey->submission_count
            ]);

            // Re-throw the exception
            throw $e;
        } catch (\Exception $e) {
            // Wrap any unexpected exceptions
            throw ServiceException::fromException(
                $e,
                ServiceException::CATEGORY_UNEXPECTED,
                ['survey_id' => $survey->id]
            );
        }
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

        foreach ($responses as $questionId => $value) {
            // Skip non-numeric keys (like 'feedback' from the form) except 'feedback' special case
            if (!is_numeric($questionId) && $questionId !== 'feedback') {
                continue;
            }

            // Special handling for feedback
            if ($questionId === 'feedback' && !empty($value)) {
                Result::create([
                    'question_id' => $questions->first()->id ?? null,
                    'submission_id' => $submissionId,
                    'value_type' => 'text',
                    'rating_value' => $value,
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
                'response_type' => is_array($value) ? 'array' : gettype($value)
            ]);

            // Validate and store the response based on question type
            $processedCount = $this->processQuestionResponse(
                $survey,
                $question,
                $questionTemplateType,
                $valueType,
                $value,
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
        $value,
        string $submissionId
    ): int {
        // Validate the value
        if (!$this->validateResponseValue($survey, $question, $valueType, $value)) {
            return 0;
        }

        // Handle different question types
        switch ($questionTemplateType) {
            case 'range':
                $this->storeRangeResponse($question, $valueType, $value, $submissionId);
                return 1;

            case 'checkboxes':
            case 'checkbox':
                return $this->storeCheckboxResponse($survey, $question, $valueType, $value, $submissionId);

            default: // Default case, e.g., 'text' or unknown types
                $this->storeTextResponse($question, $valueType, $value, $submissionId);
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
    private function validateResponseValue(Feedback $survey, $question, string $valueType, $value): bool
    {
        if ($valueType === 'number' && !is_numeric($value)) {
            throw ServiceException::validation(
                "Invalid rating_value for number type",
                [
                    'survey_id' => $survey->id,
                    'question_id' => $question->id,
                    'value_type' => $valueType,
                    'provided_value' => $value,
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
    private function storeRangeResponse($question, string $valueType, $value, string $submissionId): void
    {
        Result::create([
            'question_id' => $question->id,
            'submission_id' => $submissionId,
            'value_type' => $valueType,
            'rating_value' => $value,
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
    private function storeTextResponse($question, string $valueType, $value, string $submissionId): void
    {
        Result::create([
            'question_id' => $question->id,
            'submission_id' => $submissionId,
            'value_type' => $valueType,
            'rating_value' => $value,
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
    private function storeCheckboxResponse(Feedback $survey, $question, string $valueType, $value, string $submissionId): int
    {
        // If the value is an array of checkbox options
        if (is_array($value)) {
            // Validate the array values
            $validValues = array_filter($value, function($optionValue) {
                return is_string($optionValue) || is_numeric($optionValue);
            });

            if (empty($validValues)) {
                throw ServiceException::validation(
                    "No valid values found in checkbox response",
                    [
                        'survey_id' => $survey->id,
                        'question_id' => $question->id,
                        'values' => $value
                    ]
                );
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

                return count($validValues);
            } catch (\Exception $e) {
                throw ServiceException::database(
                    "Failed to store checkbox options",
                    [
                        'survey_id' => $survey->id,
                        'question_id' => $question->id,
                        'values' => $validValues
                    ],
                    $e
                );
            }
        } else if (is_string($value) || is_numeric($value)) {
            // Handle case where a single value is submitted instead of an array
            Log::info("Converting single checkbox value to array", [
                'survey_id' => $survey->id,
                'question_id' => $question->id,
                'value' => $value
            ]);

            // Create a single result for a single checkbox value
            Result::create([
                'question_id' => $question->id,
                'submission_id' => $submissionId,
                'value_type' => $valueType,
                'rating_value' => (string)$value,
            ]);

            return 1;
        } else {
            Log::warning("Invalid checkbox value type", [
                'survey_id' => $survey->id,
                'question_id' => $question->id,
                'value_type' => gettype($value)
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
