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
    protected $templateStrategyFactory;
    protected $feedbackRepository;

    public function __construct(
        Templates\TemplateStrategyFactory $templateStrategyFactory,
        FeedbackRepository $feedbackRepository
    ) {
        $this->templateStrategyFactory = $templateStrategyFactory;
        $this->feedbackRepository = $feedbackRepository;
    }

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

    private function determineValueType(string $questionTemplateType): string
    {
        if ($questionTemplateType === 'range') {
            return 'number';
        } else if (in_array($questionTemplateType, ['checkboxes', 'checkbox'])) {
            return 'checkbox';
        }

        return 'text';
    }

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

    private function storeRangeResponse($question, string $valueType, $rangeValue, string $submissionId): void
    {
        Result::create([
            'question_id' => $question->id,
            'submission_id' => $submissionId,
            'value_type' => $valueType,
            'rating_value' => $rangeValue,
        ]);
    }

    private function storeTextResponse($question, string $valueType, $textValue, string $submissionId): void
    {
        Result::create([
            'question_id' => $question->id,
            'submission_id' => $submissionId,
            'value_type' => $valueType,
            'rating_value' => $textValue,
        ]);
    }

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

    private function updateSurveyStatus(Feedback $survey): void
    {
        if (in_array($survey->status, ['draft', 'running'])) {
            $this->feedbackRepository->updateStatus($survey, 'running');
        }
    }
}