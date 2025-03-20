<?php

namespace App\Services\Templates;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Support\Facades\Log;

class DefaultTemplateStrategy extends AbstractTemplateStrategy
{
    protected string $templateType = 'default';

    /**
     * Check if this strategy can handle the given template
     * Override the default implementation to always return true for templates
     * not handled by other strategies
     *
     * @param string $templateName The name of the template
     * @return bool
     */
    public function canHandle(string $templateName): bool
    {
        return true;
    }

    /**
     * Create questions for a default template survey
     *
     * This is a fallback for templates not handled by specialized strategies.
     * It creates a single general feedback question if no questions are defined in the template.
     *
     * @param Feedback $survey The survey to create questions for
     * @param array $data Additional data needed for creation
     * @return void
     */
    public function createQuestions(Feedback $survey, array $data): void
    {
        // If template already has questions defined, we don't create additional ones
        // This will be handled by the default implementation in SurveyService
        if ($survey->questions()->count() > 0) {
            return;
        }

        // Create a default text question
        $textQuestionTemplate = $this->getTextQuestionTemplate();

        Question::create([
            'feedback_template_id' => $data['template_id'],
            'feedback_id' => $survey->id,
            'question_template_id' => $textQuestionTemplate->id,
            'question' => 'General Feedback',
            'order' => 1,
        ]);
    }

    /**
     * Store responses for a default template survey
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $jsonData The JSON data containing the responses
     * @param string $submissionId The unique ID for this submission
     * @return void
     */
    public function storeResponses(Feedback $survey, array $jsonData, string $submissionId): void
    {
        // Get the first question as a fallback
        $firstQuestion = $survey->questions->first();

        // If no question exists, create a default one
        if (!$firstQuestion) {
            try {
                // Create a default question for this survey
                $textQuestionTemplate = $this->getTextQuestionTemplate();

                $firstQuestion = Question::create([
                    'feedback_template_id' => $survey->feedback_template_id,
                    'feedback_id' => $survey->id,
                    'question_template_id' => $textQuestionTemplate->id,
                    'question' => 'General Feedback',
                    'order' => 1,
                ]);
            } catch (\Exception $e) {
                $this->logError("Failed to create default question for response", $survey, [
                    'error' => $e->getMessage()
                ]);
                return;
            }
        }

        // Try to find any usable data in the JSON response
        $responseText = "Unstructured response";

        // If we have a string value at the top level, use that
        if (is_string($jsonData)) {
            $responseText = $jsonData;
        }
        // If there's a feedback field, use that
        else if (isset($jsonData['feedback']) && is_string($jsonData['feedback'])) {
            $responseText = $jsonData['feedback'];
        }
        // Otherwise, attempt to serialize the entire JSON data
        else {
            try {
                $responseText = "Data: " . json_encode($jsonData, JSON_PRETTY_PRINT);
            } catch (\Exception $e) {
                $responseText = "Unsupported response format";
            }
        }

        // Store a simple text response
        try {
            $this->storeTextResult($firstQuestion, $submissionId, $responseText, $survey);
        } catch (\Exception $e) {
            $this->logError("Failed to store response", $survey, [
                'question_id' => $firstQuestion->id,
                'error' => $e->getMessage()
            ]);
        }

        // Update survey status
        $this->updateSurveyStatus($survey);
    }
}