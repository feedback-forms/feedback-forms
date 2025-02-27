<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Services\SurveyService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function store(Request $request): View|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate(['token' => 'required|string']);

        // Find the survey by accesskey
        $survey = Feedback::where('accesskey', $validated['token'])->first();

        if (!$survey) {
            return back()->with('error', __('surveys.invalid_access_key'));
        }

        // Check if the survey can be answered
        $surveyService = app(SurveyService::class);
        if (!$surveyService->canBeAnswered($survey)) {
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

    public function index(): View
    {
        return view('welcome');
    }
}
