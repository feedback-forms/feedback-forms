<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Services\SurveyService;
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

        // Get table categories from statistics data if it's a table survey
        $tableCategories = [];
        $tableItem = collect($statisticsData)->firstWhere('template_type', 'table');

        if ($tableItem && isset($tableItem['data']['table_categories'])) {
            $tableCategories = $tableItem['data']['table_categories'];

            // Ensure we have at least one category that has responses
            $hasResponses = false;
            foreach ($tableCategories as $category) {
                if (!empty($category['questions']) && ($category['hasResponses'] ?? false)) {
                    $hasResponses = true;
                    break;
                }
            }

            if (!$hasResponses) {
                Log::warning('Table survey has no categories with responses', [
                    'survey_id' => $survey->id,
                    'categories' => array_keys($tableCategories)
                ]);
            }
        }

        // Add debug logging to track the statistics data
        Log::debug('Statistics data being passed to view', [
            'survey_id' => $survey->id,
            'stats_count' => count($statisticsData),
            'has_table_item' => (bool)$tableItem,
            'template_types' => collect($statisticsData)->pluck('template_type')->unique()->toArray(),
            'template_name' => $templateName,
            'is_table_survey' => $isTableSurvey
        ]);

        // Log specifically about the table item if applicable
        if ($tableItem) {
            Log::debug('Table item details', [
                'has_table_survey_flag' => isset($tableItem['data']['table_survey']),
                'table_survey_value' => $tableItem['data']['table_survey'] ?? null,
                'has_table_categories' => isset($tableItem['data']['table_categories']),
                'table_categories_count' => is_array($tableItem['data']['table_categories'] ?? null) ?
                    count($tableItem['data']['table_categories']) : 'not an array',
                'category_keys' => is_array($tableItem['data']['table_categories'] ?? null) ?
                    array_keys($tableItem['data']['table_categories']) : []
            ]);
        }

        return view('surveys.statistics', [
            'survey' => $survey,
            'statisticsData' => $statisticsData,
            'isTableSurvey' => $isTableSurvey,
            'isSmileyTemplate' => $isSmileyTemplate,
            'isTargetTemplate' => $isTargetTemplate,
            'tableCategories' => $tableCategories,
            'submissionCount' => $survey->submission_count
        ]);
    }
}
