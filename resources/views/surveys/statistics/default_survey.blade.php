{{-- Regular Survey Statistics Component --}}

<!-- Calculate the maximum rating count across all questions with range template type -->
@php
    $maxRatingCount = 0;
    // Find the maximum count across all questions
    foreach ($statisticsData as $statItem) {
        if ($statItem['template_type'] === 'range' && isset($statItem['data']['rating_counts']) && is_array($statItem['data']['rating_counts'])) {
            foreach ($statItem['data']['rating_counts'] as $ratingValue => $count) {
                $maxRatingCount = max($maxRatingCount, $count);
            }
        }
    }
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach($statisticsData as $stat)
        @if(isset($stat['question']) && $stat['question'])
            <div class="p-6 border rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow duration-300">
                <h4 class="font-semibold text-lg mb-4 text-gray-800 dark:text-gray-200">
                    {{ $stat['question']->question }}
                </h4>

                @if($stat['template_type'] === 'range')
                    @if(isset($stat['data']['average_rating']) && is_numeric($stat['data']['average_rating']))
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Average Rating</p>
                            <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stat['data']['average_rating'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                from {{ $stat['data']['submission_count'] }} response(s)
                            </p>
                        </div>

                        @if(isset($stat['data']['rating_counts']) && count($stat['data']['rating_counts']) > 0)
                            <div class="mt-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rating Distribution</p>
                                @include('surveys.statistics.components.rating_distribution', [
                                    'ratingCounts' => $stat['data']['rating_counts'] ?? [],
                                    'maxValue' => $maxRatingCount
                                ])
                            </div>
                        @endif
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No ratings received yet.</p>
                    @endif
                @elseif($stat['template_type'] === 'text' || $stat['template_type'] === 'textarea')
                    @if(isset($stat['data']['responses']) && count($stat['data']['responses']) > 0)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                            {{ count($stat['data']['responses']) }} text response(s) received
                        </p>
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            @foreach($stat['data']['responses'] as $response)
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    {{ $response }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No text responses received yet.</p>
                    @endif
                @elseif($stat['template_type'] === 'checkbox' || $stat['template_type'] === 'checkboxes')
                    @if(isset($stat['data']['option_counts']) && count($stat['data']['option_counts']) > 0)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                            {{ $stat['data']['submission_count'] }} response(s) received
                        </p>
                        <div class="space-y-3">
                            @foreach($stat['data']['option_counts'] as $option => $count)
                                <div class="flex items-center">
                                    <div class="w-32 truncate text-sm">{{ $option }}</div>
                                    <div class="flex-1 ml-2">
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                            <div class="bg-indigo-600 dark:bg-indigo-500 h-2.5 rounded-full"
                                                 style="width: {{ ($count / max(1, $stat['data']['submission_count'])) * 100 }}%">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-2 text-sm font-medium">{{ $count }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No checkbox selections received yet.</p>
                    @endif
                @endif
            </div>
        @endif
    @endforeach
</div>