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

                    @php
                        // Initialize variables
                        $isTableSurvey = false;
                        $tableCategories = [];
                        $isTargetTemplate = false;

                        // Process the statistics data
                        foreach($statisticsData as $stat) {
                            // Check for table type statistics
                            if ($stat['template_type'] === 'table' && isset($stat['data']['table_survey']) && $stat['data']['table_survey'] === true) {
                                $isTableSurvey = true;

                                if (isset($stat['data']['table_categories']) && is_array($stat['data']['table_categories'])) {
                                    $tableCategories = $stat['data']['table_categories'];
                                }
                            }

                            // Check for target type statistics
                            if ($stat['template_type'] === 'target') {
                                $isTargetTemplate = true;
                            }
                        }

                        // Process tableCategories to add hasResponses flag
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

                            // Add hasResponses flag to each category
                            foreach ($tableCategories as $catKey => $category) {
                                $tableCategories[$catKey]['hasResponses'] = $categoryHasResponses[$catKey] ?? false;
                            }
                        }
                    @endphp

                    @if(count($statisticsData) > 0)
                        <!-- Test Alpine.js is working -->
                        <div x-data="{ testMessage: 'If you can see this, Alpine.js is working' }" class="hidden">
                            <div x-text="testMessage"></div>
                        </div>

                        <!-- Handle table surveys -->
                        @if($isTableSurvey)
                            @include('surveys.statistics.table_survey')
                        @endif

                        <!-- Handle target surveys -->
                        @if($isTargetTemplate)
                            @include('surveys.statistics.target_survey')
                        @endif

                        <!-- Display statistics for non-table surveys -->
                        @if(!$isTableSurvey)
                            @include('surveys.statistics.default_survey')
                        @endif
                    @else
                        <div class="p-6 border rounded-lg bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                            <h4 class="text-lg font-semibold mb-2">No Responses Yet</h4>
                            <p>This survey hasn't received any responses yet. Statistics will be displayed once responses are submitted.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>