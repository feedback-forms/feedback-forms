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
                'responses' => 'required|string',
            ];
        }

        // Otherwise, validate as a regular response array
        return [
            'responses' => 'required|array',
            'responses.*' => 'required',
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
                    continue;
                }

                $question = $this->findQuestionInSurvey($survey, $questionId);
                if (!$question) {
                    continue;
                }

                $questionType = $question->question_template->type ?? 'text';

                // Validate number responses
                if ($questionType === 'range' && !is_numeric($responseValue)) {
                    $validator->errors()->add(
                        "responses.{$questionId}",
                        __('validation.numeric', ['attribute' => "Question {$questionId}"])
                    );
                }

                // Validate checkbox responses
                if (in_array($questionType, ['checkboxes', 'checkbox']) && is_array($responseValue)) {
                    $validOptions = array_filter($responseValue, function($option) {
                        return is_string($option) || is_numeric($option);
                    });

                    if (empty($validOptions)) {
                        $validator->errors()->add(
                            "responses.{$questionId}",
                            __('validation.required', ['attribute' => "Question {$questionId}"])
                        );
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