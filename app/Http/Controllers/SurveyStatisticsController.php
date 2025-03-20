<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Services\SurveyService;
use App\ViewModels\SurveyStatisticsViewModel;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

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

        // Eager load all necessary relationships for statistics calculation
        $survey->load(['feedback_template', 'questions.question_template', 'questions.results']);

        // Check the template type for specialized handling
        $templateName = $survey->feedback_template->name ?? '';
        $isTableSurvey = str_contains($templateName, 'templates.feedback.table');
        $isSmileyTemplate = str_contains($templateName, 'templates.feedback.smiley');
        $isTargetTemplate = str_contains($templateName, 'templates.feedback.target');

        // Calculate statistics using the service
        $statisticsData = $this->surveyService->calculateStatisticsForSurvey($survey);

        // Create a view model to handle data processing
        $viewModel = new SurveyStatisticsViewModel($survey, $statisticsData);

        // Add debug logging
        Log::debug('Statistics data being passed to view', [
            'survey_id' => $survey->id,
            'stats_count' => count($statisticsData),
            'template_name' => $templateName
        ]);

        // Pass view model data to the view
        return view('surveys.statistics', $viewModel->toArray());
    }
}
