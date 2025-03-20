<?php

namespace App\Services;

use App\Models\{Feedback, Question, Result};
use App\Repositories\FeedbackRepository;
use App\Exceptions\ServiceException;
use App\Services\ErrorLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SurveyResponseService
{
    /**
     * @var Templates\TemplateStrategyFactory
     */
    protected $templateStrategyFactory;

    /**
     * @var FeedbackRepository
     */
    protected $feedbackRepository;

    /**
     * Constructor to initialize dependencies
     *
     * @param Templates\TemplateStrategyFactory $templateStrategyFactory Factory for creating template-specific strategies
     * @param FeedbackRepository $feedbackRepository Repository for feedback data access
     */
    public function __construct(
        Templates\TemplateStrategyFactory $templateStrategyFactory,
        FeedbackRepository $feedbackRepository
    ) {
        $this->templateStrategyFactory = $templateStrategyFactory;
        $this->feedbackRepository = $feedbackRepository;
    }

    /**
     * Store survey responses for a given survey
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $responses The responses data to store
     * @return bool True if responses were stored successfully
     * @throws ServiceException If there's an error during response storage
     */
    public function storeResponses(Feedback $survey, array $responses): bool
    {
        try {
            return DB::transaction(function () use ($survey, $responses) {
                $submissionId = (string) Str::uuid();

                ErrorLogger::logError(
                    "Processing survey submission",
                    ErrorLogger::CATEGORY_BUSINESS_LOGIC,
                    ErrorLogger::LOG_LEVEL_INFO,
                    [
                        'survey_id' => $survey->id,
                        'accesskey' => $survey->accesskey,
                        'submission_id' => $submissionId
                    ]
                );

                $questions = $survey->questions;
                $responseCount = 0;

                if ($this->processTemplateSpecificData($survey, $responses, $submissionId)) {
                    return true;
                }

                $responseCount = $this->processRegularResponses($survey, $responses, $questions, $submissionId);

                if ($responseCount > 0) {
                    $this->updateSurveyStatus($survey);

                    ErrorLogger::logError(
                        "Survey response stored successfully",
                        ErrorLogger::CATEGORY_BUSINESS_LOGIC,
                        ErrorLogger::LOG_LEVEL_INFO,
                        [
                            'survey_id' => $survey->id,
                            'submission_id' => $submissionId,
                            'response_count' => $responseCount
                        ]
                    );
                } else {
                    ErrorLogger::logError(
                        "No responses were stored for survey",
                        ErrorLogger::CATEGORY_BUSINESS_LOGIC,
                        ErrorLogger::LOG_LEVEL_WARNING,
                        [
                            'survey_id' => $survey->id
                        ]
                    );
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
     * Process template-specific response data
     *
     * This method handles specialized response formats like JSON data
     * that need to be processed using template-specific strategies.
     *
     * @param Feedback $survey The survey being processed
     * @param array $responses The response data
     * @param string $submissionId Unique ID for this submission
     * @return bool True if template-specific processing was performed
     * @throws ServiceException If there's an error during template processing
     */
    private function processTemplateSpecificData(Feedback $survey, array $responses, string $submissionId): bool
    {
        if (isset($responses['json_data']) && is_array($responses['json_data'])) {
            try {
                $templateName = $survey->feedback_template->name ?? '';
                $templateStrategy = $this->templateStrategyFactory->getStrategy($templateName);
                $templateStrategy->storeResponses($survey, $responses['json_data'], $submissionId);
                return true;
            } catch (\Exception $e) {
                throw ServiceException::businessLogic(
                    'Error processing structured JSON data',
                    [
                        'survey_id' => $survey->id,
                        'template_name' => $templateName ?? 'unknown',
                        'response_data' => $responses['json_data']
                    ],
                    $e
                );
            }
        }

        if (count($responses) === 1 && isset($responses[0]) && is_string($responses[0])) {
            try {
                $jsonData = json_decode($responses[0], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                    $templateName = $survey->feedback_template->name ?? '';
                    $templateStrategy = $this->templateStrategyFactory->getStrategy($templateName);
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
     * Process regular (non-template-specific) responses
     *
     * @param Feedback $survey The survey being processed
     * @param array $responses The response data
     * @param \Illuminate\Database\Eloquent\Collection $questions The survey questions
     * @param string $submissionId Unique ID for this submission
     * @return int Number of responses successfully processed
     * @throws ServiceException If there's an error during response processing
     */
    private function processRegularResponses(Feedback $survey, array $responses, $questions, string $submissionId): int
    {
        $responseCount = 0;

        foreach ($responses as $questionId => $responseValue) {
            if (!is_numeric($questionId) && $questionId !== 'feedback') {
                continue;
            }

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

            $question = $this->findQuestionInSurvey($survey, $questions, $questionId);
            if (!$question) {
                continue;
            }

            $questionTemplateType = $question->question_template->type ?? 'text';
            $valueType = $this->determineValueType($questionTemplateType);

            ErrorLogger::logError(
                "Processing response for question",
                ErrorLogger::CATEGORY_BUSINESS_LOGIC,
                ErrorLogger::LOG_LEVEL_INFO,
                [
                    'survey_id' => $survey->id,
                    'question_id' => $question->id,
                    'template_type' => $questionTemplateType,
                    'value_type' => $valueType,
                    'response_type' => is_array($responseValue) ? 'array' : gettype($responseValue)
                ]
            );

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
     * Find a question within a survey by ID or index
     *
     * This method tries to find a question by its ID, and if not found,
     * will attempt to find it by index position in the questions collection.
     *
     * @param Feedback $survey The survey containing the questions
     * @param \Illuminate\Database\Eloquent\Collection $questions Collection of survey questions
     * @param int|string $questionId Question ID or index to find
     * @return \App\Models\Question|null The found question or null if not found
     */
    private function findQuestionInSurvey(Feedback $survey, $questions, $questionId)
    {
        $question = $questions->firstWhere('id', $questionId);

        if (!$question) {
            $index = (int)$questionId;
            if ($index >= 0 && $index < $questions->count()) {
                $question = $questions[$index];
            } else {
                ErrorLogger::logError(
                    "Question not found for survey",
                    ErrorLogger::CATEGORY_BUSINESS_LOGIC,
                    ErrorLogger::LOG_LEVEL_WARNING,
                    [
                        'survey_id' => $survey->id,
                        'question_id' => $questionId,
                        'index' => $index
                    ]
                );
                return null;
            }
        }

        return $question;
    }

    /**
     * Determine the appropriate value type based on question template type
     *
     * @param string $questionTemplateType The template type (range, checkboxes, etc.)
     * @return string The appropriate value type (number, checkbox, text)
     */
    private function determineValueType(string $questionTemplateType): string
    {
        if ($questionTemplateType === 'range') {
            return 'number';
        } else if (in_array($questionTemplateType, ['checkboxes', 'checkbox'])) {
            return 'checkbox';
        }

        return 'text';
    }

    /**
     * Process a specific question response based on its type
     *
     * This method handles the processing logic for different question types,
     * routing to the appropriate handler method based on the question template type.
     *
     * @param Feedback $survey The survey being processed
     * @param \App\Models\Question $question The question being answered
     * @param string $questionTemplateType The type of question template
     * @param string $valueType The type of value being stored
     * @param mixed $responseValue The response value provided by the user
     * @param string $submissionId Unique ID for this submission
     * @return int Number of individual responses stored (can be >1 for checkboxes)
     * @throws ServiceException If there's an error during response processing
     */
    private function processQuestionResponse(
        Feedback $survey,
        $question,
        string $questionTemplateType,
        string $valueType,
        $responseValue,
        string $submissionId
    ): int {
        if (!$this->validateResponseValue($survey, $question, $valueType, $responseValue)) {
            return 0;
        }

        switch ($questionTemplateType) {
            case 'range':
                $this->storeRangeResponse($question, $valueType, $responseValue, $submissionId);
                return 1;

            case 'checkboxes':
            case 'checkbox':
                return $this->storeCheckboxResponse($survey, $question, $valueType, $responseValue, $submissionId);

            default:
                $this->storeTextResponse($question, $valueType, $responseValue, $submissionId);
                return 1;
        }
    }

    /**
     * Validate that a response value matches its expected type
     *
     * @param Feedback $survey The survey being processed
     * @param \App\Models\Question $question The question being answered
     * @param string $valueType The expected value type
     * @param mixed $responseValue The response value to validate
     * @return bool True if the value is valid
     * @throws ServiceException If the response value is invalid
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
     * Store a range-type response
     *
     * @param \App\Models\Question $question The question being answered
     * @param string $valueType The type of value (typically 'number')
     * @param mixed $rangeValue The numeric range value
     * @param string $submissionId Unique ID for this submission
     * @return void
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
     * Store a text-type response
     *
     * @param \App\Models\Question $question The question being answered
     * @param string $valueType The type of value (typically 'text')
     * @param mixed $textValue The text value
     * @param string $submissionId Unique ID for this submission
     * @return void
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
     * Store checkbox responses, which can be multiple values
     *
     * @param Feedback $survey The survey being processed
     * @param \App\Models\Question $question The question being answered
     * @param string $valueType The type of value (typically 'checkbox')
     * @param mixed $checkboxValues Array or string of checkbox values
     * @param string $submissionId Unique ID for this submission
     * @return int Number of checkbox values successfully stored
     * @throws ServiceException If the checkbox values are invalid
     */
    private function storeCheckboxResponse(Feedback $survey, $question, string $valueType, $checkboxValues, string $submissionId): int
    {
        if (is_array($checkboxValues)) {
            // Filter valid values
            $validCheckboxOptions = array_filter($checkboxValues, function($option) {
                return is_string($option) || is_numeric($option);
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

            $count = 0;
            foreach ($validCheckboxOptions as $option) {
                try {
                    Result::create([
                        'question_id' => $question->id,
                        'submission_id' => $submissionId,
                        'value_type' => $valueType,
                        'rating_value' => (string)$option,
                    ]);

                    ErrorLogger::logError(
                        "Stored checkbox option",
                        ErrorLogger::CATEGORY_BUSINESS_LOGIC,
                        ErrorLogger::LOG_LEVEL_INFO,
                        [
                            'survey_id' => $survey->id,
                            'question_id' => $question->id,
                            'option' => $option
                        ]
                    );

                    $count++;
                } catch (\Exception $e) {
                    // Log but continue with other options
                    ErrorLogger::logException(
                        $e,
                        ErrorLogger::CATEGORY_DATABASE,
                        ErrorLogger::LOG_LEVEL_ERROR,
                        [
                            'survey_id' => $survey->id,
                            'question_id' => $question->id,
                            'option' => $option
                        ]
                    );
                }
            }

            return $count;
        }

        if (is_string($checkboxValues) || is_numeric($checkboxValues)) {
            ErrorLogger::logError(
                "Converting single checkbox value to array",
                ErrorLogger::CATEGORY_BUSINESS_LOGIC,
                ErrorLogger::LOG_LEVEL_INFO,
                [
                    'survey_id' => $survey->id,
                    'question_id' => $question->id,
                    'value' => $checkboxValues
                ]
            );

            Result::create([
                'question_id' => $question->id,
                'submission_id' => $submissionId,
                'value_type' => $valueType,
                'rating_value' => (string)$checkboxValues,
            ]);

            return 1;
        }

        ErrorLogger::logError(
            "Invalid checkbox value type",
            ErrorLogger::CATEGORY_BUSINESS_LOGIC,
            ErrorLogger::LOG_LEVEL_WARNING,
            [
                'survey_id' => $survey->id,
                'question_id' => $question->id,
                'value_type' => gettype($checkboxValues)
            ]
        );

        return 0;
    }

    /**
     * Update the survey status after receiving responses
     *
     * @param Feedback $survey The survey to update
     * @return void
     */
    private function updateSurveyStatus(Feedback $survey): void
    {
        if (in_array($survey->status, ['draft', 'running'])) {
            $this->feedbackRepository->updateStatus($survey, 'running');
        }
    }
}