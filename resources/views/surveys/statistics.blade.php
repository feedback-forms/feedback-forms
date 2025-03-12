<x-app-layout>
    <x-slot name="header">
        <div class="bg-indigo-100 dark:bg-indigo-900 py-4 px-6 rounded-md shadow-md">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Survey Statistics') }} - {{ $survey->feedback_template->title ?? 'Survey' }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-8">
                        <a href="{{ route('dashboard') }}" class="flex flex-row gap-2 items-center w-fit text-lg px-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 dark:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Back to Dashboard') }}</span>
                        </a>
                    </div>

                    <h3 class="text-xl font-semibold mb-4 text-indigo-700 dark:text-indigo-300">{{ __('Survey Details') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
                        <div class="bg-gray-50 dark:bg-gray-700 p-5 rounded-lg shadow-sm">
                            <p class="mb-2"><span class="font-semibold">Survey Title:</span> {{ $survey->feedback_template->title ?? 'N/A' }}</p>
                            <p class="mb-2"><span class="font-semibold">Access Key:</span> {{ $survey->accesskey }}</p>
                            <p><span class="font-semibold">Created:</span> {{ $survey->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-5 rounded-lg shadow-sm">
                            <p class="mb-2"><span class="font-semibold">Responses:</span> {{ $survey->submission_count }} / {{ $survey->limit == -1 ? '∞' : $survey->limit }}</p>
                            <p class="mb-2"><span class="font-semibold">Expires:</span> {{ $survey->expire_date->format('M d, Y') }}</p>
                            <p><span class="font-semibold">Status:</span>
                                @if($survey->expire_date->isPast())
                                    <span class="text-red-500 font-medium">Expired</span>
                                @else
                                    <span class="text-green-500 font-medium">Active</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <h3 class="text-xl font-semibold mt-10 mb-6 text-indigo-700 dark:text-indigo-300">{{ __('Question Statistics') }}</h3>

                    @if(config('app.debug'))
                        <div class="p-4 mb-6 bg-gray-100 dark:bg-gray-700 rounded-lg shadow-sm">
                            <details>
                                <summary class="cursor-pointer text-sm text-indigo-600 dark:text-indigo-400">Debug Information</summary>
                                <div class="mt-2 p-2 bg-white dark:bg-gray-800 rounded overflow-auto max-h-96">
                                    <p class="font-medium mb-2">Survey Info:</p>
                                    <pre class="text-xs">{{ json_encode(['id' => $survey->id, 'template' => $survey->feedback_template->name ?? 'N/A', 'submission_count' => $survey->submissions()->count()], JSON_PRETTY_PRINT) }}</pre>

                                    <p class="font-medium mb-2 mt-4">Statistics Data Summary:</p>
                                    @foreach($statisticsData as $index => $stat)
                                        <div class="mb-1 border-b pb-1">
                                            <p><strong>Item {{ $index + 1 }}:</strong>
                                                Type: {{ $stat['template_type'] }},
                                                Question: {{ $stat['question'] ? Str::limit($stat['question']->question, 30) : 'null' }}
                                            </p>
                                            <p class="text-xs">Data Keys: {{ implode(', ', array_keys($stat['data'])) }}</p>
                                        </div>
                                    @endforeach

                                    <p class="font-medium mb-2 mt-4">Table Categories:</p>
                                    @if(isset($isTableSurvey) && $isTableSurvey)
                                        @foreach(isset($tableCategories) ? $tableCategories : [] as $catKey => $category)
                                            <p><strong>{{ $category['title'] ?? 'Unknown Category' }}:</strong> {{ count($category['questions'] ?? []) }} questions, Has Responses: {{ ($category['hasResponses'] ?? false) ? 'Yes' : 'No' }}</p>
                                        @endforeach
                                    @else
                                        <p>Not a table survey</p>
                                    @endif
                                </div>
                            </details>
                        </div>
                    @endif

                    @php
                        // Initialize variables
                        $isTableSurvey = false;
                        $tableCategories = [];
                        $hasTargetStatistics = false;

                        // Add debug section for diagnostics
                        $debug = [];
                        $debug['survey_type'] = $survey->feedback_template->name ?? 'unknown';
                        $debug['submission_count'] = $survey->submissions()->count();

                        // Process the statistics data
                        foreach($statisticsData as $stat) {
                            // Check for table type statistics
                            if ($stat['template_type'] === 'table' && isset($stat['data']['table_survey']) && $stat['data']['table_survey'] === true) {
                                $debug['has_table_stats'] = true;
                                $isTableSurvey = true;

                                \Log::debug('Found table stat item in view', [
                                    'template_type' => $stat['template_type'],
                                    'table_survey_flag' => $stat['data']['table_survey'],
                                    'has_table_categories' => isset($stat['data']['table_categories']),
                                    'submission_count' => $stat['data']['submission_count']
                                ]);

                                if (isset($stat['data']['table_categories']) && is_array($stat['data']['table_categories'])) {
                                    $tableCategories = $stat['data']['table_categories'];
                                    $debug['table_categories_count'] = count($tableCategories);
                                    $debug['table_categories_keys'] = array_keys($tableCategories);

                                    // Add debugging about first category's questions
                                    if (!empty($tableCategories)) {
                                        $firstKey = array_key_first($tableCategories);
                                        $debug['first_category'] = $firstKey;
                                        $debug['first_category_questions_count'] = count($tableCategories[$firstKey]['questions'] ?? []);
                                    }
                                } else {
                                    $debug['table_categories_issue'] = 'table_categories missing or not an array';
                                }
                            }

                            // Check for target type statistics
                            if ($stat['template_type'] === 'target') {
                                $hasTargetStatistics = true;
                            }
                        }

                        // Add debug information about tableCategories
                        $debug['is_table_survey'] = $isTableSurvey;
                        $debug['table_categories_empty'] = empty($tableCategories);

                        // If we have tableCategories, process them to add hasResponses flag
                        if (!empty($tableCategories)) {
                            // Initialize categories response tracking
                            $categoryHasResponses = [];

                            // Go through each category and check for responses
                            foreach ($tableCategories as $catKey => $category) {
                                $categoryHasResponses[$catKey] = false;

                                // Skip if no questions in this category
                                if (empty($category['questions'])) {
                                    continue;
                                }

                                // Check each question for responses
                                foreach ($category['questions'] as $stat) {
                                    // Range questions with numeric responses
                                    if ($stat['template_type'] === 'range' &&
                                        isset($stat['data']['average_rating']) &&
                                        is_numeric($stat['data']['average_rating'])) {
                                        $categoryHasResponses[$catKey] = true;
                                        break;
                                    }
                                    // Text questions with responses
                                    elseif (($stat['template_type'] === 'text' || $stat['template_type'] === 'textarea') &&
                                          isset($stat['data']['response_count']) &&
                                          $stat['data']['response_count'] > 0) {
                                        $categoryHasResponses[$catKey] = true;
                                        break;
                                    }
                                }
                            }

                            $debug['category_has_responses'] = $categoryHasResponses;

                            // Add hasResponses flag to each category
                            foreach ($tableCategories as $catKey => $category) {
                                $tableCategories[$catKey]['hasResponses'] = $categoryHasResponses[$catKey] ?? false;
                            }
                        }
                    @endphp

                    <!-- Debug output section -->
                    @if(config('app.debug'))
                    <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-lg shadow-sm">
                        <details>
                            <summary class="cursor-pointer font-semibold">Debug Information</summary>
                            <div class="mt-2 text-sm">
                                <p><strong>Survey Template:</strong> {{ $debug['survey_type'] }}</p>
                                <p><strong>Submission Count:</strong> {{ $debug['submission_count'] }}</p>
                                <p><strong>Is Table Survey:</strong> {{ $debug['is_table_survey'] ? 'Yes' : 'No' }}</p>

                                @if($debug['is_table_survey'])
                                    <p><strong>Has Table Stats:</strong> {{ $debug['has_table_stats'] ?? 'No' }}</p>

                                    @if(isset($debug['table_categories_issue']))
                                        <p><strong>Table Categories Issue:</strong> {{ $debug['table_categories_issue'] }}</p>
                                    @endif

                                    @if(isset($debug['table_categories_count']))
                                        <p><strong>Table Categories Count:</strong> {{ $debug['table_categories_count'] }}</p>
                                        <p><strong>Category Keys:</strong> {{ implode(', ', $debug['table_categories_keys']) }}</p>
                                    @endif

                                    @if(isset($debug['category_has_responses']))
                                        <p><strong>Categories with Responses:</strong></p>
                                        <ul class="ml-4 list-disc">
                                            @foreach($debug['category_has_responses'] as $category => $hasResponses)
                                                <li>{{ $category }}: {{ $hasResponses ? 'Has Responses' : 'No Responses' }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @endif

                                <p class="mt-2"><strong>Statistics Data:</strong></p>
                                <pre class="mt-1 text-xs overflow-auto p-2 bg-white dark:bg-gray-800 rounded">{{ json_encode($statisticsData, JSON_PRETTY_PRINT | JSON_PARTIAL_OUTPUT_ON_ERROR) }}</pre>
                            </div>
                        </details>
                    </div>
                    @endif

                    @if(count($statisticsData) > 0)
                        <!-- Test Alpine.js is working -->
                        <div x-data="{ testMessage: 'If you can see this, Alpine.js is working' }" class="mb-4 p-3 bg-green-50 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-lg">
                            <div x-text="testMessage"></div>
                        </div>

                        <!-- First handle table surveys separately if applicable -->
                        @php
                            $tableItem = null;
                            foreach($statisticsData as $stat) {
                                if($stat['template_type'] === 'table') {
                                    $tableItem = $stat;
                                    break;
                                }
                            }
                        @endphp

                        @if($isTableSurvey && $tableItem)
                            <div class="mb-8 p-6 border rounded-lg bg-gray-50 dark:bg-gray-700 shadow-sm hover:shadow-md transition-shadow duration-300">
                                <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-1">Table Survey Results</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Ratings grouped by category</p>

                                <!-- Table survey debug info -->
                                @if(config('app.debug'))
                                    <div class="p-2 bg-teal-50 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-lg mb-2 text-xs">
                                        <div>Table survey special section | Submission count: {{ $tableItem['data']['submission_count'] }}</div>
                                        <div>Categories: {{ implode(', ', array_keys($tableCategories)) }}</div>
                                    </div>
                                @endif

                                @if($tableItem['data']['submission_count'] > 0)
                                    <p class="text-sm mb-4">{{ $tableItem['data']['submission_count'] }} response(s) received</p>

                                    @php
                                        $firstCategoryKey = !empty($tableCategories) ? array_key_first($tableCategories) : 'behavior';
                                    @endphp

                                    <div x-data="{ activeTab: '{{ $firstCategoryKey }}' }" class="mt-6">
                                        <!-- Tabs -->
                                        <div class="border-b border-gray-300 dark:border-gray-600 flex flex-wrap" role="tablist">
                                            @foreach($tableCategories ?? [] as $catKey => $category)
                                                @if(!empty($category['questions'] ?? []))
                                                    <button
                                                        @click="activeTab = '{{ $catKey }}'"
                                                        :class="activeTab === '{{ $catKey }}' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                                        class="px-4 py-2 font-medium border-b-2 -mb-px transition"
                                                        role="tab"
                                                        id="tab-{{ $catKey }}"
                                                        :aria-selected="activeTab === '{{ $catKey }}' ? 'true' : 'false'"
                                                        aria-controls="tabpanel-{{ $catKey }}">
                                                        {{ $category['title'] ?? 'Unknown Category' }}
                                                        @if($category['hasResponses'] ?? false)
                                                            <span class="ml-1 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 text-xs font-semibold px-2 py-0.5 rounded">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline-block w-3 h-3">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                                </svg>
                                                            </span>
                                                        @endif
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>

                                        <!-- Tab Panels -->
                                        @foreach($tableCategories ?? [] as $catKey => $category)
                                            @if(!empty($category['questions'] ?? []))
                                                <div
                                                    x-show="activeTab === '{{ $catKey }}'"
                                                    class="py-4"
                                                    role="tabpanel"
                                                    id="tabpanel-{{ $catKey }}"
                                                    aria-labelledby="tab-{{ $catKey }}">
                                                    <h5 class="font-semibold text-lg mb-3 text-indigo-700 dark:text-indigo-300">{{ $category['title'] ?? 'Unknown Category' }}:</h5>

                                                    @if($category['hasResponses'] ?? false)
                                                        <!-- Questions with Responses -->
                                                        <div class="overflow-x-auto rounded-lg shadow-md mb-4">
                                                            <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
                                                                <thead>
                                                                    <tr>
                                                                        <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-left font-semibold text-gray-700 dark:text-gray-300">Question</th>
                                                                        <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Avg. Rating</th>
                                                                        <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Responses</th>
                                                                        <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300 hidden sm:table-cell">Distribution</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($category['questions'] as $questionStat)
                                                                        @if($questionStat['template_type'] === 'range' &&
                                                                           isset($questionStat['data']['average_rating']) &&
                                                                           is_numeric($questionStat['data']['average_rating']))
                                                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                                <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700">
                                                                                    {{ $questionStat['question']->question }}
                                                                                </td>
                                                                                <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                                                    <span class="text-blue-500 font-medium">{{ $questionStat['data']['average_rating'] }}</span>
                                                                                </td>
                                                                                <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                                                    {{ $questionStat['data']['submission_count'] }}
                                                                                </td>
                                                                                <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 hidden sm:table-cell">
                                                                                    <div class="flex space-x-1">
                                                                                        @foreach($questionStat['data']['rating_counts'] as $rating => $count)
                                                                                            <div class="flex flex-col items-center">
                                                                                                <div class="text-xs {{ $count > 0 ? 'text-blue-500 font-medium' : 'text-gray-400' }}">
                                                                                                    {{ $count }}
                                                                                                </div>
                                                                                                <div class="flex items-end justify-center">
                                                                                                    <div class="w-8 bg-blue-500 rounded-t-sm"
                                                                                                        style="height: {{ max(4, min(24, ($count / array_sum($questionStat['data']['rating_counts'])) * 24)) }}px;
                                                                                                                opacity: {{ 0.5 + ($rating * 0.1) }};">
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="text-xs text-gray-500">{{ $rating }}</div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                        @elseif(($questionStat['template_type'] === 'text' || $questionStat['template_type'] === 'textarea') &&
                                                                              isset($questionStat['data']['response_count']) &&
                                                                              $questionStat['data']['response_count'] > 0)
                                                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                                <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700" colspan="4">
                                                                                    <div class="mb-2 font-medium">{{ $questionStat['question']->question }}</div>
                                                                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $questionStat['data']['response_count'] }} text response(s) received</p>
                                                                                    @if(isset($questionStat['data']['responses']))
                                                                                        <div class="mt-2 space-y-2">
                                                                                            @foreach($questionStat['data']['responses'] as $response)
                                                                                                <div class="p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded">{{ $response }}</div>
                                                                                            @endforeach
                                                                                        </div>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg mb-4">
                                                            <p class="text-gray-600 dark:text-gray-300">No response statistics available for this category yet.</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    <!-- Fallback raw data display for debugging -->
                                    @if(config('app.debug'))
                                        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <h6 class="font-semibold mb-2">Raw Table Categories Data:</h6>
                                            <details>
                                                <summary class="cursor-pointer text-blue-500">Show Raw Data</summary>
                                                <div class="mt-2 overflow-auto" style="max-height: 300px;">
                                                    <pre class="text-xs bg-white dark:bg-gray-800 p-3 rounded">{{ json_encode($tableCategories, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </details>
                                        </div>
                                    @endif
                                @else
                                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-gray-600 dark:text-gray-300">
                                            No responses have been received for this table survey yet.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Now handle all other statistics types -->
                        @foreach($statisticsData as $index => $stat)
                            @if($stat['template_type'] === 'error')
                                <div class="p-4 bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg mb-6 shadow-sm">
                                    <p class="font-semibold">{{ $stat['data']['message'] }}</p>
                                    @if(isset($stat['data']['error_type']))
                                        <p class="text-sm mt-2">Error Type: {{ $stat['data']['error_type'] }}</p>
                                    @endif
                                    <p class="text-sm mt-2">Please try again later or contact support if the problem persists.</p>
                                </div>
                                @break
                            @endif

                            @if($hasTargetStatistics && $stat['template_type'] !== 'target' && str_contains($survey->feedback_template->name ?? '', 'templates.feedback.target'))
                                @continue
                            @endif

                            <!-- Skip table surveys, we handled them separately above -->
                            @if($isTableSurvey && ($stat['template_type'] === 'table' || isset($stat['question'])))
                                @continue
                            @endif

                            <div class="mb-8 p-6 border rounded-lg bg-gray-50 dark:bg-gray-700 shadow-sm hover:shadow-md transition-shadow duration-300">
                                @if($stat['question'])
                                    <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200">{{ $index + 1 }}. {{ $stat['question']->question }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Question Type: {{ ucfirst($stat['template_type']) }}</p>
                                @else
                                    @if($stat['template_type'] === 'target')
                                        <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-1">Target Survey Results</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Segment ratings and distribution</p>
                                    @elseif($stat['template_type'] === 'table')
                                        <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-1">Table Survey Results</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Ratings grouped by category</p>

                                        <!-- Table survey debug info -->
                                        @if(config('app.debug'))
                                            <div class="p-2 bg-teal-50 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-lg mb-2 text-xs">
                                                <div>In table branch | Submission count: {{ $stat['data']['submission_count'] }}</div>
                                                <div>Condition result: {{ ($stat['data']['submission_count'] > 0) ? 'true - should show stats' : 'false - no stats' }}</div>
                                            </div>
                                        @endif
                                    @else
                                        <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-1">Survey Results</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Response summary</p>
                                    @endif
                                @endif

                                <!-- Template type check debugging -->
                                @if(config('app.debug'))
                                    <div class="bg-purple-50 dark:bg-purple-900 text-purple-800 dark:text-purple-200 p-2 mb-3 text-xs rounded">
                                        Template type: {{ $stat['template_type'] }} |
                                        @if($stat['template_type'] === 'table')
                                            Is Table Survey: {{ $isTableSurvey ? 'Yes' : 'No' }} |
                                            Submission Count: {{ $stat['data']['submission_count'] ?? '0' }} |
                                            tableCategories Count: {{ count($tableCategories ?? []) }}
                                        @endif
                                    </div>
                                @endif

                                @if($stat['template_type'] === 'range')
                                    <div class="mt-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <p class="font-medium">Average Rating:
                                                    @if(is_numeric($stat['data']['average_rating']))
                                                        <span class="text-blue-500">{{ $stat['data']['average_rating'] }}</span>
                                                    @else
                                                        <span class="text-gray-500">{{ $stat['data']['average_rating'] }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div>
                                                <p class="font-medium">Median Rating:
                                                    @if(is_numeric($stat['data']['median_rating']))
                                                        <span class="text-blue-500">{{ $stat['data']['median_rating'] }}</span>
                                                    @else
                                                        <span class="text-gray-500">{{ $stat['data']['median_rating'] }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        @if(!empty($stat['data']['rating_counts']))
                                            <div class="mt-4">
                                                <p class="font-medium mb-3">Rating Distribution:</p>
                                                <div class="space-y-3">
                                                    @foreach($stat['data']['rating_counts'] as $rating => $count)
                                                        <div class="flex items-center">
                                                            <span class="w-8 text-right mr-3 font-medium">{{ $rating }}:</span>
                                                            <div class="h-6 bg-blue-500 rounded-full shadow-sm" style="width: {{ min(100, ($count / array_sum($stat['data']['rating_counts'])) * 100) }}%"></div>
                                                            <span class="ml-3">{{ $count }} response(s) ({{ round(($count / array_sum($stat['data']['rating_counts'])) * 100, 1) }}%)</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @elseif($stat['template_type'] === 'checkboxes')
                                    @if(!empty($stat['data']['option_counts']))
                                        <div class="mt-4">
                                            <p class="font-medium mb-3">Option Selections:</p>
                                            <div class="space-y-3">
                                                @foreach($stat['data']['option_counts'] as $option => $count)
                                                    <div class="flex items-center">
                                                        <span class="w-24 truncate mr-3">{{ $option }}:</span>
                                                        <div class="h-6 bg-green-500 rounded-full shadow-sm" style="width: {{ min(100, ($count / array_sum($stat['data']['option_counts'])) * 100) }}%"></div>
                                                        <span class="ml-3">{{ $count }} selection(s)</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-gray-500">No responses yet</p>
                                    @endif
                                @elseif(in_array($stat['template_type'], ['textarea', 'text']))
                                    <p class="mt-3">
                                        <span class="font-medium">Text Responses:</span>
                                        {{ $stat['data']['response_count'] }} response(s)
                                    </p>
                                @elseif(in_array($stat['template_type'], ['target', 'smiley', 'checkbox']))
                                    <div class="mt-3">
                                        <p class="font-medium mb-2">Complex Response Data:</p>

                                        <!-- Target template specific handling -->
                                        @if($stat['template_type'] === 'target')
                                            @if(isset($stat['data']['segment_statistics']) && !empty($stat['data']['segment_statistics']))
                                                <p class="text-sm mb-4">{{ $stat['data']['submission_count'] }} response(s) received</p>
                                                <div class="mt-4">
                                                    <h5 class="font-semibold text-lg mb-3 text-indigo-700 dark:text-indigo-300">Segment Ratings:</h5>
                                                    <div class="overflow-x-auto rounded-lg shadow-md">
                                                        <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
                                                            <thead>
                                                                <tr>
                                                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-left font-semibold text-gray-700 dark:text-gray-300">Segment</th>
                                                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Avg. Rating</th>
                                                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Responses</th>
                                                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Distribution</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($stat['data']['segment_statistics'] as $segmentStat)
                                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700">
                                                                            {{ $segmentStat['statement'] }}
                                                                        </td>
                                                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                                            <span class="text-blue-500 font-medium">{{ $segmentStat['average_rating'] }}</span>
                                                                        </td>
                                                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                                            {{ $segmentStat['response_count'] }}
                                                                        </td>
                                                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700">
                                                                            <div class="flex justify-between items-end w-full" style="min-height: 60px;">
                                                                                @php
                                                                                    $allRatings = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                                                                                    foreach($segmentStat['rating_counts'] as $rating => $count) {
                                                                                        $allRatings[$rating] = $count;
                                                                                    }
                                                                                    $maxCount = max(1, max($allRatings));
                                                                                @endphp
                                                                                <div class="grid grid-cols-5 gap-1 w-full">
                                                                                    @for($rating = 1; $rating <= 5; $rating++)
                                                                                        <div class="flex flex-col items-center">
                                                                                            <!-- Count number at the top -->
                                                                                            <div class="text-xs {{ $allRatings[$rating] > 0 ? 'text-blue-500 font-medium' : 'text-gray-400' }} h-4 flex items-center justify-center mb-1">
                                                                                                {{ $allRatings[$rating] }}
                                                                                            </div>
                                                                                            <!-- Container for proper alignment - crucial for upward bars -->
                                                                                            <div class="flex items-end justify-center h-[28px]">
                                                                                                <!-- Bar grows UPWARD from bottom -->
                                                                                                <div class="w-5 bg-blue-500 rounded-t-sm"
                                                                                                     style="height: {{ max(4, min(24, ($allRatings[$rating] / $maxCount) * 24)) }}px;
                                                                                                            opacity: {{ $allRatings[$rating] > 0 ? 0.5 + ($rating * 0.1) : 0.2 }};">
                                                                                                </div>
                                                                                            </div>
                                                                                            <!-- Rating number below bar -->
                                                                                            <div class="text-xs text-gray-500 h-4 flex items-center justify-center mt-1">
                                                                                                {{ $rating }}
                                                                                            </div>
                                                                                        </div>
                                                                                    @endfor
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-lg">
                                                    <p>Processing target survey data...</p>
                                                    <p class="text-sm mt-2">If you continue to see this message, please try refreshing the page.</p>
                                                </div>
                                            @endif

                                        <!-- Table template specific handling -->
                                        @elseif($stat['template_type'] === 'table')
                                            <!-- Table survey debug info -->
                                            @if(config('app.debug'))
                                                <div class="p-2 bg-teal-50 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-lg mb-2 text-xs">
                                                    <div>In table branch | Submission count: {{ $stat['data']['submission_count'] }}</div>
                                                    <div>Condition result: {{ ($stat['data']['submission_count'] > 0) ? 'true - should show stats' : 'false - no stats' }}</div>
                                                </div>
                                            @endif

                                            @if($stat['data']['submission_count'] > 0)
                                                <p class="text-sm mb-4">{{ $stat['data']['submission_count'] }} response(s) received</p>

                                                <!-- Table categories debug -->
                                                @if(config('app.debug') && empty($tableCategories))
                                                    <div class="p-2 bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg mb-2 text-xs">
                                                        WARNING: tableCategories is empty despite having submissions!
                                                    </div>
                                                @endif

                                                @php
                                                    $firstCategoryKey = !empty($tableCategories) ? array_key_first($tableCategories) : 'behavior';
                                                @endphp

                                                <div x-data="{ activeTab: '{{ $firstCategoryKey }}' }" class="mt-6">
                                                    <!-- Tabs -->
                                                    @if(config('app.debug'))
                                                    <div class="p-2 bg-indigo-50 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded-lg mb-2 text-xs">
                                                        <div>Table Categories Count: {{ count($tableCategories) }}</div>
                                                        <div>First Category Key: {{ $firstCategoryKey }}</div>
                                                        <div>Table Categories Keys: {{ implode(', ', array_keys($tableCategories)) }}</div>
                                                    </div>
                                                    @endif

                                                    <div class="border-b border-gray-300 dark:border-gray-600 flex flex-wrap" role="tablist">
                                                        @foreach($tableCategories ?? [] as $catKey => $category)
                                                            @if(!empty($category['questions'] ?? []))
                                                                <button
                                                                    @click="activeTab = '{{ $catKey }}'"
                                                                    :class="activeTab === '{{ $catKey }}' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                                                                    class="px-4 py-2 font-medium border-b-2 -mb-px transition"
                                                                    role="tab"
                                                                    id="tab-{{ $catKey }}"
                                                                    :aria-selected="activeTab === '{{ $catKey }}' ? 'true' : 'false'"
                                                                    aria-controls="tabpanel-{{ $catKey }}">
                                                                    {{ $category['title'] ?? 'Unknown Category' }}
                                                                    @if($category['hasResponses'] ?? false)
                                                                        <span class="ml-1 bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 text-xs font-semibold px-2 py-0.5 rounded">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="inline-block w-3 h-3">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                                            </svg>
                                                                        </span>
                                                                    @endif
                                                                </button>
                                                            @endif
                                                        @endforeach
                                                    </div>

                                                    <!-- Tab Panels -->
                                                    @foreach($tableCategories ?? [] as $catKey => $category)
                                                        @if(!empty($category['questions'] ?? []))
                                                            <div
                                                                x-show="activeTab === '{{ $catKey }}'"
                                                                class="py-4"
                                                                role="tabpanel"
                                                                id="tabpanel-{{ $catKey }}"
                                                                aria-labelledby="tab-{{ $catKey }}">
                                                                <h5 class="font-semibold text-lg mb-3 text-indigo-700 dark:text-indigo-300">{{ $category['title'] ?? 'Unknown Category' }}:</h5>

                                                                @if($category['hasResponses'] ?? false)
                                                                    <!-- Questions with Responses -->
                                                                    <div class="overflow-x-auto rounded-lg shadow-md mb-4">
                                                                        <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-left font-semibold text-gray-700 dark:text-gray-300">Question</th>
                                                                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Avg. Rating</th>
                                                                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Responses</th>
                                                                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300 hidden sm:table-cell">Distribution</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($category['questions'] as $questionStat)
                                                                                    @if($questionStat['template_type'] === 'range' &&
                                                                                       isset($questionStat['data']['average_rating']) &&
                                                                                       is_numeric($questionStat['data']['average_rating']))
                                                                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                                            <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700">
                                                                                                {{ $questionStat['question']->question }}
                                                                                            </td>
                                                                                            <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                                                                <span class="text-blue-500 font-medium">{{ $questionStat['data']['average_rating'] }}</span>
                                                                                            </td>
                                                                                            <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                                                                {{ $questionStat['data']['submission_count'] }}
                                                                                            </td>
                                                                                            <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 hidden sm:table-cell">
                                                                                                <div class="flex space-x-1">
                                                                                                    @foreach($questionStat['data']['rating_counts'] as $rating => $count)
                                                                                                        <div class="flex flex-col items-center">
                                                                                                            <div class="text-xs {{ $count > 0 ? 'text-blue-500 font-medium' : 'text-gray-400' }}">
                                                                                                                {{ $count }}
                                                                                                            </div>
                                                                                                            <div class="flex items-end justify-center">
                                                                                                                <div class="w-8 bg-blue-500 rounded-t-sm"
                                                                                                                    style="height: {{ max(4, min(24, ($count / array_sum($questionStat['data']['rating_counts'])) * 24)) }}px;
                                                                                                                            opacity: {{ 0.5 + ($rating * 0.1) }};">
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="text-xs text-gray-500">{{ $rating }}</div>
                                                                                                    @endforeach
                                                                                                </div>
                                                                                            </td>
                                                                                        </tr>
                                                                                    @elseif(($questionStat['template_type'] === 'text' || $questionStat['template_type'] === 'textarea') &&
                                                                                          isset($questionStat['data']['response_count']) &&
                                                                                          $questionStat['data']['response_count'] > 0)
                                                                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                                            <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700" colspan="4">
                                                                                                <div class="mb-2 font-medium">{{ $questionStat['question']->question }}</div>
                                                                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $questionStat['data']['response_count'] }} text response(s) received</p>
                                                                                                @if(isset($questionStat['data']['responses']))
                                                                                                    <div class="mt-2 space-y-2">
                                                                                                        @foreach($questionStat['data']['responses'] as $response)
                                                                                                            <div class="p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded">{{ $response }}</div>
                                                                                                        @endforeach
                                                                                                    </div>
                                                                                                @endif
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endif
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                @else
                                                                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg mb-4">
                                                                        <p class="text-gray-600 dark:text-gray-300">No response statistics available for this category yet.</p>
                                                                    </div>
                                                                @endif

                                                                <!-- Toggle for Questions with No Responses -->
                                                                @php
                                                                    $hasNoResponseQuestions = false;
                                                                    foreach($category['questions'] as $questionStat) {
                                                                        if($questionStat['template_type'] === 'range' &&
                                                                          (!isset($questionStat['data']['average_rating']) ||
                                                                           !is_numeric($questionStat['data']['average_rating']))) {
                                                                            $hasNoResponseQuestions = true;
                                                                            break;
                                                                        }
                                                                    }
                                                                @endphp

                                                                @if($hasNoResponseQuestions)
                                                                    <div x-data="{ showNoResponses: false }" class="mt-4">
                                                                        <button
                                                                            @click="showNoResponses = !showNoResponses"
                                                                            class="flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                                                            aria-expanded="false"
                                                                            :aria-expanded="showNoResponses ? 'true' : 'false'"
                                                                            aria-controls="no-responses-{{ $catKey }}">
                                                                            <span x-text="showNoResponses ? 'Hide' : 'Show'"></span> questions with no responses
                                                                            <svg x-show="!showNoResponses" xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                                            </svg>
                                                                            <svg x-show="showNoResponses" xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                                            </svg>
                                                                        </button>

                                                                        <div
                                                                            x-show="showNoResponses"
                                                                            class="mt-3 overflow-x-auto rounded-lg"
                                                                            id="no-responses-{{ $catKey }}"
                                                                            x-cloak>
                                                                            <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 opacity-50">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th class="py-2 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-left font-semibold text-gray-700 dark:text-gray-300">Question</th>
                                                                                        <th class="py-2 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @foreach($category['questions'] as $questionStat)
                                                                                        @if($questionStat['template_type'] === 'range' &&
                                                                                          (!isset($questionStat['data']['average_rating']) ||
                                                                                           !is_numeric($questionStat['data']['average_rating'])))
                                                                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                                                                                <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700">
                                                                                                    {{ $questionStat['question']->question }}
                                                                                                </td>
                                                                                                <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                                                                    <span class="text-gray-500">No responses</span>
                                                                                                </td>
                                                                                            </tr>
                                                                                        @endif
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                    <p class="text-gray-600 dark:text-gray-300">
                                                        No responses have been received for this table survey yet.
                                                    </p>
                                                </div>
                                            @endif

                                        <!-- Other template types that use json_responses -->
                                        @elseif(!empty($stat['data']['json_responses']))
                                            <p class="text-sm mb-4">{{ count($stat['data']['json_responses']) }} response(s) received</p>

                                            <details class="mt-6 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                                                <summary class="cursor-pointer text-blue-500 font-medium">View Raw Data</summary>
                                                <pre class="mt-3 p-3 bg-gray-100 dark:bg-gray-800 rounded text-xs overflow-x-auto">{{ json_encode($stat['data']['json_responses'], JSON_PRETTY_PRINT) }}</pre>
                                            </details>
                                        @else
                                            <p class="text-gray-500">No responses yet</p>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-gray-500 mt-3">{{ $stat['data']['message'] ?? 'No statistics available for this question type.' }}</p>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-lg shadow-sm">
                            <p>No questions found for this survey.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>