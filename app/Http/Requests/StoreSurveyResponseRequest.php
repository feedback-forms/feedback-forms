<?php

namespace App\Http\Requests;

use App\Exceptions\InvalidAccessKeyException;
use App\Exceptions\SurveyNotAvailableException;
use App\Models\Feedback;
use App\Services\SurveyService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class StoreSurveyResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $accesskey = $this->route('accesskey');
        $survey = Feedback::where('accesskey', $accesskey)->first();

        if (!$survey) {
            Log::warning("Invalid access key used for survey submission", [
                'accesskey' => $accesskey
            ]);
            throw new InvalidAccessKeyException();
        }

        try {
            // Get the SurveyService to check if the survey can be answered
            $surveyService = app(SurveyService::class);
            if (!$surveyService->canBeAnswered($survey)) {
                Log::warning("Attempt to submit to unavailable survey", [
                    'survey_id' => $survey->id,
                    'accesskey' => $accesskey,
                    'expire_date' => $survey->expire_date,
                    'limit' => $survey->limit,
                    'submission_count' => $survey->submission_count
                ]);
                throw new SurveyNotAvailableException();
            }
        } catch (SurveyNotAvailableException $e) {
            throw $e;
        }

        // Store the survey in the request for use in the controller
        $this->merge(['survey' => $survey]);

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // First check if this is a JSON string response
        if ($this->has('responses') && is_string($this->input('responses')) && $this->isJson($this->input('responses'))) {
            return [
                'responses' => ['required', 'string', function ($attribute, $value, $fail) {
                    $jsonData = json_decode($value, true);

                    // Validate the JSON structure has expected format
                    if (!is_array($jsonData) || empty($jsonData)) {
                        $fail('The responses JSON must contain valid survey response data.');
                        return;
                    }

                    // Validate JSON content doesn't contain potentially harmful data
                    foreach ($jsonData as $key => $val) {
                        // Check for suspicious patterns
                        if (is_string($val) && $this->containsSuspiciousContent($val)) {
                            $fail("The response contains potentially malicious content.");
                            return;
                        }

                        // For nested arrays, check each item
                        if (is_array($val)) {
                            foreach ($val as $nestedVal) {
                                if (is_string($nestedVal) && $this->containsSuspiciousContent($nestedVal)) {
                                    $fail("The response contains potentially malicious content.");
                                    return;
                                }
                            }
                        }
                    }
                }],
            ];
        }

        // Otherwise, validate as a regular response array
        return [
            'responses' => 'required|array',
            'responses.*' => ['required', function ($attribute, $value, $fail) {
                // Validate string values for potentially harmful content
                if (is_string($value) && $this->containsSuspiciousContent($value)) {
                    $fail("The response contains potentially malicious content.");
                }

                // Check array values (like for checkboxes)
                if (is_array($value)) {
                    foreach ($value as $item) {
                        if (is_string($item) && $this->containsSuspiciousContent($item)) {
                            $fail("The response contains potentially malicious content.");
                            return;
                        }
                    }
                }
            }],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Additional custom validation can be added here
            // For example, validating specific question types based on the survey template

            $survey = $this->get('survey');
            if (!$survey) {
                return;
            }

            // If we're dealing with JSON responses, we'll let the service handle the validation
            if ($this->isJsonResponse()) {
                return;
            }

            // For regular responses, add specific validation for response types
            $responses = $this->input('responses');
            if (!is_array($responses)) {
                return;
            }

            $survey->load(['questions.question_template']);

            foreach ($responses as $questionId => $responseValue) {
                if (!is_numeric($questionId) && $questionId !== 'feedback') {
                    continue;
                }

                if ($questionId === 'feedback') {
                    // Validate general feedback response
                    if (!empty($responseValue) && is_string($responseValue)) {
                        $this->validateTextContent($validator, $responseValue, "responses.feedback");
                    }
                    continue;
                }

                $question = $this->findQuestionInSurvey($survey, $questionId);
                if (!$question) {
                    continue;
                }

                $questionType = $question->question_template->type ?? 'text';

                // Validate based on question type
                switch ($questionType) {
                    case 'range':
                        $this->validateRangeResponse($validator, $questionId, $responseValue);
                        break;

                    case 'checkboxes':
                    case 'checkbox':
                        $this->validateCheckboxResponse($validator, $questionId, $responseValue);
                        break;

                    case 'text':
                    case 'textarea':
                        $this->validateTextResponse($validator, $questionId, $responseValue);
                        break;

                    case 'radio':
                    case 'select':
                        $this->validateOptionResponse($validator, $questionId, $responseValue, $question);
                        break;

                    default:
                        // For any unspecified types, ensure we still validate text content
                        if (is_string($responseValue)) {
                            $this->validateTextContent($validator, $responseValue, "responses.{$questionId}");
                        }
                }
            }
        });
    }

    /**
     * Check if the response is a JSON string
     *
     * @return bool
     */
    protected function isJsonResponse(): bool
    {
        $responses = $this->input('responses');
        return is_string($responses) && $this->isValidJson($responses);
    }

    /**
     * Check if a string is valid JSON
     *
     * @param mixed $string The string to check
     * @return bool True if the string is valid JSON, false otherwise
     */
    protected function isValidJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Validate a range/numeric response
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @param string|int $questionId The question ID
     * @param mixed $value The response value
     * @return void
     */
    protected function validateRangeResponse($validator, $questionId, $value): void
    {
        // Check if value is numeric
        if (!is_numeric($value)) {
            $validator->errors()->add(
                "responses.{$questionId}",
                __('validation.numeric', ['attribute' => "Question {$questionId}"])
            );
            return;
        }

        // Check if the value is within reasonable range (e.g., 1-10 or 0-100)
        $numericValue = (float)$value;
        if ($numericValue < 0 || $numericValue > 100) {
            $validator->errors()->add(
                "responses.{$questionId}",
                __('validation.between.numeric', [
                    'attribute' => "Question {$questionId}",
                    'min' => 0,
                    'max' => 100
                ])
            );
        }
    }

    /**
     * Validate a checkbox response
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @param string|int $questionId The question ID
     * @param mixed $value The response value
     * @return void
     */
    protected function validateCheckboxResponse($validator, $questionId, $value): void
    {
        // If not an array, it's invalid for checkboxes
        if (!is_array($value)) {
            $validator->errors()->add(
                "responses.{$questionId}",
                __('validation.array', ['attribute' => "Question {$questionId}"])
            );
            return;
        }

        // Filter valid options and check if any remain
        $validOptions = array_filter($value, function($option) {
            return (is_string($option) || is_numeric($option)) &&
                   !$this->containsSuspiciousContent((string)$option);
        });

        if (empty($validOptions)) {
            $validator->errors()->add(
                "responses.{$questionId}",
                __('validation.required', ['attribute' => "Question {$questionId}"])
            );
        }

        // Check if there are too many options selected (potential abuse)
        if (count($value) > 50) {
            $validator->errors()->add(
                "responses.{$questionId}",
                "Too many options selected for Question {$questionId}."
            );
        }
    }

    /**
     * Validate a text response
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @param string|int $questionId The question ID
     * @param mixed $value The response value
     * @return void
     */
    protected function validateTextResponse($validator, $questionId, $value): void
    {
        if (!is_string($value) && !is_numeric($value)) {
            $validator->errors()->add(
                "responses.{$questionId}",
                __('validation.string', ['attribute' => "Question {$questionId}"])
            );
            return;
        }

        $this->validateTextContent($validator, (string)$value, "responses.{$questionId}");
    }

    /**
     * Validate an option response (radio/select)
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @param string|int $questionId The question ID
     * @param mixed $value The response value
     * @param \App\Models\Question $question The question model
     * @return void
     */
    protected function validateOptionResponse($validator, $questionId, $value, $question): void
    {
        if (!(is_string($value) || is_numeric($value))) {
            $validator->errors()->add(
                "responses.{$questionId}",
                __('validation.string', ['attribute' => "Question {$questionId}"])
            );
            return;
        }

        $this->validateTextContent($validator, (string)$value, "responses.{$questionId}");
    }

    /**
     * Validate text content for potential security issues
     *
     * @param \Illuminate\Validation\Validator $validator The validator instance
     * @param string $text The text to validate
     * @param string $attribute The attribute name for error messages
     * @return void
     */
    protected function validateTextContent($validator, string $text, string $attribute): void
    {
        // Check length (prevent DoS attacks with extremely long inputs)
        if (strlen($text) > 10000) {
            $validator->errors()->add(
                $attribute,
                __('validation.max.string', ['attribute' => $attribute, 'max' => 10000])
            );
        }

        // Check for potentially malicious content
        if ($this->containsSuspiciousContent($text)) {
            $validator->errors()->add(
                $attribute,
                "The response contains potentially malicious content."
            );
        }
    }

    /**
     * Check if a string contains potentially suspicious content
     *
     * @param string $content The content to check
     * @return bool True if suspicious content is found
     */
    protected function containsSuspiciousContent(string $content): bool
    {
        // Check for common script injection patterns
        $suspiciousPatterns = [
            '/<script\b[^>]*>/i',                    // Script tags
            '/javascript:/i',                        // JavaScript protocol
            '/on\w+\s*=\s*["\'][^"\']*["\']/i',    // Event handlers (onclick, onload, etc.)
            '/eval\s*\(/i',                          // eval()
            '/document\.(location|cookie|write)/i',  // Document manipulation
            '/<iframe\b[^>]*>/i',                    // iframes
            '/<object\b[^>]*>/i',                    // object tags
            '/<embed\b[^>]*>/i',                     // embed tags
            '/\bdata:(?:text|image)\/[a-z]*;base64/i' // Data URIs
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find a question in the survey by ID or index
     *
     * @param Feedback $survey The survey to search in
     * @param int|string $questionId The question ID or index
     * @return mixed The question or null if not found
     */
    protected function findQuestionInSurvey(Feedback $survey, $questionId)
    {
        $questions = $survey->questions;
        $question = $questions->firstWhere('id', $questionId);

        if (!$question) {
            $index = (int)$questionId;
            if ($index >= 0 && $index < $questions->count()) {
                $question = $questions[$index];
            }
        }

        return $question;
    }
}