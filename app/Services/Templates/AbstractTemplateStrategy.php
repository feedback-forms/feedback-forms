<?php

namespace App\Services\Templates;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\QuestionTemplate;
use App\Models\Result;
use Illuminate\Support\Facades\Log;

abstract class AbstractTemplateStrategy implements TemplateStrategy
{
    /**
     * The type of template this strategy handles (e.g., 'target', 'smiley')
     */
    protected string $templateType;

    /**
     * Check if this strategy can handle the given template
     *
     * @param string $templateName The name of the template
     * @return bool
     */
    public function canHandle(string $templateName): bool
    {
        return preg_match('/templates\.feedback\.' . $this->templateType . '$/', $templateName) === 1;
    }

    /**
     * Get or create a text question template
     *
     * @return QuestionTemplate
     */
    protected function getTextQuestionTemplate(): QuestionTemplate
    {
        return QuestionTemplate::firstOrCreate(
            ['type' => 'text'],
            ['min_value' => null, 'max_value' => null]
        );
    }

    /**
     * Get or create a range question template
     *
     * @param int $minValue Minimum value for the range
     * @param int $maxValue Maximum value for the range
     * @return QuestionTemplate
     */
    protected function getRangeQuestionTemplate(int $minValue = 1, int $maxValue = 5): QuestionTemplate
    {
        return QuestionTemplate::firstOrCreate(
            ['type' => 'range', 'min_value' => $minValue, 'max_value' => $maxValue],
            ['min_value' => $minValue, 'max_value' => $maxValue]
        );
    }

    /**
     * Log an info message with survey context
     *
     * @param string $message The message to log
     * @param Feedback $survey The survey for context
     * @param array $context Additional context data
     * @return void
     */
    protected function logInfo(string $message, Feedback $survey, array $context = []): void
    {
        Log::info($message, array_merge([
            'survey_id' => $survey->id,
            'template_type' => $this->templateType,
        ], $context));
    }

    /**
     * Log a warning message with survey context
     *
     * @param string $message The message to log
     * @param Feedback $survey The survey for context
     * @param array $context Additional context data
     * @return void
     */
    protected function logWarning(string $message, Feedback $survey, array $context = []): void
    {
        Log::warning($message, array_merge([
            'survey_id' => $survey->id,
            'template_type' => $this->templateType,
        ], $context));
    }

    /**
     * Log an error message with survey context
     *
     * @param string $message The message to log
     * @param Feedback $survey The survey for context
     * @param array $context Additional context data
     * @return void
     */
    protected function logError(string $message, Feedback $survey, array $context = []): void
    {
        Log::error($message, array_merge([
            'survey_id' => $survey->id,
            'template_type' => $this->templateType,
        ], $context));
    }

    /**
     * Store a text result for a question
     *
     * @param Question $question The question to store the result for
     * @param string $submissionId The unique ID for this submission
     * @param string $value The text value to store
     * @param Feedback $survey The survey for context (for logging)
     * @return Result|null The created result or null if there was an error
     */
    protected function storeTextResult(Question $question, string $submissionId, string $value, Feedback $survey): ?Result
    {
        try {
            $result = Result::create([
                'question_id' => $question->id,
                'submission_id' => $submissionId,
                'value_type' => 'text',
                'rating_value' => $value,
            ]);

            $this->logInfo("Stored text result", $survey, [
                'question_id' => $question->id,
            ]);

            return $result;
        } catch (\Exception $e) {
            $this->logError("Failed to store text result: {$e->getMessage()}", $survey, [
                'question_id' => $question->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Store a numeric result for a question
     *
     * @param Question $question The question to store the result for
     * @param string $submissionId The unique ID for this submission
     * @param int|string $value The numeric value to store
     * @param Feedback $survey The survey for context (for logging)
     * @return Result|null The created result or null if there was an error
     */
    protected function storeNumberResult(Question $question, string $submissionId, $value, Feedback $survey): ?Result
    {
        try {
            $result = Result::create([
                'question_id' => $question->id,
                'submission_id' => $submissionId,
                'value_type' => 'number',
                'rating_value' => (string)$value, // Ensure it's a string
            ]);

            $this->logInfo("Stored number result", $survey, [
                'question_id' => $question->id,
                'rating' => $value
            ]);

            return $result;
        } catch (\Exception $e) {
            $this->logError("Failed to store number result: {$e->getMessage()}", $survey, [
                'question_id' => $question->id,
                'rating' => $value,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Update the survey status to 'running' if it's draft or already running
     *
     * @param Feedback $survey The survey to update
     * @return void
     */
    protected function updateSurveyStatus(Feedback $survey): void
    {
        if (in_array($survey->status, ['draft', 'running'])) {
            try {
                $survey->update(['status' => 'running']);
            } catch (\Exception $e) {
                $this->logError("Failed to update survey status: {$e->getMessage()}", $survey);
            }
        }
    }
}