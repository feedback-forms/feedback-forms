<?php

namespace App\Services;

use App\Models\{Feedback, Question, Result, Feedback_template, Question_template};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SurveyService
{
    /**
     * Create a new survey from template
     */
    public function createFromTemplate(array $data, int $userId): Feedback
    {
        return DB::transaction(function () use ($data, $userId) {
            // Create the feedback/survey
            $survey = Feedback::create([
                'name' => $data['name'] ?? null,
                'user_id' => $userId,
                'feedback_template_id' => $data['template_id'],
                'accesskey' => $this->generateUniqueAccessKey(),
                'limit' => $data['response_limit'] ?? -1,
                'expire_date' => Carbon::parse($data['expire_date']),
                'school_year' => $data['school_year'] ?? null,
                'department' => $data['department'] ?? null,
                'grade_level' => $data['grade_level'] ?? null,
                'class' => $data['class'] ?? null,
                'subject' => $data['subject'] ?? null,
            ]);

            // Get the template and its associated question templates
            $template = Feedback_template::with('questions.question_template')->findOrFail($data['template_id']);
            $templateName = $template->name ?? '';

            // Special handling for target template
            if (str_contains($templateName, 'templates.feedback.target')) {
                // Create 8 questions for the target template, one for each segment
                $targetStatements = [
                    'Ich lerne im Unterricht viel.',
                    'Die Lehrkraft hat ein großes Hintergrundwissen.',
                    'Die Lehrkraft ist immer gut vorbereitet.',
                    'Die Lehrkraft zeigt Interesse an ihren Schülern.',
                    'Die Lehrkraft sorgt für ein gutes Lernklima in der Klasse.',
                    'Die Notengebung ist fair und nachvollziehbar.',
                    'Ich konnte dem Unterricht immer gut folgen.',
                    'Der Unterricht wird vielfältig gestaltet.'
                ];

                // Find or create a range question template for target segments
                $rangeQuestionTemplate = Question_template::firstOrCreate(
                    ['type' => 'range', 'min_value' => 1, 'max_value' => 5],
                    ['min_value' => 1, 'max_value' => 5]
                );

                foreach ($targetStatements as $index => $statement) {
                    Question::create([
                        'feedback_template_id' => $data['template_id'],
                        'feedback_id' => $survey->id,
                        'question_template_id' => $rangeQuestionTemplate->id,
                        'question' => $statement,
                        'order' => $index + 1,
                    ]);
                }

                return $survey;
            }
            // Special handling for smiley template
            else if (str_contains($templateName, 'templates.feedback.smiley')) {
                // Find or create a text question template
                $textQuestionTemplate = Question_template::firstOrCreate(
                    ['type' => 'text'],
                    ['min_value' => null, 'max_value' => null]
                );

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

                return $survey;
            }
            // If the template has predefined questions, use those
            else if ($template->questions->count() > 0) {
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
            // For other templates or fallback
            else {
                // Find or create a text question template to use
                $textQuestionTemplate = Question_template::firstOrCreate(
                    ['type' => 'text'],
                    ['min_value' => null, 'max_value' => null]
                );

                // Create a default question for this survey
                Question::create([
                    'feedback_template_id' => $data['template_id'],
                    'feedback_id' => $survey->id,
                    'question_template_id' => $textQuestionTemplate->id,
                    'question' => 'General Feedback', // Generic question text
                    'order' => 1,
                ]);
            }

            return $survey;
        });
    }

    /**
     * Generate a unique 8-character access key
     */
    private function generateUniqueAccessKey(): string
    {
        do {
            $key = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Feedback::where('accesskey', $key)->exists());

        return $key;
    }

    /**
     * Validate if survey can be answered (not expired, within limits)
     *
     * @param Feedback $survey The survey to check
     * @return bool True if the survey can be answered, false otherwise
     */
    public function canBeAnswered(Feedback $survey): bool
    {
        if ($survey->expire_date < Carbon::now()) {
            return false;
        }

        if ($survey->limit > 0 && $survey->submission_count >= $survey->limit) {
            return false;
        }

        return true;
    }

    /**
     * Store survey responses
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $responses The responses to store
     * @return bool True if responses were stored successfully, false otherwise
     * @throws \Exception If there's an error during response storage
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

                // Check if this is a JSON data structure (from template-specific forms like target)
                if (isset($responses['json_data']) && is_array($responses['json_data'])) {
                    try {
                        // Handle template-specific response format using pre-decoded JSON data
                        $this->storeTemplateSpecificResponses($survey, $responses['json_data'], $submissionId);

                        // Set the status to update if it's a draft or running
                        if (in_array($survey->status, ['draft', 'running'])) {
                            $survey->update(['status' => 'running']);
                        }

                        return true;
                    } catch (\Exception $e) {
                        Log::error('Error processing structured JSON data: ' . $e->getMessage(), [
                            'survey_id' => $survey->id,
                            'response_data' => $responses['json_data'],
                            'exception' => $e
                        ]);
                        return false;
                    }
                // Legacy check for JSON string (can be removed after frontend updates)
                } else if (count($responses) === 1 && isset($responses[0]) && is_string($responses[0])) {
                    try {
                        $jsonData = json_decode($responses[0], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                            // Handle template-specific response format
                            $this->storeTemplateSpecificResponses($survey, $jsonData, $submissionId);

                            // Set the status to update if it's a draft or running
                            if (in_array($survey->status, ['draft', 'running'])) {
                                $survey->update(['status' => 'running']);
                            }

                            return true;
                        }
                    } catch (\Exception $e) {
                        Log::error('Error parsing JSON response: ' . $e->getMessage(), [
                            'survey_id' => $survey->id,
                            'response_data' => $responses[0],
                            'exception' => $e
                        ]);
                        return false;
                    }
                }

                // Handle regular question-by-question responses
                foreach ($responses as $questionId => $value) {
                    // Skip non-numeric keys (like 'feedback' from the form)
                    // Non-numeric keys are typically form metadata or special fields that are not directly tied to questions
                    // 'feedback' is handled separately as a special case for general survey feedback
                    if (!is_numeric($questionId) && $questionId !== 'feedback') {
                        continue;
                    }

                    // Special handling for feedback
                    if ($questionId === 'feedback' && !empty($value)) {
                        $result = Result::create([
                            'question_id' => $questions->first()->id ?? null,
                            'submission_id' => $submissionId,
                            'value_type' => 'text',
                            'rating_value' => $value,
                        ]);

                        $responseCount++;
                        continue;
                    }

                    // Verify the question belongs to this survey
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
                            continue; // Skip if question doesn't belong to this survey
                        }
                    }

                    // Get the question template type
                    $questionTemplateType = $question->question_template->type ?? 'text'; // Default to 'text'

                    // Set the appropriate value_type based on question template type
                    $valueType = 'text'; // Default

                    switch ($questionTemplateType) {
                        case 'range':
                            $valueType = 'number';
                            break;
                        case 'checkboxes':
                        case 'checkbox':
                            $valueType = 'checkbox';
                            break;
                        case 'textarea':
                        case 'text':
                            $valueType = 'text';
                            break;
                        default:
                            $valueType = 'text';
                            break;
                    }

                    // Data validation based on value_type
                    $isValidValue = true;
                    switch ($valueType) {
                        case 'number':
                            if (!is_numeric($value)) {
                                $isValidValue = false;
                                Log::warning("Invalid rating_value for number type", [
                                    'survey_id' => $survey->id,
                                    'question_id' => $question->id,
                                    'value_type' => $valueType,
                                    'provided_value' => $value,
                                ]);
                            }
                            break;
                        // For 'text' and 'checkbox', any string value is considered valid for this basic example.
                        default:
                            break;
                    }

                    if (!$isValidValue) {
                        Log::warning("Skipping invalid response value", [
                            'survey_id' => $survey->id,
                            'question_id' => $question->id,
                            'value' => $value,
                        ]);
                        continue;
                    }

                    // Handle different question types
                    switch ($questionTemplateType) {
                        case 'range':
                            // Create a new result record with a scalar rating value
                            $result = Result::create([
                                'question_id' => $question->id,
                                'submission_id' => $submissionId,
                                'value_type' => $valueType,
                                'rating_value' => $value,
                            ]);
                            break;

                        case 'checkboxes':
                        case 'checkbox':
                            // If the value is an array of checkbox options
                            if (is_array($value)) {
                                // Validate the array values
                                $validValues = array_filter($value, function($optionValue) {
                                    return is_string($optionValue) || is_numeric($optionValue);
                                });

                                if (empty($validValues)) {
                                    Log::warning("No valid values found in checkbox response", [
                                        'survey_id' => $survey->id,
                                        'question_id' => $question->id,
                                        'values' => $value
                                    ]);
                                    continue 2; // Skip to next response
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

                                    $responseCount += count($validValues);
                                } catch (\Exception $e) {
                                    Log::error("Failed to store checkbox options", [
                                        'survey_id' => $survey->id,
                                        'question_id' => $question->id,
                                        'values' => $validValues,
                                        'error' => $e->getMessage()
                                    ]);
                                }

                                // Since we've already created the results above, continue to next response
                                continue 2; // Skip the rest of the switch and outer loop iteration
                            } else if (is_string($value) || is_numeric($value)) {
                                // Handle case where a single value is submitted instead of an array
                                Log::info("Converting single checkbox value to array", [
                                    'survey_id' => $survey->id,
                                    'question_id' => $question->id,
                                    'value' => $value
                                ]);
                                // Continue normal processing with the single value
                            } else {
                                Log::warning("Invalid checkbox value type", [
                                    'survey_id' => $survey->id,
                                    'question_id' => $question->id,
                                    'value_type' => gettype($value)
                                ]);
                                continue 2; // Skip to next response
                            }

                            // Create a single result for a single checkbox value
                            $result = Result::create([
                                'question_id' => $question->id,
                                'submission_id' => $submissionId,
                                'value_type' => $valueType,
                                'rating_value' => (string)$value,
                            ]);
                            break;

                        default: // Default case, e.g., 'text' or unknown types
                            // Create a new result with default text value_type
                            $result = Result::create([
                                'question_id' => $question->id,
                                'submission_id' => $submissionId,
                                'value_type' => $valueType,
                                'rating_value' => $value,
                            ]);
                            break;
                    }

                    $responseCount++;
                }

                // Only increment if we actually stored responses
                if ($responseCount > 0) {
                    // Set the status to update if it's a draft or running
                    if (in_array($survey->status, ['draft', 'running'])) {
                        $survey->update(['status' => 'running']);
                    }

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
            Log::error('Error storing survey responses: ' . $e->getMessage(), [
                'survey_id' => $survey->id,
                'exception' => $e,
                'exception_class' => get_class($e),
                'exception_trace' => $e->getTraceAsString(),
                'responses' => $responses
            ]);
            return false;
        }
    }

    /**
     * Store responses from template-specific forms (like target diagram)
     */
    private function storeTemplateSpecificResponses(Feedback $survey, array $jsonData, string $submissionId): void
    {
        // Get the template type
        $templateName = $survey->feedback_template->name ?? '';
        $templateType = '';
        if (preg_match('/templates\.feedback\.(\w+)$/', $templateName, $matches)) {
            $templateType = $matches[1];
        }

        // Log the template type for debugging
        Log::info("Processing template-specific response", [
            'survey_id' => $survey->id,
            'template_type' => $templateType,
            'submission_id' => $submissionId
        ]);

        // For target template
        if ($templateType === 'target') {
            // Validate the expected structure of jsonData
            if (!isset($jsonData['ratings']) || !is_array($jsonData['ratings'])) {
                Log::warning("Invalid target response format: 'ratings' array missing or not an array", [
                    'survey_id' => $survey->id,
                    'jsonData' => $jsonData
                ]);
                return;
            }

            // Define the expected statements for target template
            $targetStatements = [
                'Ich lerne im Unterricht viel.',
                'Die Lehrkraft hat ein großes Hintergrundwissen.',
                'Die Lehrkraft ist immer gut vorbereitet.',
                'Die Lehrkraft zeigt Interesse an ihren Schülern.',
                'Die Lehrkraft sorgt für ein gutes Lernklima in der Klasse.',
                'Die Notengebung ist fair und nachvollziehbar.',
                'Ich konnte dem Unterricht immer gut folgen.',
                'Der Unterricht wird vielfältig gestaltet.'
            ];

            // Load all questions for this survey
            $questions = $survey->questions()->get();

            // Create a mapping of segment index to question based on question text
            $segmentQuestionMap = [];
            foreach ($questions as $question) {
                $statementIndex = array_search($question->question, $targetStatements);
                if ($statementIndex !== false) {
                    $segmentQuestionMap[$statementIndex] = $question;
                }
            }

            // Process each segment rating
            foreach ($jsonData['ratings'] as $ratingData) {
                // Validate the rating data structure
                if (!isset($ratingData['segment']) || !isset($ratingData['rating'])) {
                    Log::warning("Invalid rating data in target response: missing 'segment' or 'rating'", [
                        'survey_id' => $survey->id,
                        'ratingData' => $ratingData
                    ]);
                    continue;
                }

                $segment = $ratingData['segment'];
                $ratingValue = $ratingData['rating'];

                // Validate segment index
                if (!isset($segmentQuestionMap[$segment])) {
                    Log::warning("Invalid segment index or question not found for segment", [
                        'survey_id' => $survey->id,
                        'segment' => $segment,
                        'total_questions' => count($questions)
                    ]);
                    continue;
                }

                // Validate rating value (should be 1-5 for target template)
                if (!is_numeric($ratingValue) || $ratingValue < 1 || $ratingValue > 5) {
                    Log::warning("Invalid rating value for target template", [
                        'survey_id' => $survey->id,
                        'segment' => $segment,
                        'rating' => $ratingValue
                    ]);
                    continue;
                }

                // Get corresponding question for this segment
                $question = $segmentQuestionMap[$segment];

                // Create a result with scalar rating value
                try {
                    $result = Result::create([
                        'question_id' => $question->id,
                        'submission_id' => $submissionId,
                        'value_type' => 'number',
                        'rating_value' => (string)$ratingValue, // Ensure it's a string
                    ]);

                    Log::info("Stored target segment rating", [
                        'survey_id' => $survey->id,
                        'segment' => $segment,
                        'question_id' => $question->id,
                        'rating' => $ratingValue
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to store target segment rating", [
                        'survey_id' => $survey->id,
                        'segment' => $segment,
                        'question_id' => $question->id,
                        'rating' => $ratingValue,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        // For smiley template
        else if ($templateType === 'smiley') {
            // Validate the expected structure of jsonData
            $hasPositive = isset($jsonData['positive']) && is_string($jsonData['positive']);
            $hasNegative = isset($jsonData['negative']) && is_string($jsonData['negative']);

            if (!$hasPositive && !$hasNegative) {
                Log::warning("Invalid smiley response format: neither 'positive' nor 'negative' found", [
                    'survey_id' => $survey->id,
                    'jsonData' => $jsonData
                ]);
                return;
            }

            // Get the positive and negative feedback questions
            $positiveQuestion = $survey->questions()->where('question', 'Positive Feedback')->first();
            $negativeQuestion = $survey->questions()->where('question', 'Negative Feedback')->first();

            // Store positive feedback if provided
            if ($positiveQuestion && $hasPositive && !empty($jsonData['positive'])) {
                try {
                    Result::create([
                        'question_id' => $positiveQuestion->id,
                        'submission_id' => $submissionId,
                        'value_type' => 'text',
                        'rating_value' => $jsonData['positive'],
                    ]);

                    Log::info("Stored smiley positive feedback", [
                        'survey_id' => $survey->id,
                        'question_id' => $positiveQuestion->id
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to store smiley positive feedback", [
                        'survey_id' => $survey->id,
                        'question_id' => $positiveQuestion->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else if (!$positiveQuestion) {
                Log::warning("Positive feedback question not found for smiley template", [
                    'survey_id' => $survey->id
                ]);
            }

            // Store negative feedback if provided
            if ($negativeQuestion && $hasNegative && !empty($jsonData['negative'])) {
                try {
                    Result::create([
                        'question_id' => $negativeQuestion->id,
                        'submission_id' => $submissionId,
                        'value_type' => 'text',
                        'rating_value' => $jsonData['negative'],
                    ]);

                    Log::info("Stored smiley negative feedback", [
                        'survey_id' => $survey->id,
                        'question_id' => $negativeQuestion->id
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to store smiley negative feedback", [
                        'survey_id' => $survey->id,
                        'question_id' => $negativeQuestion->id,
                        'error' => $e->getMessage()
                    ]);
                }
            } else if (!$negativeQuestion) {
                Log::warning("Negative feedback question not found for smiley template", [
                    'survey_id' => $survey->id
                ]);
            }
        }
        // For table template
        else if ($templateType === 'table') {
            Log::info("Processing table template response", [
                'survey_id' => $survey->id,
                'submission_id' => $submissionId,
                'data' => $jsonData
            ]);

            // Validate the expected structure of jsonData for table template
            if (!isset($jsonData['ratings']) || !is_array($jsonData['ratings'])) {
                Log::warning("Invalid table response format: 'ratings' array missing or not an array", [
                    'survey_id' => $survey->id,
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
                    Log::warning("Question not found for table template rating", [
                        'survey_id' => $survey->id,
                        'questionKey' => $questionKey
                    ]);
                    continue;
                }

                // Validate rating value (should be 1-5 for table template questions)
                if (!is_numeric($ratingValue) || $ratingValue < 1 || $ratingValue > 5) {
                    Log::warning("Invalid rating value for table template question", [
                        'survey_id' => $survey->id,
                        'questionKey' => $questionKey,
                        'rating' => $ratingValue
                    ]);
                    continue;
                }

                // Create a result with scalar rating value
                try {
                    $result = Result::create([
                        'question_id' => $question->id,
                        'submission_id' => $submissionId,
                        'value_type' => 'number',
                        'rating_value' => (string)$ratingValue, // Ensure it's a string
                    ]);

                    Log::info("Stored table question rating", [
                        'survey_id' => $survey->id,
                        'question_id' => $question->id,
                        'rating' => $ratingValue
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to store table question rating", [
                        'survey_id' => $survey->id,
                        'question_id' => $question->id,
                        'rating' => $ratingValue,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Store open feedback responses if provided
            if (isset($jsonData['feedback']) && is_array($jsonData['feedback'])) {
                // Find the general feedback questions if they exist
                $positiveQuestion = $questions->firstWhere('question', 'Das hat mir besonders gut gefallen');
                $negativeQuestion = $questions->firstWhere('question', 'Das hat mir nicht gefallen');
                $suggestionsQuestion = $questions->firstWhere('question', 'Verbesserungsvorschläge');

                // Store positive feedback
                if ($positiveQuestion && isset($jsonData['feedback']['positive']) && !empty($jsonData['feedback']['positive'])) {
                    try {
                        Result::create([
                            'question_id' => $positiveQuestion->id,
                            'submission_id' => $submissionId,
                            'value_type' => 'text',
                            'rating_value' => $jsonData['feedback']['positive'],
                        ]);

                        Log::info("Stored table positive feedback", [
                            'survey_id' => $survey->id,
                            'question_id' => $positiveQuestion->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to store table positive feedback", [
                            'survey_id' => $survey->id,
                            'question_id' => $positiveQuestion->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Store negative feedback
                if ($negativeQuestion && isset($jsonData['feedback']['negative']) && !empty($jsonData['feedback']['negative'])) {
                    try {
                        Result::create([
                            'question_id' => $negativeQuestion->id,
                            'submission_id' => $submissionId,
                            'value_type' => 'text',
                            'rating_value' => $jsonData['feedback']['negative'],
                        ]);

                        Log::info("Stored table negative feedback", [
                            'survey_id' => $survey->id,
                            'question_id' => $negativeQuestion->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to store table negative feedback", [
                            'survey_id' => $survey->id,
                            'question_id' => $negativeQuestion->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Store suggestions feedback
                if ($suggestionsQuestion && isset($jsonData['feedback']['suggestions']) && !empty($jsonData['feedback']['suggestions'])) {
                    try {
                        Result::create([
                            'question_id' => $suggestionsQuestion->id,
                            'submission_id' => $submissionId,
                            'value_type' => 'text',
                            'rating_value' => $jsonData['feedback']['suggestions'],
                        ]);

                        Log::info("Stored table suggestions feedback", [
                            'survey_id' => $survey->id,
                            'question_id' => $suggestionsQuestion->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Failed to store table suggestions feedback", [
                            'survey_id' => $survey->id,
                            'question_id' => $suggestionsQuestion->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        }
        // Fallback for other template types or if template-specific handling fails
        else {
            Log::warning("Unsupported template type or invalid data format", [
                'survey_id' => $survey->id,
                'template_type' => $templateType,
                'data' => $jsonData
            ]);

            // Get the first question as a fallback
            $firstQuestion = $survey->questions->first();

            // If no question exists, create a default one
            if (!$firstQuestion) {
                try {
                    // Find or create a text question template
                    $textQuestionTemplate = Question_template::firstOrCreate(
                        ['type' => 'text'],
                        ['min_value' => null, 'max_value' => null]
                    );

                    // Create a default question for this survey
                    $firstQuestion = Question::create([
                        'feedback_template_id' => $survey->feedback_template_id,
                        'feedback_id' => $survey->id,
                        'question_template_id' => $textQuestionTemplate->id,
                        'question' => 'General Feedback', // Generic question text
                        'order' => 1,
                    ]);

                    Log::info("Created default question for unsupported template response", [
                        'survey_id' => $survey->id,
                        'question_id' => $firstQuestion->id,
                        'template_type' => $templateType
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to create default question for template response", [
                        'survey_id' => $survey->id,
                        'template_type' => $templateType,
                        'error' => $e->getMessage()
                    ]);
                    return;
                }
            }

            // Store a simple text response indicating the template type
            try {
                Result::create([
                    'question_id' => $firstQuestion->id,
                    'submission_id' => $submissionId,
                    'value_type' => 'text',
                    'rating_value' => "Response from {$templateType} template",
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to store fallback response", [
                    'survey_id' => $survey->id,
                    'question_id' => $firstQuestion->id,
                    'template_type' => $templateType,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Set the status to running
        if (in_array($survey->status, ['draft', 'running'])) {
            try {
                $survey->update(['status' => 'running']);
            } catch (\Exception $e) {
                Log::error("Failed to update survey status", [
                    'survey_id' => $survey->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Calculate statistics for a survey
     *
     * This method processes all questions in a survey and calculates appropriate
     * statistics based on the question type. It handles different question types
     * (range, checkbox, text, etc.) and generates statistics like averages, medians,
     * and distributions.
     *
     * @param Feedback $survey The survey to calculate statistics for
     * @return array An array of statistics data for each question
     */
    public function calculateStatisticsForSurvey(Feedback $survey): array
    {
        // Format: statsArray[questionIndex] = ['question' => Question, 'template_type' => templateType, 'data' => [...]]
        $statistics = [];

        try {
            // Ensure all needed relationships are loaded
            if (!$survey->relationLoaded('questions')) {
                $survey->load('questions');
            }

            if (!$survey->relationLoaded('feedback_template')) {
                $survey->load('feedback_template');
            }

            $submissionCount = $survey->submissions()->count();

            // Log survey processing for debugging
            \Log::debug('Processing statistics for survey', [
                'survey_id' => $survey->id,
                'submission_count' => $submissionCount,
                'template' => $survey->feedback_template->name ?? 'unknown'
            ]);

            if ($submissionCount == 0) {
                // If there are no submissions, return empty statistics
                \Log::debug('No submissions found for survey', ['survey_id' => $survey->id]);
                return $statistics;
            }

            // Get the feedback template name (used to determine special processing)
            $templateName = $survey->feedback_template->name ?? '';
            $isTableSurvey = str_contains($templateName, 'templates.feedback.table');

            // Special handling for certain template types
            if (str_contains($templateName, 'templates.feedback.smiley')) {
                // For smiley template, we need to calculate the average smiley rating
                // and collect positive and negative feedback

                // ... [existing code for smiley template]

            }
            else if (str_contains($templateName, 'templates.feedback.target')) {
                // For target template, we need to calculate statistics for each segment
                \Log::debug('Processing target survey statistics', [
                    'survey_id' => $survey->id,
                    'questions_count' => $survey->questions->count()
                ]);

                // Eager load all question templates and results for better performance
                if (!$survey->questions->isEmpty() &&
                    (!$survey->questions->first()->relationLoaded('question_template') ||
                     !$survey->questions->first()->relationLoaded('results'))) {
                    $survey->load('questions.question_template', 'questions.results');
                }

                // Calculate target-specific statistics
                $segmentStatisticsData = $this->calculateTargetStatistics($survey);

                // Add a marker with target diagram data
                $statistics[] = [
                    'question' => null,
                    'template_type' => 'target',
                    'data' => [
                        'submission_count' => $submissionCount,
                        'segment_statistics' => $segmentStatisticsData,
                    ],
                ];

                // We'll still process individual questions below in the generic loop,
                // but the template-specific display will use the segment_statistics data
            }
            else if ($isTableSurvey) {
                // For table templates, we need to calculate statistics for each question,
                // as table templates are composed of multiple range-type questions

                \Log::debug('Processing table survey statistics', [
                    'survey_id' => $survey->id,
                    'questions_count' => $survey->questions->count()
                ]);

                // Eager load all question templates and results for better performance
                if (!$survey->questions->isEmpty() &&
                    (!$survey->questions->first()->relationLoaded('question_template') ||
                     !$survey->questions->first()->relationLoaded('results'))) {
                    $survey->load('questions.question_template', 'questions.results');
                }

                // Process individual questions before generating the marker so we can pass categories
                // to the table marker
                $tempStats = [];
                foreach ($survey->questions as $question) {
                    // Get the template type, fallback to text if not available
                    $questionTemplateType = $question->question_template->type ?? 'text';

                    // Calculate question-specific statistics
                    $questionStatistics = $this->calculateQuestionStatistics($question, $questionTemplateType);

                    // Log question statistics for debugging
                    \Log::debug('Question statistics', [
                        'question_id' => $question->id,
                        'question' => $question->question,
                        'template_type' => $questionTemplateType,
                        'has_results' => $question->results->count() > 0,
                        'stats' => array_keys($questionStatistics)
                    ]);

                    // Add to temporary stats array
                    if (!empty($questionStatistics)) {
                        $tempStats[] = [
                            'question' => $question,
                            'template_type' => $questionTemplateType,
                            'data' => $questionStatistics,
                        ];
                    }
                }

                // Categorize the questions
                $tableCategories = $this->categorizeTableSurveyQuestions($tempStats);

                // Log table categories for debugging
                \Log::debug('Table categories', [
                    'categories_count' => count($tableCategories),
                    'category_keys' => array_keys($tableCategories),
                    'first_category_questions_count' => isset($tableCategories[array_key_first($tableCategories)]) ?
                        count($tableCategories[array_key_first($tableCategories)]['questions'] ?? []) : 0,
                    'categories_structure' => json_encode(array_map(function($cat) {
                        return [
                            'title' => $cat['title'] ?? 'No Title',
                            'questions_count' => count($cat['questions'] ?? []),
                            'has_responses' => $cat['hasResponses'] ?? false,
                        ];
                    }, $tableCategories))
                ]);

                // Add the marker with table categories
                $statistics[] = [
                    'question' => null,
                    'template_type' => 'table',
                    'data' => [
                        'submission_count' => $submissionCount,
                        'table_survey' => true,
                        'table_categories' => $tableCategories,
                    ],
                ];

                // Add individual question stats to main stats array
                $statistics = array_merge($statistics, $tempStats);
            }

            // Original statistics calculation for other templates or in addition to template-specific stats
            foreach ($survey->questions as $question) {
                // Skip template-specific questions that have already been handled above
                if ((str_contains($templateName, 'templates.feedback.smiley') &&
                    in_array($question->question, ['Positive Feedback', 'Negative Feedback'])) ||
                    (str_contains($templateName, 'templates.feedback.target') &&
                    $question->question_template && $question->question_template->type === 'range')) {
                    // Skip these questions as they're already handled in template-specific stats
                    continue;
                }

                $questionStatistics = [];
                // Ensure question_template is loaded and has a type
                if (!$question->relationLoaded('question_template') && $question->question_template_id) {
                    $question->load('question_template');
                }
                // Default to text if no template type is available
                $questionTemplateType = $question->question_template->type ?? 'text';

                switch ($questionTemplateType) {
                    case 'range':
                        // Get only the numeric results for range questions
                        $ratings = $question->results
                            ->where('value_type', 'number')
                            ->pluck('rating_value')
                            ->filter()
                            ->toArray();

                        if (!empty($ratings)) {
                            // Convert to numeric values
                            $ratings = array_map('floatval', $ratings);

                            // Calculate average (mean) rating
                            $questionStatistics['average_rating'] = round(array_sum($ratings) / count($ratings), 2);

                            // Count occurrences of each rating value
                            $questionStatistics['rating_counts'] = array_count_values(array_map('strval', $ratings));

                            // Calculate median rating
                            sort($ratings);
                            $count = count($ratings);
                            $questionStatistics['median_rating'] = $count % 2 === 0
                                ? ($ratings[($count / 2) - 1] + $ratings[$count / 2]) / 2
                                : $ratings[floor($count / 2)];

                            // Add count of unique submissions that answered this question
                            $questionStatistics['submission_count'] = $question->results
                                ->pluck('submission_id')
                                ->unique()
                                ->count();
                        } else {
                            $questionStatistics['average_rating'] = 'No responses';
                            $questionStatistics['median_rating'] = 'No responses';
                            $questionStatistics['rating_counts'] = [];
                            $questionStatistics['submission_count'] = 0;
                        }
                        break;

                    case 'checkboxes':
                    case 'checkbox':
                        // For checkbox questions, only get checkbox type results
                        $checkboxResponses = $question->results
                            ->where('value_type', 'checkbox')
                            ->pluck('rating_value')
                            ->filter()
                            ->toArray();

                        $questionStatistics['option_counts'] = !empty($checkboxResponses)
                            ? array_count_values($checkboxResponses)
                            : [];

                        // Add count of unique submissions that answered this question
                        $questionStatistics['submission_count'] = $question->results
                            ->pluck('submission_id')
                            ->unique()
                            ->count();
                        break;

                    case 'textarea':
                    case 'text':
                        // Only get text type results
                        $textResponses = $question->results
                            ->where('value_type', 'text')
                            ->pluck('rating_value')
                            ->filter()
                            ->toArray();

                        $questionStatistics['response_count'] = count($textResponses);
                        $questionStatistics['responses'] = $textResponses;

                        // Add count of unique submissions that answered this question
                        $questionStatistics['submission_count'] = $question->results
                            ->pluck('submission_id')
                            ->unique()
                            ->count();
                        break;

                    default:
                        // Handle unknown question types gracefully
                        $questionStatistics['message'] = 'Statistics not implemented for this question type.';
                        $questionStatistics['submission_count'] = 0;
                }

                // Build the complete statistics object for this question
                $statistics[] = [
                    'question' => $question,
                    'template_type' => $questionTemplateType,
                    'data' => $questionStatistics,
                ];
            }
        } catch (\Exception $e) {
            // Log the error but return a graceful empty result with more specific error information
            \Log::error('Error calculating survey statistics: ' . $e->getMessage(), [
                'survey_id' => $survey->id,
                'exception' => $e
            ]);

            return [
                [
                    'question' => null,
                    'template_type' => 'error',
                    'data' => [
                        'message' => 'An error occurred while calculating statistics: ' . $e->getMessage(),
                        'error_type' => get_class($e),
                        'survey_id' => $survey->id
                    ]
                ]
            ];
        }

        return $statistics;
    }

    /**
     * Calculate statistics for target template surveys
     *
     * This method processes target survey responses and calculates statistics
     * for each segment in the target diagram.
     *
     * @param Feedback $survey The target survey to calculate statistics for
     * @return array An array of statistics data for each segment
     */
    protected function calculateTargetStatistics(Feedback $survey): array
    {
        $segmentStatisticsData = [];

        // Use the already loaded questions and sort them in code
        $questions = $survey->questions
            ->sortBy('order')
            ->sortBy('id')
            ->values();

        // Process each question (segment)
        foreach ($questions as $index => $question) {
            // Get ratings for this segment/question (only numeric values)
            $ratings = $question->results
                ->where('value_type', 'number')
                ->pluck('rating_value')
                ->filter()
                ->toArray();

            // Convert to numeric values
            $ratings = array_map('floatval', $ratings);

            if (!empty($ratings)) {
                $averageRating = round(array_sum($ratings) / count($ratings), 2);
                $ratingCounts = array_count_values(array_map('strval', $ratings));

                // Count unique submissions for this segment question
                $submissionCount = $question->results
                    ->pluck('submission_id')
                    ->unique()
                    ->count();
            } else {
                $averageRating = 'No responses';
                $ratingCounts = [];
                $submissionCount = 0;
            }

            $segmentStatisticsData[] = [
                'segment_index' => $index,
                'statement' => $question->question,
                'average_rating' => $averageRating,
                'response_count' => count($ratings),
                'submission_count' => $submissionCount,
                'rating_counts' => $ratingCounts,
            ];
        }

        return $segmentStatisticsData;
    }

    /**
     * Categorize questions from a table survey into predefined categories.
     *
     * @param array $statistics The statistics data from calculateStatisticsForSurvey
     * @return array An array of categories with their questions
     */
    public function categorizeTableSurveyQuestions(array $statistics): array
    {
        // Set to true for verbose logging of categorization decisions
        $verboseLogging = config('app.debug', false);

        // Define categories structure with the correct German category names
        $tableCategories = [
            'behavior' => [
                'title' => 'Verhalten des Lehrers',
                'questions' => [],
                'hasResponses' => false,
            ],
            'statements' => [
                'title' => 'Bewerten Sie folgende Aussagen',
                'questions' => [],
                'hasResponses' => false,
            ],
            'quality' => [
                'title' => 'Wie ist der Unterricht?',
                'questions' => [],
                'hasResponses' => false,
            ],
            'claims' => [
                'title' => 'Bewerten Sie folgende Behauptungen',
                'questions' => [],
                'hasResponses' => false,
            ],
            'feedback' => [
                'title' => 'Offenes Feedback',
                'questions' => [],
                'hasResponses' => false,
            ],
        ];

        \Log::debug('Starting table survey categorization', [
            'stats_count' => count($statistics)
        ]);

        // Enable extra debug logging temporarily
        \Log::debug('Looking for potential statements category questions', [
            'questions' => collect($statistics)
                ->filter(function($stat) {
                    return isset($stat['question']) && isset($stat['question']->question);
                })
                ->map(function($stat) {
                    return [
                        'id' => $stat['question']->id ?? 'unknown',
                        'text' => $stat['question']->question ?? 'unknown',
                        'has_responses' => isset($stat['data']['submission_count']) && $stat['data']['submission_count'] > 0
                    ];
                })
                ->values()
                ->toArray()
        ]);

        $uncategorizedQuestions = [];
        $categoryPrefixes = [
            'behavior' => [
                // Teacher behavior related prefixes
                '... hält',
                '... ist motiviert',
                '... erklärt',
                '... spricht',
                '... reagiert',
                '... ist fachlich',
                '... ist',
                '... wirkt',
                '... fördert',
                '... unterrichtet',
                '... zeigt',
                '... freundlich',
                '... energisch',
                '... tatkräftig',
                '... aufgeschlossen',
                '... ungeduldig',
                '... sicher',
                'Der Lehrer achtet auf Ruhe',
            ],
            'statements' => [
                // Statement evaluation prefixes
                'Ich lerne',
                'Die Lehrkraft hat',
                'Die Lehrkraft ist',
                'Die Lehrkraft zeigt',
                'Die Lehrkraft sorgt',
                'Die Notengebung ist',
                'Ich konnte',
                'Der Unterricht wird',
                'Die Fragen und Beiträge',
                '... bevorzugt',
                '... nimmt',
                '... ermutigt',
                '... entscheidet',
                '... gesteht',
            ],
            'quality' => [
                // Teaching quality related prefixes
                'Der Unterricht',
                'Die Unterrichtsgestaltung',
                'Die Unterrichtsinhalte',
                'Die Lernatmosphäre',
                'Das Unterrichtstempo',
                'Das Lernklima',
                'Der Lehrer redet',
                'Der Lehrer schweift',
                'Die Sprache des Lehrers',
                'Die Ziele des Unterrichts',
                'Unterrichtsmaterialien',
                'Der Stoff wird',
            ],
            'claims' => [
                // Claim evaluation prefixes
                'Die Themen der Schulaufgaben',
                'Der Schwierigkeitsgrad',
                'Die Bewertungen sind',
                'Tests und Schulaufgaben',
                'Die Leistungsanforderungen',
                'Die Beurteilung',
            ],
            'feedback' => [
                // Open feedback prefixes
                'Was gefällt dir',
                'Was gefällt dir nicht',
                'Was würdest du',
                'Das hat mir besonders',
                'Das hat mir nicht',
                'Verbesserungsvorschläge',
                'Feedback',
                'Anmerkungen',
                'Kommentare',
            ],
        ];

        // Loop through statistics and categorize questions
        foreach ($statistics as $stat) {
            if (!isset($stat['question']) || !isset($stat['question']->question)) {
                \Log::debug('Skipping stat without question data', [
                    'stat_keys' => array_keys($stat)
                ]);
                continue;
            }

            $question = $stat['question']->question ?? '';
            $questionId = $stat['question']->id ?? 'unknown';
            $categoryAssigned = false;

            // Check if this question has responses
            $hasResponses = false;
            if (isset($stat['data']['submission_count']) && $stat['data']['submission_count'] > 0) {
                $hasResponses = true;
            }

            // Try to categorize the question based on prefixes
            foreach ($categoryPrefixes as $category => $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (Str::startsWith($question, $prefix)) {
                        $tableCategories[$category]['questions'][] = $stat;
                        if ($hasResponses) {
                            $tableCategories[$category]['hasResponses'] = true;
                        }
                        $categoryAssigned = true;
                        if ($verboseLogging) {
                            \Log::debug("Assigned question to category '$category'", [
                                'question' => $question,
                                'id' => $questionId,
                                'matched_prefix' => $prefix
                            ]);
                        }
                        break 2; // Break out of both loops
                    }
                }
            }

            // Special case: If not assigned and starts with '...' assign to behavior
            if (!$categoryAssigned && Str::startsWith($question, '...')) {
                $tableCategories['behavior']['questions'][] = $stat;
                if ($hasResponses) {
                    $tableCategories['behavior']['hasResponses'] = true;
                }
                $categoryAssigned = true;
                if ($verboseLogging) {
                    \Log::debug("Assigned '...' question to default 'behavior' category", [
                        'question' => $question,
                        'id' => $questionId
                    ]);
                }
            }

            // If still not categorized, add to uncategorized list and log
            if (!$categoryAssigned) {
                $uncategorizedQuestions[] = [
                    'id' => $questionId,
                    'text' => $question
                ];
                // As a fallback, put uncategorized questions in feedback
                $tableCategories['feedback']['questions'][] = $stat;
                if ($hasResponses) {
                    $tableCategories['feedback']['hasResponses'] = true;
                }
                \Log::warning("Question not categorized, added to feedback category", [
                    'question' => $question,
                    'id' => $questionId
                ]);
            }
        }

        // Log uncategorized questions
        if (!empty($uncategorizedQuestions)) {
            \Log::warning('Uncategorized questions found', [
                'count' => count($uncategorizedQuestions),
                'questions' => $uncategorizedQuestions
            ]);
        }

        // Collect category summary for logging
        $categorySummary = [];
        foreach ($tableCategories as $key => $category) {
            $categorySummary[$key] = [
                'title' => $category['title'],
                'question_count' => count($category['questions']),
                'has_responses' => $category['hasResponses']
            ];
        }

        if ($verboseLogging) {
            \Log::debug('Category summary before filtering empty categories', [
                'categories' => $categorySummary
            ]);
        }

        // Remove empty categories or ones without responses
        foreach ($tableCategories as $key => $category) {
            if (empty($category['questions'])) {
                \Log::debug("Removing empty category: $key");
                unset($tableCategories[$key]);
            }
        }

        if ($verboseLogging) {
            \Log::debug('Table survey categorization complete', [
                'final_category_count' => count($tableCategories),
                'categories' => array_keys($tableCategories)
            ]);
        }

        return $tableCategories;
    }

    /**
     * Calculate statistics for a single question based on its type.
     *
     * @param \App\Models\Question $question The question to calculate statistics for
     * @param string $questionTemplateType The template type of the question
     * @return array An array of statistics data for the question
     */
    protected function calculateQuestionStatistics($question, string $questionTemplateType): array
    {
        $questionStatistics = [];

        switch ($questionTemplateType) {
            case 'range':
                // Get only the numeric results for range questions
                $ratings = $question->results
                    ->where('value_type', 'number')
                    ->pluck('rating_value')
                    ->filter()
                    ->toArray();

                if (!empty($ratings)) {
                    // Convert to numeric values
                    $ratings = array_map('floatval', $ratings);

                    // Calculate average (mean) rating
                    $questionStatistics['average_rating'] = round(array_sum($ratings) / count($ratings), 2);

                    // Count occurrences of each rating value
                    $questionStatistics['rating_counts'] = array_count_values(array_map('strval', $ratings));

                    // Calculate median rating
                    sort($ratings);
                    $count = count($ratings);
                    $questionStatistics['median_rating'] = $count % 2 === 0
                        ? ($ratings[($count / 2) - 1] + $ratings[$count / 2]) / 2
                        : $ratings[floor($count / 2)];

                    // Add count of unique submissions that answered this question
                    $questionStatistics['submission_count'] = $question->results
                        ->pluck('submission_id')
                        ->unique()
                        ->count();
                } else {
                    $questionStatistics['average_rating'] = 'No responses';
                    $questionStatistics['median_rating'] = 'No responses';
                    $questionStatistics['rating_counts'] = [];
                    $questionStatistics['submission_count'] = 0;
                }
                break;

            case 'checkboxes':
            case 'checkbox':
                // For checkbox questions, only get checkbox type results
                $checkboxResults = $question->results
                    ->where('value_type', 'checkbox')
                    ->pluck('rating_value')
                    ->filter()
                    ->toArray();

                if (!empty($checkboxResults)) {
                    // Count occurrences of each option
                    $questionStatistics['option_counts'] = array_count_values($checkboxResults);

                    // Count submissions - each checkbox produces multiple results for a single submission,
                    // so we need to count unique submission_ids
                    $questionStatistics['submission_count'] = $question->results
                        ->pluck('submission_id')
                        ->unique()
                        ->count();
                } else {
                    $questionStatistics['option_counts'] = [];
                    $questionStatistics['submission_count'] = 0;
                }
                break;

            case 'textarea':
            case 'text':
                // Get text responses
                $textResponses = $question->results
                    ->where('value_type', 'text')
                    ->pluck('rating_value')
                    ->filter()
                    ->toArray();

                $questionStatistics['responses'] = $textResponses;
                $questionStatistics['response_count'] = count($textResponses);

                // Count unique submissions
                $questionStatistics['submission_count'] = $question->results
                    ->pluck('submission_id')
                    ->unique()
                    ->count();
                break;

            default:
                // For other or unknown question types, just include submission count
                $questionStatistics['submission_count'] = $question->results
                    ->pluck('submission_id')
                    ->unique()
                    ->count();

                // Set a message for unknown question types
                $questionStatistics['message'] = 'No statistics available for this question type.';
                break;
        }

        return $questionStatistics;
    }
}