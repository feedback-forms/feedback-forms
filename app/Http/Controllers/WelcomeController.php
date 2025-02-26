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

        // Show the survey response form
        return view('surveys.respond', [
            'survey' => $survey->load(['feedback_template', 'questions.question_template']),
        ]);
    }

    public function index(): View
    {
        return view('welcome');
    }
}
