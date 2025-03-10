<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use App\Services\SurveyService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class SurveyResponseController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService
    ) {}

    /**
     * Submit survey responses
     */
    public function submitResponses(Request $request, string $accesskey): RedirectResponse
    {
        Log::info("Survey submission attempt", [
            'accesskey' => $accesskey,
            'has_responses' => $request->has('responses'),
            'response_type' => gettype($request->input('responses'))
        ]);

        $survey = Feedback::where('accesskey', $accesskey)->first();

        if (!$survey) {
            Log::warning("Invalid access key used for survey submission", [
                'accesskey' => $accesskey
            ]);
            return redirect()->route('welcome')
                ->with('error', __('surveys.invalid_access_key'));
        }

        if (!$this->surveyService->canBeAnswered($survey)) {
            Log::warning("Attempt to submit to unavailable survey", [
                'survey_id' => $survey->id,
                'accesskey' => $accesskey,
                'expire_date' => $survey->expire_date,
                'limit' => $survey->limit,
                'already_answered' => $survey->already_answered
            ]);
            return redirect()->route('welcome')
                ->with('error', __('surveys.survey_not_available'));
        }

        try {
            // Load the survey with its questions to ensure we're working with the correct data
            $survey->load('questions');

            // Check if the response is a JSON string (from template-specific forms)
            $responses = $request->input('responses');
            $success = false;

            if (is_string($responses) && $this->isJson($responses)) {
                // Handle JSON string response
                $jsonData = json_decode($responses, true);
                Log::info("Processing JSON response", [
                    'survey_id' => $survey->id,
                    'json_data' => $jsonData
                ]);

                // Process and store the responses as a JSON object
                $success = $this->surveyService->storeResponses($survey, [$responses]);
            } else {
                // Validate the responses with more flexible validation for array inputs
                $validated = $request->validate([
                    'responses' => 'required|array',
                    'responses.*' => 'required',
                ]);

                // Process and store the responses as an array
                $success = $this->surveyService->storeResponses($survey, $validated['responses']);
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
     */
    private function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Show thank you page after survey submission
     */
    public function showThankYou(): View
    {
        return view('surveys.thank-you');
    }
}