<?php

namespace App\Services\Templates;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Support\Facades\Log;

class TableTemplateStrategy extends AbstractTemplateStrategy
{
    protected string $templateType = 'table';

    /**
     * Create questions for a table template survey
     *
     * Note: This method assumes that the table template questions are already defined
     * in the template itself, so we don't need to create them here (they will be created
     * by the default implementation in SurveyService)
     *
     * @param Feedback $survey The survey to create questions for
     * @param array $data Additional data needed for creation
     * @return void
     */
    public function createQuestions(Feedback $survey, array $data): void
    {
        // Table template questions are handled by the default template question creation
        // as they are defined in the template itself, not generated here
    }

    /**
     * Store responses for a table template survey
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $jsonData The JSON data containing the responses
     * @param string $submissionId The unique ID for this submission
     * @return void
     */
    public function storeResponses(Feedback $survey, array $jsonData, string $submissionId): void
    {
        $this->logInfo("Processing table template response", $survey, [
            'submission_id' => $submissionId
        ]);

        // Validate the expected structure of jsonData
        if (!isset($jsonData['ratings']) || !is_array($jsonData['ratings'])) {
            $this->logWarning("Invalid table response format: 'ratings' array missing or not an array", $survey, [
                'jsonData' => $jsonData
            ]);
            return;
        }

        // Load all questions for this survey
        $questions = $survey->questions()->get();

        // Process each question rating
        foreach ($jsonData['ratings'] as $questionKey => $ratingValue) {
            // Find the corresponding question by the question text
            $question = $questions->firstWhere('question', $questionKey);

            if (!$question) {
                $this->logWarning("Question not found for table template rating", $survey, [
                    'questionKey' => $questionKey
                ]);
                continue;
            }

            // Validate rating value (should be 1-5 for table template questions)
            if (!is_numeric($ratingValue) || $ratingValue < 1 || $ratingValue > 5) {
                $this->logWarning("Invalid rating value for table template question", $survey, [
                    'questionKey' => $questionKey,
                    'rating' => $ratingValue
                ]);
                continue;
            }

            // Store the rating
            $this->storeNumberResult($question, $submissionId, $ratingValue, $survey);
        }

        // Process feedback responses if provided
        $this->processTableFeedback($survey, $jsonData, $submissionId, $questions);

        // Update survey status
        $this->updateSurveyStatus($survey);
    }

    /**
     * Process feedback responses for table template
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $jsonData The JSON data containing the responses
     * @param string $submissionId The unique ID for this submission
     * @param \Illuminate\Database\Eloquent\Collection $questions The survey questions
     * @return void
     */
    private function processTableFeedback(Feedback $survey, array $jsonData, string $submissionId, $questions): void
    {
        if (!isset($jsonData['feedback']) || !is_array($jsonData['feedback'])) {
            return;
        }

        // Find the feedback questions if they exist
        $positiveQuestion = $questions->firstWhere('question', 'Das hat mir besonders gut gefallen');
        $negativeQuestion = $questions->firstWhere('question', 'Das hat mir nicht gefallen');
        $suggestionsQuestion = $questions->firstWhere('question', 'Verbesserungsvorschläge');

        // Store positive feedback
        if ($positiveQuestion && isset($jsonData['feedback']['positive']) && !empty($jsonData['feedback']['positive'])) {
            $this->storeTextResult($positiveQuestion, $submissionId, $jsonData['feedback']['positive'], $survey);
        }

        // Store negative feedback
        if ($negativeQuestion && isset($jsonData['feedback']['negative']) && !empty($jsonData['feedback']['negative'])) {
            $this->storeTextResult($negativeQuestion, $submissionId, $jsonData['feedback']['negative'], $survey);
        }

        // Store suggestions feedback
        if ($suggestionsQuestion && isset($jsonData['feedback']['suggestions']) && !empty($jsonData['feedback']['suggestions'])) {
            $this->storeTextResult($suggestionsQuestion, $submissionId, $jsonData['feedback']['suggestions'], $survey);
        }
    }
}