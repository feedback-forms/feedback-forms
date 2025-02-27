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

        // Load the survey with its template and questions
        $survey->load([
            'feedback_template',
            'questions' => function($query) {
                $query->with('question_template');
            }
        ]);

        // Get the template name from the feedback_template
        $templateName = $survey->feedback_template->name ?? '';

        // Extract the template type from the name (e.g., "templates.feedback.target" -> "target")
        $templateType = '';
        if (preg_match('/templates\.feedback\.(\w+)$/', $templateName, $matches)) {
            $templateType = $matches[1];
        }

        // If we have a valid template type, render that template
        if (in_array($templateType, ['target', 'smiley', 'table', 'checkbox'])) {
            return view("survey_templates.{$templateType}_respond", [
                'survey' => $survey,
                'isStudentView' => true,
            ]);
        }

        // Fallback to the generic response form
        return view('surveys.respond', [
            'survey' => $survey,
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
            // Load the survey with its questions to ensure we're working with the correct data
            $survey->load('questions');

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