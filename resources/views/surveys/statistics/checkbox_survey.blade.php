{{-- Checkbox Survey Statistics Component --}}

<!-- Calculate statistics for checkbox questions -->
@php
    // Group questions by type
    $checkboxQuestions = $survey->questions->where(function($q) {
        return $q->question_template && $q->question_template->type === 'checkbox';
    })->sortBy('order');

    // Get text/open feedback questions
    $textQuestions = $survey->questions->where(function($q) {
        return $q->question_template && $q->question_template->type === 'text';
    })->sortBy('order');

    // Add the special feedback field if it exists
    $openFeedback = collect();
    foreach ($survey->questions as $question) {
        $feedbackResponses = $question->results->where('question_id', $question->id)
            ->where(function($result) {
                // Look for feedback in various formats
                return $result->rating_value === 'feedback' ||
                       $result->rating_value === 'comments' ||
                       stripos($result->rating_value, 'feedback') !== false ||
                       stripos($result->rating_value, 'kommentar') !== false ||
                       (isset($result->value_type) && $result->value_type === 'text');
            })
            ->pluck('rating_value')
            ->filter()
            ->values();

        if ($feedbackResponses->count() > 0) {
            $openFeedback = $openFeedback->merge($feedbackResponses);
        }
    }

    // Check if there are any text questions or open feedback responses
    $hasOpenFeedback = $textQuestions->isNotEmpty() || $openFeedback->isNotEmpty();

    // Additional check for open text responses in the statistics data
    if (!$hasOpenFeedback) {
        foreach ($statisticsData as $stat) {
            if (isset($stat['data']['responses']) && !empty($stat['data']['responses'])) {
                $hasOpenFeedback = true;
                break;
            }
        }
    }

    // Get all submissions for this survey
    $submissionCount = 0;
    try {
        $submissionCount = $survey->getUniqueSubmissionsCount();
    } catch (\Exception $e) {
        // Fallback: Get count from results directly
        $submissionCount = $survey->questions->flatMap->results->pluck('submission_id')->unique()->count();
    }
@endphp

<!-- Tab navigation -->
<div class="border-b border-gray-200 dark:border-gray-700 mb-6">
    <ul class="flex flex-wrap -mb-px" id="checkboxTabs">
        <li class="mr-2">
            <button
                onclick="showCheckboxTab('results')"
                class="inline-block p-4 text-indigo-600 border-b-2 border-indigo-600 rounded-t-lg active"
                id="results-tab"
            >
                {{ __('surveys.question_results') }}
            </button>
        </li>
        <li class="mr-2">
            <button
                onclick="showCheckboxTab('feedback')"
                class="inline-block p-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 border-b-2 border-transparent rounded-t-lg"
                id="feedback-tab"
            >
                {{ __('surveys.open_feedback') }}
            </button>
        </li>
    </ul>
</div>

<!-- Tab content -->
<div class="space-y-6">
    <!-- Checkbox Results Tab -->
    <div id="results-content" class="block">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($checkboxQuestions as $question)
                @php
                    // Find statistics for this question in the array instead of using firstWhere collection method
                    $questionStats = null;
                    foreach ($statisticsData as $stats) {
                        if (isset($stats['question']) && isset($stats['question']['id']) && $stats['question']['id'] === $question->id) {
                            $questionStats = $stats;
                            break;
                        }
                    }
                @endphp
                @if($questionStats && isset($questionStats['data']['option_counts']))
                    <div class="p-6 border rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <h4 class="font-semibold text-lg mb-4 text-gray-800 dark:text-gray-200">
                            {{ $question->question }}
                        </h4>

                        @include('surveys.statistics.components.checkbox_distribution', [
                            'optionCounts' => $questionStats['data']['option_counts'],
                            'submissionCount' => $questionStats['data']['submission_count'] ?? $submissionCount
                        ])
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Open Feedback Tab - Always include the tab content section -->
    <div id="feedback-content" class="hidden">
        <div class="space-y-6">
            @if($textQuestions->isNotEmpty() || !empty($openFeedback))
                @foreach($textQuestions as $question)
                    @php
                        // Find statistics for this question in the array instead of using firstWhere collection method
                        $questionStats = null;
                        foreach ($statisticsData as $stats) {
                            if (isset($stats['question']) && isset($stats['question']['id']) && $stats['question']['id'] === $question->id) {
                                $questionStats = $stats;
                                break;
                            }
                        }
                    @endphp
                    @if($questionStats && isset($questionStats['data']['responses']))
                        <div class="p-6 border rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow duration-300">
                            <h4 class="font-semibold text-lg mb-4 text-gray-800 dark:text-gray-200">
                                {{ $question->question }}
                            </h4>

                            @include('surveys.statistics.components.open_feedback', [
                                'responses' => $questionStats['data']['responses']
                            ])
                        </div>
                    @endif
                @endforeach

                @if($openFeedback->isNotEmpty())
                    <div class="p-6 border rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <h4 class="font-semibold text-lg mb-4 text-gray-800 dark:text-gray-200">
                            {{ __('surveys.additional_comments') }}
                        </h4>

                        @include('surveys.statistics.components.open_feedback', [
                            'responses' => $openFeedback->toArray()
                        ])
                    </div>
                @endif
            @else
                <!-- If there's no feedback data, show a message -->
                <div class="p-6 border rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <p class="text-gray-500 dark:text-gray-400">{{ __('surveys.no_feedback_responses') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Tab switching script -->
<script>
    function showCheckboxTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('[id$="-content"]').forEach(tab => {
            tab.classList.add('hidden');
            tab.classList.remove('block');
        });

        // Show the selected tab content
        document.getElementById(tabName + '-content').classList.remove('hidden');
        document.getElementById(tabName + '-content').classList.add('block');

        // Update tab button styles
        document.querySelectorAll('[id$="-tab"]').forEach(button => {
            button.classList.remove('text-indigo-600', 'border-indigo-600', 'active');
            button.classList.add('text-gray-500', 'border-transparent');
        });

        // Set active tab button style
        document.getElementById(tabName + '-tab').classList.remove('text-gray-500', 'border-transparent');
        document.getElementById(tabName + '-tab').classList.add('text-indigo-600', 'border-indigo-600', 'active');
    }
</script>