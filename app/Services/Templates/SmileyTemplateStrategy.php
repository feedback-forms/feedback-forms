<?php

namespace App\Services\Templates;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Support\Facades\Log;

class SmileyTemplateStrategy extends AbstractTemplateStrategy
{
    protected string $templateType = 'smiley';

    /**
     * Create questions for a smiley template survey
     *
     * @param Feedback $survey The survey to create questions for
     * @param array $data Additional data needed for creation
     * @return void
     */
    public function createQuestions(Feedback $survey, array $data): void
    {
        // Find or create a text question template
        $textQuestionTemplate = $this->getTextQuestionTemplate();

        // Create two questions: one for positive feedback and one for negative feedback
        Question::create([
            'feedback_template_id' => $data['template_id'],
            'feedback_id' => $survey->id,
            'question_template_id' => $textQuestionTemplate->id,
            'question' => 'Positive Feedback',
            'order' => 1,
        ]);

        Question::create([
            'feedback_template_id' => $data['template_id'],
            'feedback_id' => $survey->id,
            'question_template_id' => $textQuestionTemplate->id,
            'question' => 'Negative Feedback',
            'order' => 2,
        ]);
    }

    /**
     * Store responses for a smiley template survey
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $jsonData The JSON data containing the responses
     * @param string $submissionId The unique ID for this submission
     * @return void
     */
    public function storeResponses(Feedback $survey, array $jsonData, string $submissionId): void
    {
        // Validate the expected structure of jsonData
        $hasPositive = isset($jsonData['positive']) && is_string($jsonData['positive']);
        $hasNegative = isset($jsonData['negative']) && is_string($jsonData['negative']);

        if (!$hasPositive && !$hasNegative) {
            $this->logWarning("Invalid smiley response format: neither 'positive' nor 'negative' found", $survey, [
                'jsonData' => $jsonData
            ]);
            return;
        }

        // Get the positive and negative feedback questions
        $positiveQuestion = $survey->questions()->where('question', 'Positive Feedback')->first();
        $negativeQuestion = $survey->questions()->where('question', 'Negative Feedback')->first();

        // Store positive feedback if provided
        if ($positiveQuestion && $hasPositive && !empty($jsonData['positive'])) {
            $this->storeTextResult($positiveQuestion, $submissionId, $jsonData['positive'], $survey);
        } else if (!$positiveQuestion && $hasPositive && !empty($jsonData['positive'])) {
            $this->logWarning("Positive feedback question not found for smiley template", $survey);
        }

        // Store negative feedback if provided
        if ($negativeQuestion && $hasNegative && !empty($jsonData['negative'])) {
            $this->storeTextResult($negativeQuestion, $submissionId, $jsonData['negative'], $survey);
        } else if (!$negativeQuestion && $hasNegative && !empty($jsonData['negative'])) {
            $this->logWarning("Negative feedback question not found for smiley template", $survey);
        }

        // Update survey status
        $this->updateSurveyStatus($survey);
    }
}