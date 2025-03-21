<?php

namespace App\Services\Templates;

use App\Models\Feedback;
use App\Models\QuestionTemplate;

interface TemplateStrategy
{
    /**
     * Create questions for a survey based on a specific template type
     *
     * @param Feedback $survey The survey to create questions for
     * @param array $data Additional data needed for creation
     * @return void
     */
    public function createQuestions(Feedback $survey, array $data): void;

    /**
     * Store responses for a specific template type
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $jsonData The JSON data containing the responses
     * @param string $submissionId The unique ID for this submission
     * @return void
     */
    public function storeResponses(Feedback $survey, array $jsonData, string $submissionId): void;

    /**
     * Check if this strategy can handle the given template
     *
     * @param string|null $templateType The type of the template
     * @param string|null $templateName The name of the template (for backward compatibility)
     * @return bool
     */
    public function canHandle(?string $templateType = null, ?string $templateName = null): bool;
}