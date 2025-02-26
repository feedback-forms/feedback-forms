<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use App\Services\SurveyService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SurveyResponseController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService
    ) {}

    /**
     * Show the survey access form
     */
    public function showAccessForm(): View
    {
        return view('surveys.access');
    }

    /**
     * Access a survey using an access key
     */
    public function accessSurvey(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'accesskey' => 'required|string|size:8',
        ]);

        $survey = Feedback::where('accesskey', $validated['accesskey'])->first();

        if (!$survey) {
            return back()->with('error', __('surveys.invalid_access_key'));
        }

        if (!$this->surveyService->canBeAnswered($survey)) {
            return back()->with('error', __('surveys.survey_not_available'));
        }

        return view('surveys.respond', [
            'survey' => $survey->load(['feedback_template', 'questions.question_template']),
        ]);
    }

    /**
     * Submit survey responses
     */
    public function submitResponses(Request $request, string $accesskey): RedirectResponse
    {
        $survey = Feedback::where('accesskey', $accesskey)->first();

        if (!$survey) {
            return redirect()->route('surveys.access')
                ->with('error', __('surveys.invalid_access_key'));
        }

        if (!$this->surveyService->canBeAnswered($survey)) {
            return redirect()->route('surveys.access')
                ->with('error', __('surveys.survey_not_available'));
        }

        // Validate the responses
        $validated = $request->validate([
            'responses' => 'required|array',
            'responses.*' => 'required|string',
        ]);

        try {
            // Process and store the responses
            $this->surveyService->storeResponses($survey, $validated['responses']);

            return redirect()->route('surveys.thank-you')
                ->with('success', __('surveys.response_submitted'));
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', __('surveys.submission_failed') . ' ' . $e->getMessage());
        }
    }

    /**
     * Show thank you page after survey submission
     */
    public function showThankYou(): View
    {
        return view('surveys.thank-you');
    }
}