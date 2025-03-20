<?php

namespace App\Services\Templates;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Support\Facades\Log;

class TargetTemplateStrategy extends AbstractTemplateStrategy
{
    protected string $templateType = 'target';

    /**
     * Target statements used for creating questions
     */
    protected array $targetStatements = [
        'Ich lerne im Unterricht viel.',
        'Die Lehrkraft hat ein großes Hintergrundwissen.',
        'Die Lehrkraft ist immer gut vorbereitet.',
        'Die Lehrkraft zeigt Interesse an ihren Schülern.',
        'Die Lehrkraft sorgt für ein gutes Lernklima in der Klasse.',
        'Die Notengebung ist fair und nachvollziehbar.',
        'Ich konnte dem Unterricht immer gut folgen.',
        'Der Unterricht wird vielfältig gestaltet.'
    ];

    /**
     * Create questions for a target template survey
     *
     * @param Feedback $survey The survey to create questions for
     * @param array $data Additional data needed for creation
     * @return void
     */
    public function createQuestions(Feedback $survey, array $data): void
    {
        // Find or create a range question template for target segments
        $rangeQuestionTemplate = $this->getRangeQuestionTemplate(1, 5);

        foreach ($this->targetStatements as $index => $statement) {
            Question::create([
                'feedback_template_id' => $data['template_id'],
                'feedback_id' => $survey->id,
                'question_template_id' => $rangeQuestionTemplate->id,
                'question' => $statement,
                'order' => $index + 1,
            ]);
        }
    }

    /**
     * Store responses for a target template survey
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $jsonData The JSON data containing the responses
     * @param string $submissionId The unique ID for this submission
     * @return void
     */
    public function storeResponses(Feedback $survey, array $jsonData, string $submissionId): void
    {
        // Validate the expected structure of jsonData
        if (!isset($jsonData['ratings']) || !is_array($jsonData['ratings'])) {
            $this->logWarning("Invalid target response format: 'ratings' array missing or not an array", $survey, [
                'jsonData' => $jsonData
            ]);
            return;
        }

        // Load all questions for this survey
        $questions = $survey->questions()->get();

        // Create a mapping of segment index to question based on question text
        $segmentQuestionMap = [];
        foreach ($questions as $question) {
            $statementIndex = array_search($question->question, $this->targetStatements);
            if ($statementIndex !== false) {
                $segmentQuestionMap[$statementIndex] = $question;
            }
        }

        // Process each segment rating
        foreach ($jsonData['ratings'] as $ratingData) {
            // Validate the rating data structure
            if (!isset($ratingData['segment']) || !isset($ratingData['rating'])) {
                $this->logWarning("Invalid rating data in target response: missing 'segment' or 'rating'", $survey, [
                    'ratingData' => $ratingData
                ]);
                continue;
            }

            $segment = $ratingData['segment'];
            $ratingValue = $ratingData['rating'];

            // Validate segment index
            if (!isset($segmentQuestionMap[$segment])) {
                $this->logWarning("Invalid segment index or question not found for segment", $survey, [
                    'segment' => $segment,
                    'total_questions' => count($questions)
                ]);
                continue;
            }

            // Validate rating value (should be 1-5 for target template)
            if (!is_numeric($ratingValue) || $ratingValue < 1 || $ratingValue > 5) {
                $this->logWarning("Invalid rating value for target template", $survey, [
                    'segment' => $segment,
                    'rating' => $ratingValue
                ]);
                continue;
            }

            // Get corresponding question for this segment
            $question = $segmentQuestionMap[$segment];

            // Store the rating value
            $this->storeNumberResult($question, $submissionId, $ratingValue, $survey);
        }

        // Process open feedback text if provided
        $this->processOpenFeedback($survey, $jsonData, $submissionId);

        // Update survey status
        $this->updateSurveyStatus($survey);
    }

    /**
     * Process open feedback text if provided
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $jsonData The JSON data containing the responses
     * @param string $submissionId The unique ID for this submission
     * @return void
     */
    private function processOpenFeedback(Feedback $survey, array $jsonData, string $submissionId): void
    {
        if (!isset($jsonData['feedback']) || empty($jsonData['feedback'])) {
            return;
        }

        try {
            // Find or create a question for open feedback
            $openFeedbackQuestion = $survey->questions()
                ->where('question', 'Open Feedback')
                ->first();

            if (!$openFeedbackQuestion) {
                $textQuestionTemplate = $this->getTextQuestionTemplate();

                $openFeedbackQuestion = Question::create([
                    'feedback_template_id' => $survey->feedback_template_id,
                    'feedback_id' => $survey->id,
                    'question_template_id' => $textQuestionTemplate->id,
                    'question' => 'Open Feedback',
                    'order' => count($this->targetStatements) + 1,
                ]);
            }

            // Store the open feedback
            $this->storeTextResult($openFeedbackQuestion, $submissionId, $jsonData['feedback'], $survey);

        } catch (\Exception $e) {
            $this->logError("Failed to store target open feedback: {$e->getMessage()}", $survey);
        }
    }
}