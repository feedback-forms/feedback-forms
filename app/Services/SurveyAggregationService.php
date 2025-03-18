<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for aggregating survey results while maintaining anonymity
 *
 * This service calculates aggregated statistics for surveys grouped by various
 * organizational categories (class, department, subject, school year) while
 * enforcing minimum threshold requirements to ensure anonymity.
 */
class SurveyAggregationService
{
    /**
     * Category threshold requirements
     * Defines the minimum number of survey responses needed for each category
     * to ensure anonymity when aggregating data
     *
     * @var array<string, int>
     */
    private const THRESHOLDS = [
        'class' => 3,
        'department' => 2,
        'subject' => 3,
        'school_year' => 2
    ];

    /**
     * Get all available values for a specific category
     *
     * @param string $category Category to get values for (class, department, subject, school_year)
     * @return array Array of distinct values for the category
     */
    public function getCategoryValues(string $category): array
    {
        // Validate category is supported
        if (!in_array($category, array_keys(self::THRESHOLDS))) {
            Log::warning("Attempted to get values for unsupported category: {$category}");
            return [];
        }

        // Only include active or completed surveys with non-null values for the category
        return Feedback::where(function($query) {
                $query->where('status', 'running')
                      ->orWhere('status', 'expired');
            })
            ->whereNotNull($category)
            ->distinct($category)
            ->pluck($category)
            ->toArray();
    }

    /**
     * Aggregate results for a specific category and value
     *
     * @param string $category Category to aggregate by (class, department, subject, school_year)
     * @param string $value The specific value to filter on
     * @param int|null $threshold Optional custom threshold (defaults to preset thresholds)
     * @return array Array of aggregated data including threshold status and results if available
     */
    public function aggregateByCategory(string $category, string $value, ?int $threshold = null): array
    {
        // Ensure the category is valid
        if (!in_array($category, array_keys(self::THRESHOLDS))) {
            Log::warning("Attempted to aggregate by unsupported category: {$category}");
            return [
                'threshold_met' => false,
                'error' => 'Unsupported category',
                'category' => $category,
                'value' => $value
            ];
        }

        // Use either the provided threshold or the default for this category
        $minimumThreshold = $threshold ?? self::THRESHOLDS[$category];

        try {
            // Find all feedback forms matching the category and value
            $feedbacks = Feedback::where($category, $value)
                ->where(function($query) {
                    $query->where('status', 'running')
                          ->orWhere('status', 'expired');
                })
                ->get();

            Log::debug("Found feedback forms for category: {$category}, value: {$value}", [
                'feedback_count' => $feedbacks->count(),
                'feedback_ids' => $feedbacks->pluck('id')->toArray()
            ]);

            // Calculate the count of unique submissions across all matching feedbacks
            $submissionCount = $this->getUniqueSubmissionCount($feedbacks);

            Log::debug("Submission count: {$submissionCount}");

            // If the submission count doesn't meet the threshold, return insufficient data response
            if ($submissionCount < $minimumThreshold) {
                return [
                    'threshold_met' => false,
                    'submission_count' => $submissionCount,
                    'min_threshold' => $minimumThreshold,
                    'category' => $category,
                    'value' => $value
                ];
            }

            // Aggregate the results
            $aggregatedResults = $this->calculateAggregatedResults($feedbacks);

            // Log info about the aggregated results
            if (isset($aggregatedResults['categories'])) {
                Log::debug("Aggregated results structure", [
                    'category_count' => count($aggregatedResults['categories']),
                    'categories' => array_keys($aggregatedResults['categories']),
                    'has_results' => !empty($aggregatedResults['categories'])
                ]);

                foreach ($aggregatedResults['categories'] as $categoryName => $categoryData) {
                    Log::debug("Category: {$categoryName} details", [
                        'has_range' => isset($categoryData['results']['range']),
                        'has_checkboxes' => isset($categoryData['results']['checkboxes']),
                        'range_count' => isset($categoryData['results']['range']) ? count($categoryData['results']['range']) : 0,
                        'checkbox_count' => isset($categoryData['results']['checkboxes']) ? count($categoryData['results']['checkboxes']) : 0
                    ]);
                }
            } else {
                Log::debug("No categories found in aggregated results", [
                    'structure_keys' => array_keys($aggregatedResults)
                ]);
            }

            // Check if we have range or checkbox data in the results
            $hasRangeData = false;
            $hasCheckboxData = false;
            $rangeCount = 0;
            $checkboxesCount = 0;

            // Check in categories first
            if (isset($aggregatedResults['categories'])) {
                foreach ($aggregatedResults['categories'] as $categoryData) {
                    if (isset($categoryData['results']['range']) && !empty($categoryData['results']['range'])) {
                        $hasRangeData = true;
                        $rangeCount += count($categoryData['results']['range']);
                    }
                    if (isset($categoryData['results']['checkboxes']) && !empty($categoryData['results']['checkboxes'])) {
                        $hasCheckboxData = true;
                        $checkboxesCount += count($categoryData['results']['checkboxes']);
                    }
                }
            }

            // Also check in the root results (backward compatibility)
            if (isset($aggregatedResults['range']) && !empty($aggregatedResults['range'])) {
                $hasRangeData = true;
                $rangeCount += count($aggregatedResults['range']);
            }
            if (isset($aggregatedResults['checkboxes']) && !empty($aggregatedResults['checkboxes'])) {
                $hasCheckboxData = true;
                $checkboxesCount += count($aggregatedResults['checkboxes']);
            }

            // Determine if we have any categories
            $hasCategories = isset($aggregatedResults['categories']) && !empty($aggregatedResults['categories']);

            // Set the active tab if we have categories
            $activeTab = null;
            if ($hasCategories) {
                $activeTab = array_key_first($aggregatedResults['categories']);
            }

            Log::debug("Aggregated data loaded", [
                'threshold_met' => true,
                'submission_count' => $submissionCount,
                'min_threshold' => $minimumThreshold,
                'has_range_data' => $hasRangeData,
                'has_checkbox_data' => $hasCheckboxData,
                'range_count' => $rangeCount,
                'checkboxes_count' => $checkboxesCount
            ]);

            return [
                'threshold_met' => true,
                'submission_count' => $submissionCount,
                'min_threshold' => $minimumThreshold,
                'category' => $category,
                'value' => $value,
                'has_range_data' => $hasRangeData,
                'has_checkbox_data' => $hasCheckboxData,
                'has_categories' => $hasCategories,
                'categories' => $aggregatedResults['categories'] ?? [],
                'active_tab' => $activeTab,
                'results' => $aggregatedResults['results'] ?? []
            ];
        } catch (\Exception $e) {
            Log::error("Error aggregating survey data: " . $e->getMessage(), ['category' => $category, 'value' => $value, 'exception' => $e]);

            return [
                'threshold_met' => false,
                'error' => 'Error processing survey data',
                'category' => $category,
                'value' => $value
            ];
        }
    }

