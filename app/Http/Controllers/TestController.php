<?php

namespace App\Http\Controllers;

use App\Services\SurveyAggregationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * Test the SurveyAggregationService
     *
     * @param Request $request
     * @param SurveyAggregationService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function testAggregation(Request $request, SurveyAggregationService $service)
    {
        $category = $request->input('category', 'class');

        // Get all available values for the category
        $values = $service->getCategoryValues($category);

        Log::debug("Available values for {$category}", ['values' => $values]);

        $results = [];

        // For each value, try to aggregate data
        foreach ($values as $value) {
            $aggregated = $service->aggregateByCategory($category, $value);
            $results[$value] = [
                'threshold_met' => $aggregated['threshold_met'],
                'submission_count' => $aggregated['submission_count'] ?? 0,
                'min_threshold' => $aggregated['min_threshold'] ?? 0,
                'has_results' => isset($aggregated['results']),
                'result_types' => isset($aggregated['results']) ? array_keys($aggregated['results']) : [],
            ];
        }

        return response()->json([
            'category' => $category,
            'values' => $values,
            'results' => $results,
            'thresholds' => $service->getAllThresholds(),
        ]);
    }

    /**
     * Display information about question categories for debugging purposes
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function questionCategories(Request $request)
    {
        // Get category and value parameters
        $category = $request->query('category', 'school_year');
        $value = $request->query('value', '2023/24');

        // Find all feedback forms matching the category and value
        $feedbacks = Feedback::where($category, $value)
            ->where(function($query) {
                $query->where('status', 'running')
                      ->orWhere('status', 'expired');
            })
            ->get();

        $feedbackIds = $feedbacks->pluck('id')->toArray();

        // Get submission count
        $submissionCount = DB::table('results')
            ->join('questions', 'results.question_id', '=', 'questions.id')
            ->whereIn('questions.feedback_id', $feedbackIds)
            ->distinct('results.submission_id')
            ->count('results.submission_id');

        // Get questions for these feedbacks
        $questions = Question::whereIn('feedback_id', $feedbackIds)
            ->with(['question_template', 'feedback'])
            ->get();

        // Count questions with and without categories
        $questionsWithCategory = $questions->whereNotNull('category')->count();
        $questionsWithoutCategory = $questions->whereNull('category')->count();

        // Get unique categories
        $uniqueCategories = $questions->pluck('category')->filter()->unique()->values()->toArray();

        // Get questions and results by category
        $categoryCounts = [];
        foreach ($uniqueCategories as $cat) {
            // Filter questions by this category
            $categoryQuestions = $questions->where('category', $cat);

            // Get count of results for these questions
            $questionIds = $categoryQuestions->pluck('id')->toArray();
            $resultCount = Result::whereIn('question_id', $questionIds)->count();

            // Group questions by type
            $typeCount = [];
            foreach ($categoryQuestions as $q) {
                $type = $q->question_template->type;
                if (!isset($typeCount[$type])) {
                    $typeCount[$type] = 0;
                }
                $typeCount[$type]++;
            }

            $categoryCounts[$cat] = [
                'question_count' => $categoryQuestions->count(),
                'result_count' => $resultCount,
                'question_types' => $typeCount
            ];
        }

        // For questions without category, group by type
        $noCategory = [];
        $noCategoryQuestions = $questions->whereNull('category');
        if ($noCategoryQuestions->count() > 0) {
            $typeCount = [];
            foreach ($noCategoryQuestions as $q) {
                $type = $q->question_template->type;
                if (!isset($typeCount[$type])) {
                    $typeCount[$type] = 0;
                }
                $typeCount[$type]++;
            }

            $questionIds = $noCategoryQuestions->pluck('id')->toArray();
            $resultCount = Result::whereIn('question_id', $questionIds)->count();

            $noCategory = [
                'question_count' => $noCategoryQuestions->count(),
                'result_count' => $resultCount,
                'question_types' => $typeCount
            ];
        }

        // Return diagnostic info
        return response()->json([
            'diagnostics' => [
                'category' => $category,
                'value' => $value,
                'feedbacks_count' => count($feedbackIds),
                'feedback_ids' => $feedbackIds,
                'submission_count' => $submissionCount,
                'total_questions' => $questions->count(),
                'questions_with_category' => $questionsWithCategory,
                'questions_without_category' => $questionsWithoutCategory,
                'unique_categories' => $uniqueCategories,
                'category_details' => $categoryCounts,
                'no_category_details' => $noCategory
            ]
        ]);
    }

    /**
     * Test how the SurveyAggregationService assigns questions to categories for the tabbed interface
     *
     * @param Request $request
     * @param SurveyAggregationService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function testTabCategories(Request $request, SurveyAggregationService $service)
    {
        // Get category and value parameters
        $category = $request->query('category', 'class');
        $value = $request->query('value', '5a');

        // Direct access to the service method that calculates aggregated results
        $feedbacks = Feedback::where($category, $value)
            ->where(function($query) {
                $query->where('status', 'running')
                      ->orWhere('status', 'expired');
            })
            ->get();

        // Extract the questions for inspection before aggregation
        $questions = Question::whereIn('feedback_id', $feedbacks->pluck('id')->toArray())
            ->with(['question_template', 'feedback_template'])
            ->get();

        $originalQuestions = $questions->map(function($q) {
            return [
                'id' => $q->id,
                'question' => $q->question,
                'category' => $q->category,
                'has_feedback_template' => $q->feedback_template ? true : false,
                'feedback_template_category' => $q->feedback_template ? $q->feedback_template->category : null,
                'question_type' => $q->question_template ? $q->question_template->type : null
            ];
        });

        // Call the service to get aggregated results
        $aggregatedData = $service->aggregateByCategory($category, $value);

        // Return diagnostic info
        return response()->json([
            'parameters' => [
                'category' => $category,
                'value' => $value,
            ],
            'questions' => [
                'total' => $questions->count(),
                'with_category' => $questions->whereNotNull('category')->count(),
                'unique_categories' => $questions->pluck('category')->filter()->unique()->values(),
                'sample' => $originalQuestions->take(15)->toArray()
            ],
            'aggregated_data' => [
                'threshold_met' => $aggregatedData['threshold_met'],
                'has_categories' => $aggregatedData['has_categories'] ?? false,
                'category_count' => isset($aggregatedData['categories']) ? count($aggregatedData['categories']) : 0,
                'categories' => isset($aggregatedData['categories']) ? array_keys($aggregatedData['categories']) : [],
                'active_tab' => $aggregatedData['active_tab'] ?? null
            ],
            'results_structure' => [
                'has_direct_results' => isset($aggregatedData['results']) && !empty($aggregatedData['results']),
                'direct_results_types' => isset($aggregatedData['results']) ? array_keys($aggregatedData['results']) : []
            ]
        ]);
    }
}