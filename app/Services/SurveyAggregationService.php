<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Question;
use App\Models\Result;
use App\Models\SchoolClass;
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

    private const CATEGORY_ID = [
        'class' => 'school_class_id',
        'department' => 'department_id',
        'subject' => 'subject_id',
        'school_year' => 'school_year_id',
    ];

    /**
     * Get all available values for a specific category
     *
     * @param string $category Category to get values for (class, department, subject, school_year)
     * @return array Array of distinct values for the category
     */
    public function getCategoryValues(string $category): array
    {
        $runningOrExpired = function ($query) {
            $query->where('status', 'running')
                ->orWhere('status', 'expired');
        };

        switch ($category) {
            case 'class':
                return DB::table('school_classes')
                    ->join('feedback', 'school_classes.id', '=', 'feedback.school_class_id')
                    ->where($runningOrExpired)
                    ->groupBy('school_classes.id')
                    ->pluck('school_classes.id', 'school_classes.name')
                    ->toArray();
            case 'department':
                return DB::table('departments')
                    ->join('feedback', 'departments.id', '=', 'feedback.department_id')
                    ->where($runningOrExpired)
                    ->groupBy('departments.id')
                    ->pluck('departments.id', 'departments.name')
                    ->toArray();
            case 'subject':
                return DB::table('subjects')
                    ->join('feedback', 'subjects.id', '=', 'feedback.subject_id')
                    ->where($runningOrExpired)
                    ->groupBy('subjects.id')
                    ->pluck('subjects.id', 'subjects.name')
                    ->toArray();
            case 'school_year':
                return DB::table('school_years')
                    ->join('feedback', 'school_years.id', '=', 'feedback.school_year_id')
                    ->where($runningOrExpired)
                    ->groupBy('school_years.id')
                    ->pluck('school_years.id', 'school_years.name')
                    ->toArray();
            default:
                Log::warning("Attempted to get values for unsupported category: {$category}");
                return [];
        }

    }

    /**
     * Aggregate results for a specific category and value
     *
     * @param string $category Category to aggregate by (class, department, subject, school_year)
     * @param int $value The specific value to filter on
     * @param int|null $threshold Optional custom threshold (defaults to preset thresholds)
     * @return array Array of aggregated data including threshold status and results if available
     */
    public function aggregateByCategory(string $category, int $value, ?int $threshold = null): array
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
            $feedbacks = Feedback::where(self::CATEGORY_ID[$category], $value)
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
                // Prioritize target_feedback as the active tab if it exists
                if (isset($aggregatedResults['categories']['target_feedback'])) {
                    $activeTab = 'target_feedback';
                    Log::debug("Setting target_feedback as active tab");
                } else {
                    $activeTab = array_key_first($aggregatedResults['categories']);
                    Log::debug("Setting first available category as active tab: {$activeTab}");
                }
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

        // Ensure the checkbox_feedback category is properly handled
        // Extract checkbox questions separately if they're not already categorized
        $checkboxQuestions = $questions->filter(function($question) {
            return $question->question_template &&
                   in_array($question->question_template->type, ['checkbox', 'checkboxes']);
        });

        // If we have checkbox questions and they're not in a category, add them to checkbox_feedback
        if ($checkboxQuestions->count() > 0 &&
            !isset($questionsByCategory['checkbox_feedback'])) {
            $questionsByCategory['checkbox_feedback'] = $checkboxQuestions;

            Log::debug("Added checkbox questions to checkbox_feedback category", [
                'count' => $checkboxQuestions->count(),
                'question_ids' => $checkboxQuestions->pluck('id')->toArray()
            ]);
        }

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
                return $question->question_template->type ?? 'unknown';
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

            // Process checkbox questions - collect both 'checkbox' and 'checkboxes' types
            $allCheckboxQuestions = collect();

            if ($questionsByType->has('checkbox')) {
                $allCheckboxQuestions = $allCheckboxQuestions->merge($questionsByType->get('checkbox'));
            }

            if ($questionsByType->has('checkboxes')) {
                $allCheckboxQuestions = $allCheckboxQuestions->merge($questionsByType->get('checkboxes'));
            }

            if ($allCheckboxQuestions->count() > 0) {
                $checkboxResults = $this->aggregateCheckboxQuestions($allCheckboxQuestions);
                $categoryResults['checkboxes'] = $checkboxResults;

                Log::debug("Aggregated checkbox questions for category: {$categoryName}", [
                    'question_count' => $allCheckboxQuestions->count(),
                    'results_count' => count($checkboxResults),
                    'question_ids' => $allCheckboxQuestions->pluck('id')->toArray()
                ]);
            }

            // Only add category if it has results or if it's the target_feedback category which should always be shown
            if (!empty($categoryResults) || $categoryName === 'target_feedback') {
                $aggregated['categories'][$categoryName] = [
                    'name' => $categoryName,
                    'results' => $categoryResults
                ];
                if (!empty($categoryResults)) {
                    Log::debug("Added category with results: {$categoryName}", [
                        'range_count' => isset($categoryResults['range']) ? count($categoryResults['range']) : 0,
                        'checkbox_count' => isset($categoryResults['checkboxes']) ? count($categoryResults['checkboxes']) : 0
                    ]);
                } else {
                    Log::debug("Added category without results (special case): {$categoryName}");
                }
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
        // Define our final categories and their display order
        $finalCategories = [
            'behavior',          // Verhalten des Lehrers
            'statements',        // Bewerten Sie folgende Aussagen
            'quality',           // Wie ist der Unterricht?
            'claims',            // Bewerten Sie folgende Behauptungen
            'checkbox_feedback', // Checkbox Feedback
            'target_feedback'    // Zielscheiben Feedback
        ];

        // Initialize categories collection with ordered keys
        $questionsByCategory = collect();
        foreach ($finalCategories as $category) {
            $questionsByCategory[$category] = collect();
        }

        // Log all the questions for diagnostic purposes
        Log::debug("Questions for categorization", [
            'total_count' => $questions->count(),
            'feedback_ids' => $questions->pluck('feedback_id')->unique()->toArray()
        ]);

        // First pass: Special handling for checkbox and checkboxes types to put them in checkbox_feedback category
        foreach ($questions as $question) {
            if ($question->question_template &&
                in_array($question->question_template->type, ['checkbox', 'checkboxes'])) {
                $questionsByCategory['checkbox_feedback']->push($question);
                // Mark as processed by adding a property
                $question->already_categorized = true;
            } else {
                $question->already_categorized = false;
            }
        }

        // Report on checkbox categorization
        Log::debug("Checkbox questions categorized", [
            'count' => $questionsByCategory['checkbox_feedback']->count(),
            'question_ids' => $questionsByCategory['checkbox_feedback']->pluck('id')->toArray()
        ]);

        // Check for feedback templates used with target feedback
        $targetFeedbackTemplateIds = $questions->map(function($q) {
            return $q->feedback_template &&
                   (stripos($q->feedback_template->template ?? '', 'target') !== false ||
                   stripos($q->feedback_template->name ?? '', 'zielscheibe') !== false) ?
                   $q->feedback_template->id : null;
        })->filter()->unique()->values()->toArray();

        // Get feedback ids that use target feedback templates
        $targetFeedbackIds = $questions->filter(function($q) use ($targetFeedbackTemplateIds) {
            return in_array($q->feedback_template_id ?? 0, $targetFeedbackTemplateIds);
        })->pluck('feedback_id')->unique()->values()->toArray();

        Log::debug("Target feedback information", [
            'template_ids' => $targetFeedbackTemplateIds,
            'feedback_ids' => $targetFeedbackIds
        ]);

        // Define exact patterns for each category
        $categoryPatterns = [
            'behavior' => [
                '... ungeduldig',
                '... sicher im Auftreten',
                '... freundlich',
                '... energisch und aufbauend',
                '... tatkräftig, aktiv',
                '... aufgeschlossen',
                'Sie/Er ist ...'
            ],
            'statements' => [
                'bevorzugt manche Schülerinnen oder Schüler',
                'nimmt die Schülerinnen und Schüler ernst',
                'ermutigt und lobt viel',
                'entscheidet immer allein',
                'gesteht eigene Fehler ein',
                'Die Lehrerin, der Lehrer ...'
            ],
            'quality' => [
                'Die Ziele des Unterrichts sind klar erkennbar',
                'Der Lehrer redet zu viel',
                'Der Lehrer schweift oft vom Thema ab',
                'Die Fragen und Beiträge der Schülerinnen und Schüler werden ernst genommen',
                'Die Sprache des Lehrers ist gut verständlich',
                'Der Lehrer achtet auf Ruhe und Disziplin im Unterricht',
                'Der Unterricht ist abwechslungsreich',
                'Unterrichtsmaterialien sind ansprechend und gut verständlich gestaltet',
                'Der Stoff wird ausreichend wiederholt und geübt',
                'Wie ist der Unterricht?'
            ],
            'claims' => [
                'Die Themen der Schulaufgaben werden rechtzeitig vorher bekannt gegeben',
                'Der Schwierigkeitsgrad der Leistungsnachweise entspricht dem der Unterrichtsinhalte',
                'Die Bewertungen sind nachvollziehbar und verständlich',
                'Bewerten Sie folgende Behauptungen'
            ],
            'target_feedback' => [
                'Ich lerne im Unterricht viel',
                'Die Lehrkraft hat ein großes Hintergrundwissen',
                'Die Lehrkraft ist immer gut vorbereitet',
                'Die Lehrkraft zeigt Interesse an ihren Schülern',
                'Die Lehrkraft sorgt für ein gutes Lernklima in der Klasse',
                'Die Notengebung ist fair und nachvollziehbar',
                'Ich konnte dem Unterricht immer gut folgen',
                'Der Unterricht wird vielfältig gestaltet'
            ]
        ];

        // Categorize remaining questions
        foreach ($questions as $question) {
            // Skip already categorized questions (like checkboxes)
            if ($question->already_categorized ?? false) {
                continue;
            }

            $questionText = $question->question;
            $assignedCategory = null; // Default unassigned

            // First check if this question belongs to a target feedback form
            if (in_array($question->feedback_id, $targetFeedbackIds)) {
                $assignedCategory = 'target_feedback';
                Log::debug("Assigned to target_feedback based on feedback template", [
                    'question' => $questionText,
                    'id' => $question->id,
                    'feedback_id' => $question->feedback_id
                ]);
            }
            // Special case for text type questions (always target_feedback)
            else if ($question->question_template->type === 'text') {
                $assignedCategory = 'target_feedback';
                Log::debug("Assigned text question to target_feedback", [
                    'question' => $questionText,
                    'id' => $question->id
                ]);
            }
            else {
                // Try to match against our exact patterns
                $matchFound = false;

                foreach ($categoryPatterns as $category => $patterns) {
                    foreach ($patterns as $pattern) {
                        if (stripos($questionText, $pattern) !== false) {
                            $assignedCategory = $category;
                            $matchFound = true;
                            Log::debug("Assigned to {$category} based on exact pattern match", [
                                'question' => $questionText,
                                'pattern' => $pattern,
                                'id' => $question->id
                            ]);
                            break 2; // Break both loops
                        }
                    }
                }

                // If no exact pattern matched, use additional heuristics
                if (!$matchFound) {
                    // Check if it's a behavior question starting with '...'
                    if (preg_match('/^\.\.\./', $questionText)) {
                        $assignedCategory = 'behavior';
                        Log::debug("Assigned to behavior category based on '...' prefix", [
                            'question' => $questionText,
                            'id' => $question->id
                        ]);
                    }
                    // Categorize based on section headers
                    else if (stripos($questionText, 'Verhalten des Lehrers') !== false) {
                        $assignedCategory = 'behavior';
                        Log::debug("Assigned to behavior based on section header", [
                            'question' => $questionText,
                            'id' => $question->id
                        ]);
                    }
                    else if (stripos($questionText, 'Bewerten Sie folgende Aussagen') !== false) {
                        $assignedCategory = 'statements';
                        Log::debug("Assigned to statements based on section header", [
                            'question' => $questionText,
                            'id' => $question->id
                        ]);
                    }
                    else if (stripos($questionText, 'Wie ist der Unterricht') !== false) {
                        $assignedCategory = 'quality';
                        Log::debug("Assigned to quality based on section header", [
                            'question' => $questionText,
                            'id' => $question->id
                        ]);
                    }
                    else if (stripos($questionText, 'Bewerten Sie folgende Behauptungen') !== false) {
                        $assignedCategory = 'claims';
                        Log::debug("Assigned to claims based on section header", [
                            'question' => $questionText,
                            'id' => $question->id
                        ]);
                    }
                    // Fallback categorization based on keywords
                    else if (stripos($questionText, 'Lehrer') !== false ||
                             stripos($questionText, 'Lehrkraft') !== false) {
                        if (stripos($questionText, 'Unterricht') !== false ||
                            stripos($questionText, 'unterrichtet') !== false) {
                            $assignedCategory = 'quality';
                        } else {
                            $assignedCategory = 'behavior';
                        }
                        Log::debug("Assigned based on teacher keyword", [
                            'question' => $questionText,
                            'id' => $question->id,
                            'category' => $assignedCategory
                        ]);
                    }
                    else if (stripos($questionText, 'Unterricht') !== false ||
                             stripos($questionText, 'Materialien') !== false ||
                             stripos($questionText, 'Stoff') !== false) {
                        $assignedCategory = 'quality';
                        Log::debug("Assigned to quality based on teaching keywords", [
                            'question' => $questionText,
                            'id' => $question->id
                        ]);
                    }
                    else if (stripos($questionText, 'Bewertung') !== false ||
                             stripos($questionText, 'Notengebung') !== false ||
                             stripos($questionText, 'Schulaufgaben') !== false ||
                             stripos($questionText, 'Leistungsnachweise') !== false) {
                        $assignedCategory = 'claims';
                        Log::debug("Assigned to claims based on assessment keywords", [
                            'question' => $questionText,
                            'id' => $question->id
                        ]);
                    }
                    // Fallback - if all else fails
                    else {
                        $assignedCategory = 'target_feedback';
                        Log::debug("No category match, assigned to default target_feedback", [
                            'question' => $questionText,
                            'id' => $question->id
                        ]);
                    }
                }
            }

            // Add to the appropriate category collection
            if (isset($questionsByCategory[$assignedCategory])) {
                $questionsByCategory[$assignedCategory]->push($question);
            } else {
                // Create category if it doesn't exist yet (should not happen with our predefined list)
                $questionsByCategory[$assignedCategory] = collect([$question]);
            }
        }

        // Keep only categories with questions
        $filteredCategories = $questionsByCategory->filter(function ($questions) {
            return $questions->count() > 0;
        });

        // Always ensure target_feedback exists, even if empty
        if (!isset($filteredCategories['target_feedback'])) {
            $filteredCategories['target_feedback'] = collect();
        }

        // Log final category assignments
        Log::debug("Category assignment summary", [
            'categories' => $filteredCategories->map(function($questions, $category) {
                return [
                    'name' => $category,
                    'count' => $questions->count(),
                    'sample_questions' => $questions->take(3)->map(function($q) {
                        return $q->question;
                    })->toArray()
                ];
            })->toArray()
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
     * Groups similar questions by text and calculates distribution of selected options
     *
     * @param \Illuminate\Database\Eloquent\Collection $questions Collection of checkbox/checkboxes type questions
     * @return array Aggregated checkbox results
     */
    private function aggregateCheckboxQuestions(Collection $questions): array
    {
        $results = [];
        // English keys used in the database
        $validOptions = ['yes', 'no', 'na'];

        Log::debug("Starting to aggregate checkbox questions", [
            'question_count' => $questions->count(),
            'question_ids' => $questions->pluck('id')->toArray()
        ]);

        foreach ($questions as $question) {
            // Group similar questions by their text content
            $questionText = $question->question;
            $questionType = $question->question_template->type ?? 'checkbox';

            // Skip open feedback responses (typically stored with name containing 'feedback')
            if (stripos($questionText, 'feedback') !== false ||
                stripos($questionText, 'comments') !== false ||
                $questionText == 'responses[feedback]') {
                continue;
            }

            Log::debug("Processing checkbox question", [
                'question_id' => $question->id,
                'question_text' => $questionText,
                'question_type' => $questionType
            ]);

            if (!isset($results[$questionText])) {
                $results[$questionText] = [
                    'question' => $questionText,
                    'question_type' => $questionType,
                    'options' => [],
                    'total_responses' => 0
                ];

                // Initialize all valid options with 0 counts
                foreach ($validOptions as $option) {
                    $results[$questionText]['options'][$option] = 0;
                }
            }

            // Get all results for this question
            // For checkbox type we want value_type = 'checkbox' or any responses for checkbox type questions
            $questionResults = $question->results()
                ->where(function($query) {
                    $query->where('value_type', 'checkbox')
                          ->orWhere('value_type', 'text'); // Some older responses might be stored as text
                })
                ->get();

            Log::debug("Found checkbox results for question", [
                'question_id' => $question->id,
                'results_count' => $questionResults->count(),
                'submission_ids' => $questionResults->pluck('submission_id')->unique()->count()
            ]);

            // Count unique submissions as total responses
            $uniqueSubmissionCount = $questionResults->pluck('submission_id')->unique()->count();
            $results[$questionText]['total_responses'] = $uniqueSubmissionCount;

            foreach ($questionResults as $result) {
                // Ensure we have a valid value
                if (!isset($result->rating_value) || empty($result->rating_value)) {
                    continue;
                }

                $optionValue = $result->rating_value;

                // Only count valid options (Yes, No, Not applicable)
                if (!in_array($optionValue, $validOptions)) {
                    Log::debug("Filtering out custom response", [
                        'question_id' => $question->id,
                        'result_id' => $result->id,
                        'custom_value' => $optionValue
                    ]);
                    continue;
                }

                // Increment the count for this option
                $results[$questionText]['options'][$optionValue]++;
            }

            // Calculate percentages for better presentation
            if ($results[$questionText]['total_responses'] > 0) {
                $results[$questionText]['percentages'] = [];
                foreach ($results[$questionText]['options'] as $option => $count) {
                    $results[$questionText]['percentages'][$option] = round(
                        ($count / $results[$questionText]['total_responses']) * 100
                    );
                }
            }
        }

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