    /**
     * Get the count of unique submissions across multiple feedbacks
     *
     * @param \Illuminate\Database\Eloquent\Collection $feedbacks Collection of feedbacks
     * @return int Count of unique submissions
     */
    private function getUniqueSubmissionCount($feedbacks): int
    {
        $feedbackIds = $feedbacks->pluck('id')->toArray();

        if (empty($feedbackIds)) {
            return 0;
        }

        return DB::table('results')
            ->join('questions', 'results.question_id', '=', 'questions.id')
            ->whereIn('questions.feedback_id', $feedbackIds)
            ->distinct('results.submission_id')
            ->count('results.submission_id');
    }

    /**
     * Calculate aggregated results for a collection of feedbacks
     *
     * @param \Illuminate\Database\Eloquent\Collection $feedbacks Collection of feedbacks
     * @return array Aggregated results grouped by question type
     */
    private function calculateAggregatedResults($feedbacks): array
    {
        $aggregated = [];
        $feedbackIds = $feedbacks->pluck('id')->toArray();

        if (empty($feedbackIds)) {
            Log::debug("No feedback IDs found for aggregation");
            return $aggregated;
        }

        // Get all questions for these feedbacks, with eager loaded relations
        $questions = Question::whereIn('feedback_id', $feedbackIds)
            ->with(['question_template', 'results', 'feedback_template'])
            ->get();

        Log::debug("Found questions for aggregation", [
            'question_count' => $questions->count(),
            'question_ids' => $questions->pluck('id')->toArray(),
            'questions_with_category' => $questions->whereNotNull('category')->count(),
            'categories' => $questions->pluck('category')->unique()->filter()->toArray()
        ]);

        // Use our categorizer to group questions
        $questionsByCategory = $this->categorizeQuestions($questions);

        Log::debug("Questions grouped by category", [
            'category_count' => $questionsByCategory->count(),
            'categories' => $questionsByCategory->keys()->toArray(),
            'questions_per_category' => $questionsByCategory->map->count()->toArray()
        ]);

        $aggregated['categories'] = [];

        // Process each category
        foreach ($questionsByCategory as $categoryName => $categoryQuestions) {
            $categoryResults = [];

            // Group by question type within each category
            $questionsByType = $categoryQuestions->groupBy(function($question) {
                return $question->question_template->type;
            });

            Log::debug("Question types in category: {$categoryName}", [
                'types' => $questionsByType->keys()->toArray(),
                'question_count' => $categoryQuestions->count(),
                'question_ids' => $categoryQuestions->pluck('id')->toArray()
            ]);

            // Process rating questions (range type)
            if ($questionsByType->has('range')) {
                $rangeQuestions = $questionsByType->get('range');
                $rangeResults = $this->aggregateRangeQuestions($rangeQuestions);
                $categoryResults['range'] = $rangeResults;

                Log::debug("Aggregated range questions for category: {$categoryName}", [
                    'question_count' => $rangeQuestions->count(),
                    'results_count' => count($rangeResults),
                    'question_ids' => $rangeQuestions->pluck('id')->toArray()
                ]);
            }

            // Process checkbox questions
            if ($questionsByType->has('checkboxes')) {
                $checkboxQuestions = $questionsByType->get('checkboxes');
                $checkboxResults = $this->aggregateCheckboxQuestions($checkboxQuestions);
                $categoryResults['checkboxes'] = $checkboxResults;

                Log::debug("Aggregated checkbox questions for category: {$categoryName}", [
                    'question_count' => $checkboxQuestions->count(),
                    'results_count' => count($checkboxResults),
                    'question_ids' => $checkboxQuestions->pluck('id')->toArray()
                ]);
            }

            // Only add category if it has results
            if (!empty($categoryResults)) {
                $aggregated['categories'][$categoryName] = [
                    'name' => $categoryName,
                    'results' => $categoryResults
                ];
                Log::debug("Added category with results: {$categoryName}", [
                    'range_count' => isset($categoryResults['range']) ? count($categoryResults['range']) : 0,
                    'checkbox_count' => isset($categoryResults['checkboxes']) ? count($categoryResults['checkboxes']) : 0
                ]);
            } else {
                Log::debug("Category has no results, skipping: {$categoryName}");
            }
        }

        // If no categories were found/used, maintain backwards compatibility
        if (empty($aggregated['categories'])) {
            Log::debug("No categories with results found, attempting backward compatibility mode");

            // Group questions by type only (old method)
            $questionsByType = $questions->groupBy(function($question) {
                return $question->question_template->type;
            });

            Log::debug("Questions grouped by type (backward compatibility)", [
                'types' => $questionsByType->keys()->toArray()
            ]);

            // Process rating questions (range type)
            if ($questionsByType->has('range')) {
                $rangeQuestions = $questionsByType->get('range');
                $rangeResults = $this->aggregateRangeQuestions($rangeQuestions);
                $aggregated['range'] = $rangeResults;

                Log::debug("Aggregated range questions (backward compatibility)", [
                    'question_count' => $rangeQuestions->count(),
                    'results_count' => count($rangeResults)
                ]);
            }

            // Process checkbox questions
            if ($questionsByType->has('checkboxes')) {
                $checkboxQuestions = $questionsByType->get('checkboxes');
                $checkboxResults = $this->aggregateCheckboxQuestions($checkboxQuestions);
                $aggregated['checkboxes'] = $checkboxResults;

                Log::debug("Aggregated checkbox questions (backward compatibility)", [
                    'question_count' => $checkboxQuestions->count(),
                    'results_count' => count($checkboxResults)
                ]);
            }

            // If we have results but no categories, create a default category
            if (!empty($aggregated['range']) || !empty($aggregated['checkboxes'])) {
                $defaultResults = [];
                if (!empty($aggregated['range'])) {
                    $defaultResults['range'] = $aggregated['range'];
                    unset($aggregated['range']);
                }
                if (!empty($aggregated['checkboxes'])) {
                    $defaultResults['checkboxes'] = $aggregated['checkboxes'];
                    unset($aggregated['checkboxes']);
                }

                $aggregated['categories']['general'] = [
                    'name' => 'general',
                    'results' => $defaultResults
                ];

                Log::debug("Created default 'general' category for backward compatibility");
            } else {
                Log::debug("No results found for any question type");
            }
        }

        return $aggregated;
    }

