<?php

namespace App\Services;

use App\Models\{Feedback, Question, Result};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StatisticsService
{
    /**
     * Calculate statistics for a survey
     *
     * This method processes all questions in a survey and calculates appropriate
     * statistics based on the question type. It handles different question types
     * (range, checkbox, text, etc.) and generates statistics like averages, medians,
     * and distributions.
     *
     * @param Feedback $survey The survey to calculate statistics for
     * @return array An array of statistics data for each question
     */
    public function calculateStatisticsForSurvey(Feedback $survey): array
    {
        // Format: statsArray[questionIndex] = ['question' => Question, 'template_type' => templateType, 'data' => [...]]
        $statistics = [];

        try {
            // Eager load all needed relationships upfront to avoid N+1 queries
            // This ensures we load questions, question templates, and all results in a single query
            if (!$survey->relationLoaded('questions') ||
                !$survey->relationLoaded('feedback_template') ||
                ($survey->questions->isNotEmpty() &&
                (!$survey->questions->first()->relationLoaded('question_template') ||
                !$survey->questions->first()->relationLoaded('results')))) {
                $survey->load([
                    'questions.question_template',
                    'questions.results',
                    'feedback_template'
                ]);
            }

            // Get unique submission count using the preloaded results
            // This avoids the additional query from $survey->submissions()->count()
            $submissionCount = 0;
            if ($survey->questions->isNotEmpty()) {
                $allResults = $survey->questions->flatMap(function ($question) {
                    return $question->results;
                });

                $submissionCount = $allResults->pluck('submission_id')->unique()->count();
            }

            // Log survey processing for debugging
            \Log::debug('Processing statistics for survey', [
                'survey_id' => $survey->id,
                'submission_count' => $submissionCount,
                'template' => $survey->feedback_template->name ?? 'unknown'
            ]);

            if ($submissionCount == 0) {
                // If there are no submissions, return empty statistics
                \Log::debug('No submissions found for survey', ['survey_id' => $survey->id]);
                return $statistics;
            }

            // Get the feedback template name (used to determine special processing)
            $templateName = $survey->feedback_template->name ?? '';
            $isTableSurvey = str_contains($templateName, 'templates.feedback.table');

            // Special handling for certain template types
            if (str_contains($templateName, 'templates.feedback.smiley')) {
                // For smiley template, we need to calculate the average smiley rating
                // and collect positive and negative feedback
                \Log::debug('Processing smiley survey statistics', [
                    'survey_id' => $survey->id,
                    'questions_count' => $survey->questions->count()
                ]);

                // We've already eager loaded these relationships at the start

                // Add a marker with smiley data
                $statistics[] = [
                    'question' => null,
                    'template_type' => 'smiley',
                    'data' => [
                        'submission_count' => $submissionCount,
                    ],
                ];

                // We'll still process individual questions below in the generic loop
                // and NOT skip them, so the smiley_survey view can find and display them
            }
            else if (str_contains($templateName, 'templates.feedback.target')) {
                // For target template, we need to calculate statistics for each segment
                \Log::debug('Processing target survey statistics', [
                    'survey_id' => $survey->id,
                    'questions_count' => $survey->questions->count()
                ]);

                // We've already eager loaded these relationships at the start

                // Calculate target-specific statistics
                $segmentStatisticsData = $this->calculateTargetStatistics($survey);

                // Add a marker with target diagram data
                $statistics[] = [
                    'question' => null,
                    'template_type' => 'target',
                    'data' => [
                        'submission_count' => $submissionCount,
                        'segment_statistics' => $segmentStatisticsData,
                    ],
                ];

                // We'll still process individual questions below in the generic loop,
                // but the template-specific display will use the segment_statistics data
            }
            else if ($isTableSurvey) {
                // For table templates, we need to calculate statistics for each question,
                // as table templates are composed of multiple range-type questions

                \Log::debug('Processing table survey statistics', [
                    'survey_id' => $survey->id,
                    'questions_count' => $survey->questions->count()
                ]);

                // We've already eager loaded these relationships at the start

                // Process individual questions before generating the marker so we can pass categories
                // to the table marker
                $tempStats = [];
                foreach ($survey->questions as $question) {
                    // Get the template type, fallback to text if not available
                    $questionTemplateType = $question->question_template->type ?? 'text';

                    // Calculate question-specific statistics
                    $questionStatistics = $this->calculateQuestionStatistics($question, $questionTemplateType);

                    // Log question statistics for debugging
                    \Log::debug('Question statistics', [
                        'question_id' => $question->id,
                        'question' => $question->question,
                        'template_type' => $questionTemplateType,
                        'has_results' => $question->results->count() > 0,
                        'stats' => array_keys($questionStatistics)
                    ]);

                    // Add to temporary stats array
                    if (!empty($questionStatistics)) {
                        $tempStats[] = [
                            'question' => $question,
                            'template_type' => $questionTemplateType,
                            'data' => $questionStatistics,
                        ];
                    }
                }

                // Categorize the questions
                $tableCategories = $this->categorizeTableSurveyQuestions($tempStats);

                // Log table categories for debugging
                \Log::debug('Table categories', [
                    'categories_count' => count($tableCategories),
                    'category_keys' => array_keys($tableCategories),
                    'first_category_questions_count' => isset($tableCategories[array_key_first($tableCategories)]) ?
                        count($tableCategories[array_key_first($tableCategories)]['questions'] ?? []) : 0,
                    'categories_structure' => json_encode(array_map(function($cat) {
                        return [
                            'title' => $cat['title'] ?? 'No Title',
                            'questions_count' => count($cat['questions'] ?? []),
                            'has_responses' => $cat['hasResponses'] ?? false,
                        ];
                    }, $tableCategories))
                ]);

                // Add the marker with table categories
                $statistics[] = [
                    'question' => null,
                    'template_type' => 'table',
                    'data' => [
                        'submission_count' => $submissionCount,
                        'table_survey' => true,
                        'table_categories' => $tableCategories,
                    ],
                ];

                // Add individual question stats to main stats array
                $statistics = array_merge($statistics, $tempStats);
            }

            // Original statistics calculation for other templates or in addition to template-specific stats
            foreach ($survey->questions as $question) {
                // Skip template-specific questions that have already been handled above
                if ((str_contains($templateName, 'templates.feedback.target') &&
                    $question->question_template && $question->question_template->type === 'range')) {
                    // Skip these questions as they're already handled in template-specific stats
                    continue;
                }

                $questionStatistics = [];
                // All question templates are already loaded at the beginning
                // Default to text if no template type is available
                $questionTemplateType = $question->question_template->type ?? 'text';

                switch ($questionTemplateType) {
                    case 'range':
                        // Get only the numeric results for range questions
                        $ratings = $question->results
                            ->where('value_type', 'number')
                            ->pluck('rating_value')
                            ->filter()
                            ->toArray();

                        if (!empty($ratings)) {
                            // Convert to numeric values
                            $ratings = array_map('floatval', $ratings);

                            // Calculate average (mean) rating
                            $questionStatistics['average_rating'] = round(array_sum($ratings) / count($ratings), 2);

                            // Count occurrences of each rating value
                            $questionStatistics['rating_counts'] = array_count_values(array_map('strval', $ratings));

                            // Calculate median rating
                            sort($ratings);
                            $count = count($ratings);
                            $questionStatistics['median_rating'] = $count % 2 === 0
                                ? ($ratings[($count / 2) - 1] + $ratings[$count / 2]) / 2
                                : $ratings[floor($count / 2)];
                            // Add count of unique submissions using the preloaded results
                            $questionStatistics['submission_count'] = collect($ratings)->count();
                        } else {
                            $questionStatistics['average_rating'] = 'No responses';
                            $questionStatistics['median_rating'] = 'No responses';
                            $questionStatistics['rating_counts'] = [];
                            $questionStatistics['submission_count'] = 0;
                        }
                        break;

                    case 'checkboxes':
                    case 'checkbox':
                        // For checkbox questions, only get checkbox type results
                        $checkboxResponses = $question->results
                            ->where('value_type', 'checkbox')
                            ->pluck('rating_value')
                            ->filter()
                            ->toArray();

                        $questionStatistics['option_counts'] = !empty($checkboxResponses)
                            ? array_count_values($checkboxResponses)
                            : [];

                        // Add count of unique submissions using the already loaded data
                        $questionStatistics['submission_count'] = collect($checkboxResponses)
                            ->unique()
                            ->count();
                        break;

                    case 'textarea':
                    case 'text':
                        // Only get text type results
                        $textResponses = $question->results
                            ->where('value_type', 'text')
                            ->pluck('rating_value')
                            ->filter()
                            ->toArray();

                        $questionStatistics['response_count'] = count($textResponses);
                        $questionStatistics['responses'] = $textResponses;

                        // Use the already loaded results to count unique submissions
                        $questionStatistics['submission_count'] = collect($textResponses)
                            ->count();
                        break;

                    default:
                        // Handle unknown question types gracefully
                        $questionStatistics['message'] = 'Statistics not implemented for this question type.';
                        $questionStatistics['submission_count'] = 0;
                }

                // Build the complete statistics object for this question
                $statistics[] = [
                    'question' => $question,
                    'template_type' => $questionTemplateType,
                    'data' => $questionStatistics,
                ];
            }
        } catch (\Exception $e) {
            // Log the error but return a graceful empty result with more specific error information
            \Log::error('Error calculating survey statistics: ' . $e->getMessage(), [
                'survey_id' => $survey->id,
                'exception' => $e
            ]);

            return [
                [
                    'question' => null,
                    'template_type' => 'error',
                    'data' => [
                        'message' => 'An error occurred while calculating statistics: ' . $e->getMessage(),
                        'error_type' => get_class($e),
                        'survey_id' => $survey->id
                    ]
                ]
            ];
        }

        return $statistics;
    }

    /**
     * Calculate statistics for target template surveys
     *
     * This method processes target survey responses and calculates statistics
     * for each segment in the target diagram.
     *
     * @param Feedback $survey The target survey to calculate statistics for
     * @return array An array of statistics data for each segment
     */
    protected function calculateTargetStatistics(Feedback $survey): array
    {
        $segmentStatisticsData = [];

        // Use the already loaded questions and sort them in code
        $questions = $survey->questions
            ->sortBy('order')
            ->sortBy('id')
            ->values();

        // Process each question (segment)
        foreach ($questions as $index => $question) {
            // Get ratings for this segment/question (only numeric values)
            $ratings = $question->results
                ->where('value_type', 'number')
                ->pluck('rating_value')
                ->filter()
                ->toArray();

            // Convert to numeric values
            $ratings = array_map('floatval', $ratings);

            if (!empty($ratings)) {
                $averageRating = round(array_sum($ratings) / count($ratings), 2);
                $ratingCounts = array_count_values(array_map('strval', $ratings));

                // Count unique submissions using the already loaded results
                $submissionCount = count($ratings);
            } else {
                $averageRating = 'No responses';
                $ratingCounts = [];
                $submissionCount = 0;
            }

            $segmentStatisticsData[] = [
                'segment_index' => $index,
                'statement' => $question->question,
                'average_rating' => $averageRating,
                'response_count' => count($ratings),
                'submission_count' => $submissionCount,
                'rating_counts' => $ratingCounts,
            ];
        }

        return $segmentStatisticsData;
    }

    /**
     * Categorize questions from a table survey into predefined categories.
     *
     * @param array $statistics The statistics data from calculateStatisticsForSurvey
     * @return array An array of categories with their questions
     */
    public function categorizeTableSurveyQuestions(array $statistics): array
    {
        // Set to true for verbose logging of categorization decisions
        $verboseLogging = config('app.debug', false);

        // Define categories structure with the correct German category names
        $tableCategories = [
            'behavior' => [
                'title' => 'Verhalten des Lehrers',
                'questions' => [],
                'hasResponses' => false,
            ],
            'statements' => [
                'title' => 'Bewerten Sie folgende Aussagen',
                'questions' => [],
                'hasResponses' => false,
            ],
            'quality' => [
                'title' => 'Wie ist der Unterricht?',
                'questions' => [],
                'hasResponses' => false,
            ],
            'claims' => [
                'title' => 'Bewerten Sie folgende Behauptungen',
                'questions' => [],
                'hasResponses' => false,
            ],
            'feedback' => [
                'title' => 'Offenes Feedback',
                'questions' => [],
                'hasResponses' => false,
            ],
        ];

        \Log::debug('Starting table survey categorization', [
            'stats_count' => count($statistics)
        ]);

        // Enable extra debug logging temporarily
        \Log::debug('Looking for potential statements category questions', [
            'questions' => collect($statistics)
                ->filter(function($stat) {
                    return isset($stat['question']) && isset($stat['question']->question);
                })
                ->map(function($stat) {
                    return [
                        'id' => $stat['question']->id ?? 'unknown',
                        'text' => $stat['question']->question ?? 'unknown',
                        'has_responses' => isset($stat['data']['submission_count']) && $stat['data']['submission_count'] > 0
                    ];
                })
                ->values()
                ->toArray()
        ]);

        $uncategorizedQuestions = [];
        $categoryPrefixes = [
            'behavior' => [
                // Teacher behavior related prefixes
                '... hält',
                '... ist motiviert',
                '... erklärt',
                '... spricht',
                '... reagiert',
                '... ist fachlich',
                '... ist',
                '... wirkt',
                '... fördert',
                '... unterrichtet',
                '... zeigt',
                '... freundlich',
                '... energisch',
                '... tatkräftig',
                '... aufgeschlossen',
                '... ungeduldig',
                '... sicher',
                'Der Lehrer achtet auf Ruhe',
            ],
            'statements' => [
                // Statement evaluation prefixes
                'Ich lerne',
                'Die Lehrkraft hat',
                'Die Lehrkraft ist',
                'Die Lehrkraft zeigt',
                'Die Lehrkraft sorgt',
                'Die Notengebung ist',
                'Ich konnte',
                'Der Unterricht wird',
                'Die Fragen und Beiträge',
                '... bevorzugt',
                '... nimmt',
                '... ermutigt',
                '... entscheidet',
                '... gesteht',
            ],
            'quality' => [
                // Teaching quality related prefixes
                'Der Unterricht',
                'Die Unterrichtsgestaltung',
                'Die Unterrichtsinhalte',
                'Die Lernatmosphäre',
                'Das Unterrichtstempo',
                'Das Lernklima',
                'Der Lehrer redet',
                'Der Lehrer schweift',
                'Die Sprache des Lehrers',
                'Die Ziele des Unterrichts',
                'Unterrichtsmaterialien',
                'Der Stoff wird',
            ],
            'claims' => [
                // Claim evaluation prefixes
                'Die Themen der Schulaufgaben',
                'Der Schwierigkeitsgrad',
                'Die Bewertungen sind',
                'Tests und Schulaufgaben',
                'Die Leistungsanforderungen',
                'Die Beurteilung',
            ],
            'feedback' => [
                // Open feedback prefixes
                'Was gefällt dir',
                'Was gefällt dir nicht',
                'Was würdest du',
                'Das hat mir besonders',
                'Das hat mir nicht',
                'Verbesserungsvorschläge',
                'Feedback',
                'Anmerkungen',
                'Kommentare',
            ],
        ];

        // Loop through statistics and categorize questions
        foreach ($statistics as $stat) {
            if (!isset($stat['question']) || !isset($stat['question']->question)) {
                \Log::debug('Skipping stat without question data', [
                    'stat_keys' => array_keys($stat)
                ]);
                continue;
            }

            $question = $stat['question']->question ?? '';
            $questionId = $stat['question']->id ?? 'unknown';
            $categoryAssigned = false;

            // Check if this question has responses
            $hasResponses = false;
            if (isset($stat['data']['submission_count']) && $stat['data']['submission_count'] > 0) {
                $hasResponses = true;
            }

            // Try to categorize the question based on prefixes
            foreach ($categoryPrefixes as $category => $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (Str::startsWith($question, $prefix)) {
                        $tableCategories[$category]['questions'][] = $stat;
                        if ($hasResponses) {
                            $tableCategories[$category]['hasResponses'] = true;
                        }
                        $categoryAssigned = true;
                        if ($verboseLogging) {
                            \Log::debug("Assigned question to category '$category'", [
                                'question' => $question,
                                'id' => $questionId,
                                'matched_prefix' => $prefix
                            ]);
                        }
                        break 2; // Break out of both loops
                    }
                }
            }

            // Special case: If not assigned and starts with '...' assign to behavior
            if (!$categoryAssigned && Str::startsWith($question, '...')) {
                $tableCategories['behavior']['questions'][] = $stat;
                if ($hasResponses) {
                    $tableCategories['behavior']['hasResponses'] = true;
                }
                $categoryAssigned = true;
                if ($verboseLogging) {
                    \Log::debug("Assigned '...' question to default 'behavior' category", [
                        'question' => $question,
                        'id' => $questionId
                    ]);
                }
            }

            // If still not categorized, add to uncategorized list and log
            if (!$categoryAssigned) {
                $uncategorizedQuestions[] = [
                    'id' => $questionId,
                    'text' => $question
                ];
                // As a fallback, put uncategorized questions in feedback
                $tableCategories['feedback']['questions'][] = $stat;
                if ($hasResponses) {
                    $tableCategories['feedback']['hasResponses'] = true;
                }
                \Log::warning("Question not categorized, added to feedback category", [
                    'question' => $question,
                    'id' => $questionId
                ]);
            }
        }

        // Log uncategorized questions
        if (!empty($uncategorizedQuestions)) {
            \Log::warning('Uncategorized questions found', [
                'count' => count($uncategorizedQuestions),
                'questions' => $uncategorizedQuestions
            ]);
        }

        // Collect category summary for logging
        $categorySummary = [];
        foreach ($tableCategories as $key => $category) {
            $categorySummary[$key] = [
                'title' => $category['title'],
                'question_count' => count($category['questions']),
                'has_responses' => $category['hasResponses']
            ];
        }

        if ($verboseLogging) {
            \Log::debug('Category summary before filtering empty categories', [
                'categories' => $categorySummary
            ]);
        }

        // Remove empty categories or ones without responses
        foreach ($tableCategories as $key => $category) {
            if (empty($category['questions'])) {
                \Log::debug("Removing empty category: $key");
                unset($tableCategories[$key]);
            }
        }

        if ($verboseLogging) {
            \Log::debug('Table survey categorization complete', [
                'final_category_count' => count($tableCategories),
                'categories' => array_keys($tableCategories)
            ]);
        }

        return $tableCategories;
    }

    /**
     * Calculate statistics for a single question based on its type.
     *
     * @param \App\Models\Question $question The question to calculate statistics for
     * @param string $questionTemplateType The template type of the question
     * @return array An array of statistics data for the question
     */
    protected function calculateQuestionStatistics($question, string $questionTemplateType): array
    {
        $questionStatistics = [];

        switch ($questionTemplateType) {
            case 'range':
                // Get only the numeric results for range questions
                $ratings = $question->results
                    ->where('value_type', 'number')
                    ->pluck('rating_value')
                    ->filter()
                    ->toArray();

                if (!empty($ratings)) {
                    // Convert to numeric values
                    $ratings = array_map('floatval', $ratings);

                    // Calculate average (mean) rating
                    $questionStatistics['average_rating'] = round(array_sum($ratings) / count($ratings), 2);

                    // Count occurrences of each rating value
                    $questionStatistics['rating_counts'] = array_count_values(array_map('strval', $ratings));

                    // Calculate median rating
                    sort($ratings);
                    $count = count($ratings);
                    $questionStatistics['median_rating'] = $count % 2 === 0
                        ? ($ratings[($count / 2) - 1] + $ratings[$count / 2]) / 2
                        : $ratings[floor($count / 2)];

                    // Use the count from the already filtered results
                    $questionStatistics['submission_count'] = count($ratings);
                } else {
                    $questionStatistics['average_rating'] = 'No responses';
                    $questionStatistics['median_rating'] = 'No responses';
                    $questionStatistics['rating_counts'] = [];
                    $questionStatistics['submission_count'] = 0;
                }
                break;

            case 'checkboxes':
            case 'checkbox':
                // For checkbox questions, only get checkbox type results
                $checkboxResults = $question->results
                    ->where('value_type', 'checkbox')
                    ->pluck('rating_value')
                    ->filter()
                    ->toArray();

                if (!empty($checkboxResults)) {
                    // Count occurrences of each option
                    $questionStatistics['option_counts'] = array_count_values($checkboxResults);

                    // Use a more efficient method that doesn't require additional querying
                    // Just count the unique checkbox results
                    $questionStatistics['submission_count'] = $question->results
                        ->where('value_type', 'checkbox')
                        ->pluck('submission_id')
                        ->unique()
                        ->count();
                } else {
                    $questionStatistics['option_counts'] = [];
                    $questionStatistics['submission_count'] = 0;
                }
                break;

            case 'textarea':
            case 'text':
                // Get text responses
                $textResponses = $question->results
                    ->where('value_type', 'text')
                    ->pluck('rating_value')
                    ->filter()
                    ->toArray();

                $questionStatistics['responses'] = $textResponses;
                $questionStatistics['response_count'] = count($textResponses);

                // Count directly from the loaded text responses
                $questionStatistics['submission_count'] = count($textResponses);
                break;

            default:
                // For other or unknown question types, just include submission count
                $questionStatistics['submission_count'] = $question->results
                    ->pluck('submission_id')
                    ->unique()
                    ->count();

                // Set a message for unknown question types
                $questionStatistics['message'] = 'No statistics available for this question type.';
                break;
        }

        return $questionStatistics;
    }
}