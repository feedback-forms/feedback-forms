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

        // Eager load all necessary relationships for statistics calculation
        $survey->load(['feedback_template', 'questions.question_template', 'questions.results']);

        $statisticsData = $this->surveyService->calculateStatisticsForSurvey($survey);

        // Add debug logging to track the statistics data
        \Log::debug('Statistics data being passed to view', [
            'survey_id' => $survey->id,
            'stats_count' => count($statisticsData),
            'has_table_item' => collect($statisticsData)->contains(function ($stat) {
                return $stat['template_type'] === 'table';
            }),
            'template_types' => collect($statisticsData)->pluck('template_type')->unique()->toArray()
        ]);

        // Log specifically about the table item
        $tableItem = collect($statisticsData)->firstWhere('template_type', 'table');
        if ($tableItem) {
            \Log::debug('Table item details', [
                'has_table_survey_flag' => isset($tableItem['data']['table_survey']),
                'table_survey_value' => $tableItem['data']['table_survey'] ?? null,
                'has_table_categories' => isset($tableItem['data']['table_categories']),
                'table_categories_count' => is_array($tableItem['data']['table_categories'] ?? null) ?
                    count($tableItem['data']['table_categories']) : 'not an array',
                'first_table_category' => is_array($tableItem['data']['table_categories'] ?? null) &&
                    !empty($tableItem['data']['table_categories']) ?
                    array_key_first($tableItem['data']['table_categories']) : 'no categories'
            ]);
        }

        return view('surveys.statistics', [
            'survey' => $survey,
            'statisticsData' => $statisticsData,
        ]);
    }
}