    /**
     * Categorize questions based on their content into predefined categories
     * Implements pattern matching to assign questions to the desired categories
     *
     * @param Collection $questions Collection of questions to categorize
     * @return Collection Questions grouped by assigned category
     */
    private function categorizeQuestions(Collection $questions): Collection
    {
        // Define category patterns and mappings
        $categoryPatterns = [
            // Behavior category (Verhalten des Lehrers)
            'behavior' => [
                '... ungeduldig',
                '... sicher im Auftreten',
                '... freundlich',
                '... energisch',
                '... tatkräftig',
                '... aufgeschlossen',
                'Der Lehrer achtet auf Ruhe'
            ],

            // Statements category (Bewerten Sie folgende Aussagen)
            'statements' => [
                '... bevorzugt',
                '... nimmt die',
                '... ermutigt',
                '... entscheidet',
                '... gesteht',
                'Die Fragen und Beiträge'
            ],

            // Quality category (Wie ist der Unterricht?)
            'quality' => [
                'Die Ziele des Unterrichts',
                'Der Lehrer redet',
                'Der Lehrer schweift',
                'Die Sprache des Lehrers',
                'Der Unterricht ist',
                'Unterrichtsmaterialien',
                'Der Stoff wird'
            ],

            // Claims category (Bewerten Sie folgende Behauptungen)
            'claims' => [
                'Die Themen der Schulaufgaben',
                'Der Schwierigkeitsgrad',
                'Die Bewertungen sind'
            ],

            // Feedback category (Zielscheiben Feedback)
            'feedback' => [
                'Das hat mir besonders',
                'Das hat mir nicht',
                'Verbesserungsvorschläge',
                'Open Feedback'
            ]
        ];

        // Map for questions about learning and teaching
        $teachingQualityKeywords = [
            'Unterricht', 'Lehrkraft', 'Lehrer', 'Lernklima', 'lernen', 'Hintergrundwissen', 'vorbereitet'
        ];

        // Initialize categories collection
        $questionsByCategory = collect([
            'behavior' => collect([]),
            'statements' => collect([]),
            'quality' => collect([]),
            'claims' => collect([]),
            'feedback' => collect([])
        ]);

        // Find potential statements category questions
        Log::debug("Looking for potential statements category questions", [
            'questions' => $questions->map(function($q) {
                return [
                    'id' => $q->id,
                    'text' => $q->question,
                    'has_responses' => $q->results->count() > 0
                ];
            })->toArray()
        ]);

        // Process each question
        foreach ($questions as $question) {
            $questionText = $question->question;
            $questionType = $question->question_template->type ?? 'unknown';
            $assignedCategory = null;

            // Try to match question text to category patterns
            foreach ($categoryPatterns as $category => $patterns) {
                foreach ($patterns as $pattern) {
                    if (stripos($questionText, $pattern) !== false) {
                        $assignedCategory = $category;
                        Log::debug("Assigned question to category '{$category}'", [
                            'question' => $questionText,
                            'id' => $question->id,
                            'matched_prefix' => $pattern
                        ]);
                        break 2; // Break both loops when a match is found
                    }
                }
            }

            // If no match yet, check if it's about teaching quality
            if (!$assignedCategory && $questionType !== 'text') {
                foreach ($teachingQualityKeywords as $keyword) {
                    if (stripos($questionText, $keyword) !== false) {
                        $assignedCategory = 'quality';
                        Log::debug("Assigned question to teaching quality category", [
                            'question' => $questionText,
                            'id' => $question->id,
                            'matched_keyword' => $keyword
                        ]);
                        break;
                    }
                }
            }

            // Default category for text type questions is feedback
            if (!$assignedCategory && $questionType === 'text') {
                $assignedCategory = 'feedback';
                Log::debug("Assigned text question to feedback category", [
                    'question' => $questionText,
                    'id' => $question->id
                ]);
            }

            // If still no match, put in general behavior category
            if (!$assignedCategory) {
                $assignedCategory = 'behavior';
                Log::debug("No category match, assigned to default behavior category", [
                    'question' => $questionText,
                    'id' => $question->id
                ]);
            }

            // Add to the appropriate category collection
            if (isset($questionsByCategory[$assignedCategory])) {
                $questionsByCategory[$assignedCategory]->push($question);
            } else {
                // Create category if it doesn't exist yet
                $questionsByCategory[$assignedCategory] = collect([$question]);
            }
        }

        // Remove empty categories
        $filteredCategories = $questionsByCategory->filter(function ($questions) {
            return $questions->count() > 0;
        });

        // Log category summary before filtering
        Log::debug("Category summary before filtering empty categories", [
            'categories' => $questionsByCategory->map(function($questions, $categoryName) {
                $hasResponses = $questions->reduce(function($carry, $question) {
                    return $carry || $question->results->count() > 0;
                }, false);

                return [
                    'title' => __('admin.category.' . $categoryName, ['default' => $categoryName]),
                    'question_count' => $questions->count(),
                    'has_responses' => $hasResponses
                ];
            })->toArray()
        ]);

        // Log the final grouping result
        Log::debug("Table survey categorization complete", [
            'final_category_count' => $filteredCategories->count(),
            'categories' => $filteredCategories->keys()->toArray()
        ]);

        return $filteredCategories;
    }

