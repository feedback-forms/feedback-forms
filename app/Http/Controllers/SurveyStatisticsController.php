<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Services\SurveyService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;

class SurveyStatisticsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected SurveyService $surveyService)
    {
        // Auth middleware is applied in the route definition
    }

    /**
     * Show statistics for a specific survey.
     */
    public function show(Feedback $survey): View
    {
        // Authorization: Ensure the logged-in user owns the survey
        if (! Gate::allows('owns-survey', $survey)) {
            abort(403, 'Unauthorized action.');
        }

        $statisticsData = $this->surveyService->calculateStatisticsForSurvey($survey);

        return view('surveys.statistics', [
            'survey' => $survey->load(['feedback_template', 'questions.question_template']),
            'statisticsData' => $statisticsData,
        ]);
    }
}
