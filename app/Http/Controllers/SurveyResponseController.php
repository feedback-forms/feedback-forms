<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidAccessKeyException;
use App\Exceptions\SurveyNotAvailableException;
use App\Http\Requests\StoreSurveyResponseRequest;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use App\Services\SurveyService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SurveyResponseController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService
    ) {}

    /**
     * Submit survey responses
     *
     * @param Request $request
     * @param string $accesskey
     * @return RedirectResponse
     * @throws InvalidAccessKeyException
     * @throws SurveyNotAvailableException
     */
    public function submitResponses(StoreSurveyResponseRequest $request, string $accesskey): RedirectResponse
    {
        Log::info("Survey submission attempt", [
            'accesskey' => $accesskey,
            'has_responses' => $request->has('responses'),
            'response_type' => gettype($request->input('responses'))
        ]);

        // Survey is already validated and accessible through the request
        $survey = $request->get('survey');

        try {
            // Load the survey with its questions to ensure we're working with the correct data
            $survey->load(['questions' => function($query) {
                $query->orderBy('order')->with('question_template');
            }]);

            // Check if the response is a JSON string (from template-specific forms)
            $responses = $request->input('responses');
            $success = false;

            if (is_string($responses) && $this->isValidJson($responses)) {
                // Handle JSON string response
                $jsonData = json_decode($responses, true);
                $success = $this->storeJsonResponses($survey, $jsonData);
            } else {
                // The response data is already validated by the form request
                $success = $this->surveyService->storeResponses($survey, $request->validated()['responses']);
            }

            if (!$success) {
                throw new \Exception('Failed to store responses');
            }

            // Ensure the counter is updated
            $survey->refresh();

            Log::info("Survey submission successful", [
                'survey_id' => $survey->id,
                'accesskey' => $accesskey
            ]);

            return redirect()->route('surveys.thank-you')
                ->with('success', __('surveys.response_submitted'));
        } catch (ValidationException $e) {
            Log::error('Survey submission validation failed: ' . $e->getMessage(), [
                'survey_id' => $survey->id,
                'accesskey' => $accesskey,
                'exception' => $e
            ]);

            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Survey submission failed: ' . $e->getMessage(), [
                'survey_id' => $survey->id,
                'accesskey' => $accesskey,
                'exception' => $e
            ]);

            return back()
                ->withInput()
                ->with('error', __('surveys.submission_failed'));
        }
    }

    /**
     * Check if a string is valid JSON
     *
     * @param mixed $string The string to check
     * @return bool True if the string is valid JSON, false otherwise
     */
    private function isValidJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Store survey responses from JSON data
     *
     * @param Feedback $survey The survey to store responses for
     * @param array $jsonData The JSON data to store
     * @return bool True if responses were stored successfully, false otherwise
     */
    private function storeJsonResponses(Feedback $survey, array $jsonData): bool
    {
        if (empty($jsonData)) {
            Log::warning("Empty JSON data received for survey submission", [
                'survey_id' => $survey->id,
                'accesskey' => $survey->accesskey
            ]);
            return false;
        }

        Log::info("Processing JSON response", [
            'survey_id' => $survey->id,
            'json_data' => $jsonData
        ]);

        try {
            // Process and store the responses as structured data, not as JSON
            return $this->surveyService->storeResponses($survey, ['json_data' => $jsonData]);
        } catch (\Exception $e) {
            Log::error('Error storing survey responses from JSON: ' . $e->getMessage(), [
                'survey_id' => $survey->id,
                'exception' => $e,
                'json_data' => $jsonData
            ]);
            return false;
        }
    }

    /**
     * Show thank you page after survey submission
     *
     * @return View
     */
    public function showThankYou(): View
    {
        return view('surveys.thank-you');
    }
}