    /**
     * Aggregate results for range type questions
     *
     * Groups similar questions by text and calculates average ratings and
     * distribution of ratings for each question
     *
     * @param \Illuminate\Database\Eloquent\Collection $questions Collection of range-type questions with eager-loaded results
     * @return array Aggregated range results
     */
    private function aggregateRangeQuestions(Collection $questions): array
    {
        $results = [];

        Log::debug("Starting to aggregate range questions", [
            'question_count' => $questions->count(),
            'question_ids' => $questions->pluck('id')->toArray()
        ]);

        foreach ($questions as $question) {
            // Group similar questions by their text content
            $questionText = $question->question;

            Log::debug("Processing range question", [
                'question_id' => $question->id,
                'question_text' => $questionText,
                'min_value' => $question->question_template->min_value,
                'max_value' => $question->question_template->max_value
            ]);

            if (!isset($results[$questionText])) {
                $results[$questionText] = [
                    'question' => $questionText,
                    'min' => $question->question_template->min_value,
                    'max' => $question->question_template->max_value,
                    'average' => 0,
                    'count' => 0,
                    'distribution' => [],
                    'sum' => 0
                ];

                // Initialize the distribution array based on min/max values
                for ($i = $question->question_template->min_value; $i <= $question->question_template->max_value; $i++) {
                    $results[$questionText]['distribution'][$i] = 0;
                }
            }

            // Use the eager-loaded results rather than querying again
            $questionResults = $question->results()
                ->where('value_type', 'number')
                ->get();

            Log::debug("Found results for question", [
                'question_id' => $question->id,
                'results_count' => $questionResults->count(),
                'submission_ids' => $questionResults->pluck('submission_id')->unique()->toArray()
            ]);

            foreach ($questionResults as $result) {
                // Ensure we have a valid numeric value
                if (!isset($result->rating_value) || !is_numeric($result->rating_value)) {
                    Log::warning("Invalid rating value for result", [
                        'result_id' => $result->id,
                        'question_id' => $question->id,
                        'rating_value' => $result->rating_value ?? 'null'
                    ]);
                    continue;
                }

                $numericValue = (int)$result->rating_value;

                // Validate the value is within the expected range
                if ($numericValue < $question->question_template->min_value ||
                    $numericValue > $question->question_template->max_value) {
                    Log::warning("Rating value out of range", [
                        'result_id' => $result->id,
                        'question_id' => $question->id,
                        'rating_value' => $numericValue,
                        'min_value' => $question->question_template->min_value,
                        'max_value' => $question->question_template->max_value
                    ]);
                    continue;
                }

                // Update the distribution count for this rating
                $results[$questionText]['distribution'][$numericValue]++;

                // Update the sum and count for average calculation
                $results[$questionText]['sum'] += $numericValue;
                $results[$questionText]['count']++;
            }

            // Calculate the average if we have results
            if ($results[$questionText]['count'] > 0) {
                $results[$questionText]['average'] = round($results[$questionText]['sum'] / $results[$questionText]['count'], 1);
            }

            Log::debug("Completed processing range question", [
                'question_id' => $question->id,
                'question_text' => $questionText,
                'count' => $results[$questionText]['count'],
                'average' => $results[$questionText]['average'],
                'distribution' => $results[$questionText]['distribution']
            ]);
        }

        Log::debug("Completed aggregating range questions", [
            'unique_questions' => count($results)
        ]);

        return $results;
    }

