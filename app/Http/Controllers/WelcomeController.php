<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Services\SurveyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class WelcomeController extends Controller
{
    public function __construct(
        protected SurveyService $surveyService
    ) {}

    /**
     * Show the welcome page with survey access form
     */
    public function index(): View
    {
        return view('welcome');
    }

    /**
     * Access a survey through QR code scanning
     */
    public function scanQrAccess(Request $request): RedirectResponse
    {
        // Get the token from the URL query parameter
        $token = $request->query('token');

        Log::info("QR Code scan request received", [
            'token_exists' => !empty($token),
            'token_length' => !empty($token) ? strlen($token) : 0,
            'request_url' => $request->fullUrl(),
            'user_agent' => $request->userAgent()
        ]);

        if (empty($token)) {
            Log::warning("QR code scan missing token parameter");
            return redirect()->route('welcome')
                ->with('error', __('surveys.invalid_access_key'));
        }

        // Check if the token is valid (corresponds to an existing survey)
        $survey = Feedback::where('accesskey', $token)->first();

        if (!$survey) {
            Log::warning("Invalid access key used via QR code", [
                'accesskey' => $token
            ]);
            return redirect()->route('welcome')
                ->with('error', __('surveys.invalid_access_key'));
        }

        // Optionally, check if the survey can be answered
        if (!$this->surveyService->canBeAnswered($survey)) {
            Log::warning("Attempt to access unavailable survey via QR code", [
                'survey_id' => $survey->id,
                'accesskey' => $token,
                'expire_date' => $survey->expire_date,
                'limit' => $survey->limit,
                'already_answered' => $survey->already_answered
            ]);
            return redirect()->route('welcome')
                ->with('error', __('surveys.survey_not_available'));
        }

        Log::info("Valid survey access via QR code", [
            'survey_id' => $survey->id,
            'accesskey' => $token
        ]);

        // Redirect to welcome page with the token
        // The welcome page will handle form submission with the token
        return redirect()->route('welcome', ['token' => $token]);
    }

    /**
     * Access a survey using an access key
     */
    public function accessSurvey(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        Log::info("Survey access attempt", [
            'accesskey' => $validated['token']
        ]);

        $survey = Feedback::where('accesskey', $validated['token'])->first();

        if (!$survey) {
            Log::warning("Invalid access key used", [
                'accesskey' => $validated['token']
            ]);
            return back()->with('error', __('surveys.invalid_access_key'));
        }

        if (!$this->surveyService->canBeAnswered($survey)) {
            Log::warning("Attempt to access unavailable survey", [
                'survey_id' => $survey->id,
                'accesskey' => $validated['token'],
                'expire_date' => $survey->expire_date,
                'limit' => $survey->limit,
                'already_answered' => $survey->already_answered
            ]);
            return back()->with('error', __('surveys.survey_not_available'));
        }

        Log::info("Survey accessed successfully", [
            'survey_id' => $survey->id,
            'accesskey' => $validated['token']
        ]);

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
}