    /**
     * Aggregate results for checkbox type questions
     *
     * Groups similar checkbox questions by text and calculates counts and
     * percentages for each option
     *
     * @param \Illuminate\Database\Eloquent\Collection $questions Collection of checkbox-type questions with eager-loaded results
     * @return array Aggregated checkbox results
     */
    private function aggregateCheckboxQuestions(Collection $questions): array
    {
        $results = [];

        Log::debug("Starting to aggregate checkbox questions", [
            'question_count' => $questions->count(),
            'question_ids' => $questions->pluck('id')->toArray()
        ]);

        foreach ($questions as $question) {
            // Group similar questions by their text content
            $questionText = $question->question;

            Log::debug("Processing checkbox question", [
                'question_id' => $question->id,
                'question_text' => $questionText,
                'options' => $question->question_template->options ?? []
            ]);

            if (!isset($results[$questionText])) {
                $results[$questionText] = [
                    'question' => $questionText,
                    'options' => [],
                    'percentages' => [],
                    'total_responses' => 0
                ];

                // Initialize options from the question template if available
                if ($question->question_template->options) {
                    $options = json_decode($question->question_template->options, true);
                    if (is_array($options)) {
                        foreach ($options as $option) {
                            $results[$questionText]['options'][$option] = 0;
                        }
                    }
                }
            }

            // Use the eager-loaded results rather than querying again
            $questionResults = $question->results()
                ->where('value_type', 'array')
                ->get();

            Log::debug("Found results for checkbox question", [
                'question_id' => $question->id,
                'results_count' => $questionResults->count(),
                'submission_ids' => $questionResults->pluck('submission_id')->unique()->toArray()
            ]);

            foreach ($questionResults as $result) {
                // Ensure we have a valid array value
                if (!isset($result->checkbox_value) || empty($result->checkbox_value)) {
                    Log::warning("Invalid checkbox value for result", [
                        'result_id' => $result->id,
                        'question_id' => $question->id,
                        'checkbox_value' => $result->checkbox_value ?? 'null'
                    ]);
                    continue;
                }

                // Try to decode the JSON value
                $selectedOptions = json_decode($result->checkbox_value, true);

                if (!is_array($selectedOptions)) {
                    Log::warning("Failed to decode checkbox value as array", [
                        'result_id' => $result->id,
                        'question_id' => $question->id,
                        'checkbox_value' => $result->checkbox_value
                    ]);
                    continue;
                }

                // Count each selected option
                foreach ($selectedOptions as $option) {
                    // Add option if it doesn't exist (in case the template options changed)
                    if (!isset($results[$questionText]['options'][$option])) {
                        $results[$questionText]['options'][$option] = 0;
                    }

                    // Increment the count for this option
                    $results[$questionText]['options'][$option]++;
                }

                // Increment the total responses count
                $results[$questionText]['total_responses']++;
            }

            // Calculate percentages for each option
            if ($results[$questionText]['total_responses'] > 0) {
                foreach ($results[$questionText]['options'] as $option => $count) {
                    $results[$questionText]['percentages'][$option] = round(
                        ($count / $results[$questionText]['total_responses']) * 100
                    );
                }
            }

            Log::debug("Completed processing checkbox question", [
                'question_id' => $question->id,
                'question_text' => $questionText,
                'total_responses' => $results[$questionText]['total_responses'],
                'options' => $results[$questionText]['options'],
                'percentages' => $results[$questionText]['percentages']
            ]);
        }

        Log::debug("Completed aggregating checkbox questions", [
            'unique_questions' => count($results)
        ]);

        return $results;
    }

    /**
     * Get threshold requirement for a specific category
     *
     * @param string $category The category to get threshold for
     * @return int The minimum threshold requirement
     */
    public function getThreshold(string $category): int
    {
        return self::THRESHOLDS[$category] ?? 0;
    }

    /**
     * Get all threshold values
     *
     * @return array Array of threshold values for each category
     */
    public function getAllThresholds(): array
    {
        return self::THRESHOLDS;
    }
}