<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Survey Statistics') }} - {{ $survey->feedback_template->title ?? 'Survey' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <a href="{{ route('dashboard') }}" class="flex flex-row gap-2 items-center w-fit text-lg px-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 dark:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-500 dark:text-gray-400">{{ __('Back to Dashboard') }}</span>
                        </a>
                    </div>

                    <h3 class="text-xl font-semibold mb-4">{{ __('Survey Details') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <p><span class="font-semibold">Survey Title:</span> {{ $survey->feedback_template->title ?? 'N/A' }}</p>
                            <p><span class="font-semibold">Access Key:</span> {{ $survey->accesskey }}</p>
                            <p><span class="font-semibold">Created:</span> {{ $survey->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <p><span class="font-semibold">Responses:</span> {{ $survey->already_answered }} / {{ $survey->limit == -1 ? '∞' : $survey->limit }}</p>
                            <p><span class="font-semibold">Expires:</span> {{ $survey->expire_date->format('M d, Y') }}</p>
                            <p><span class="font-semibold">Status:</span>
                                @if($survey->expire_date->isPast())
                                    <span class="text-red-500">Expired</span>
                                @else
                                    <span class="text-green-500">Active</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <h3 class="text-xl font-semibold mt-8 mb-4">{{ __('Question Statistics') }}</h3>

                    @if(count($statisticsData) > 0)
                        @php
                            // Check if this is a target survey and has target statistics
                            $hasTargetStatistics = false;
                            foreach($statisticsData as $stat) {
                                if($stat['template_type'] === 'target' && isset($stat['data']['segment_statistics'])) {
                                    $hasTargetStatistics = true;
                                    break;
                                }
                            }
                        @endphp

                        @foreach($statisticsData as $index => $stat)
                            @if($stat['template_type'] === 'error')
                                <div class="p-4 bg-red-50 dark:bg-red-900 text-red-800 dark:text-red-200 rounded-lg mb-6">
                                    <p>{{ $stat['data']['message'] }}</p>
                                    <p class="text-sm mt-2">Please try again later or contact support if the problem persists.</p>
                                </div>
                                @break
                            @endif

                            @if($hasTargetStatistics && $stat['template_type'] !== 'target' && str_contains($survey->feedback_template->name ?? '', 'templates.feedback.target'))
                                @continue
                            @endif

                            <div class="mb-6 p-4 border rounded-lg bg-gray-50 dark:bg-gray-700">
                                @if($stat['question'])
                                    <h4 class="font-semibold text-lg">{{ $index + 1 }}. {{ $stat['question']->question }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Question Type: {{ ucfirst($stat['template_type']) }}</p>
                                @else
                                    <h4 class="font-semibold text-lg">Target Survey Results</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Segment ratings and distribution</p>
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
                                            <div class="mt-3">
                                                <p class="font-medium mb-2">Rating Distribution:</p>
                                                <div class="space-y-2">
                                                    @foreach($stat['data']['rating_counts'] as $rating => $count)
                                                        <div class="flex items-center">
                                                            <span class="w-8 text-right mr-2">{{ $rating }}:</span>
                                                            <div class="h-5 bg-blue-500 rounded" style="width: {{ min(100, ($count / array_sum($stat['data']['rating_counts'])) * 100) }}%"></div>
                                                            <span class="ml-2">{{ $count }} response(s) ({{ round(($count / array_sum($stat['data']['rating_counts'])) * 100, 1) }}%)</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @elseif($stat['template_type'] === 'checkboxes')
                                    @if(!empty($stat['data']['option_counts']))
                                        <div class="mt-3">
                                            <p class="font-medium mb-2">Option Selections:</p>
                                            <div class="space-y-2">
                                                @foreach($stat['data']['option_counts'] as $option => $count)
                                                    <div class="flex items-center">
                                                        <span class="w-24 truncate mr-2">{{ $option }}:</span>
                                                        <div class="h-5 bg-green-500 rounded" style="width: {{ min(100, ($count / array_sum($stat['data']['option_counts'])) * 100) }}%"></div>
                                                        <span class="ml-2">{{ $count }} selection(s)</span>
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
                                @elseif(in_array($stat['template_type'], ['target', 'smiley', 'table', 'checkbox']))
                                    <div class="mt-3">
                                        <p class="font-medium mb-2">Complex Response Data:</p>
                                        @if(!empty($stat['data']['json_responses']))
                                            <p class="text-sm">{{ count($stat['data']['json_responses']) }} response(s) received</p>

                                            @if($stat['template_type'] === 'target' && isset($stat['data']['segment_statistics']))
                                                <div class="mt-4">
                                                    <h5 class="font-medium mb-2">Segment Ratings:</h5>
                                                    <div class="overflow-x-auto">
                                                        <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
                                                            <thead>
                                                                <tr>
                                                                    <th class="py-2 px-4 border-b border-gray-300 dark:border-gray-700 text-left">Segment</th>
                                                                    <th class="py-2 px-4 border-b border-gray-300 dark:border-gray-700 text-center">Avg. Rating</th>
                                                                    <th class="py-2 px-4 border-b border-gray-300 dark:border-gray-700 text-center">Responses</th>
                                                                    <th class="py-2 px-4 border-b border-gray-300 dark:border-gray-700 text-center">Distribution</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($stat['data']['segment_statistics'] as $segmentStat)
                                                                    <tr>
                                                                        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700">
                                                                            {{ $segmentStat['statement'] }}
                                                                        </td>
                                                                        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                                            <span class="text-blue-500 font-medium">{{ $segmentStat['average_rating'] }}</span>
                                                                        </td>
                                                                        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                                            {{ $segmentStat['response_count'] }}
                                                                        </td>
                                                                        <td class="py-2 px-4 border-b border-gray-300 dark:border-gray-700">
                                                                            <div class="flex items-center justify-center space-x-1">
                                                                                @foreach($segmentStat['rating_counts'] as $rating => $count)
                                                                                    @if($segmentStat['response_count'] > 0)
                                                                                        <div class="tooltip-container relative" title="Rating {{ $rating }}: {{ $count }} responses">
                                                                                            <div class="h-5 bg-blue-500 rounded" style="width: {{ max(5, ($count / $segmentStat['response_count']) * 100) }}px; opacity: {{ 0.2 + ($rating * 0.15) }};"></div>
                                                                                            <div class="absolute top-6 text-xs">{{ $rating }}</div>
                                                                                        </div>
                                                                                    @else
                                                                                        <div class="h-5 bg-gray-300 dark:bg-gray-700 rounded" style="width: 5px;"></div>
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @elseif($stat['template_type'] === 'target' && !isset($stat['data']['segment_statistics']))
                                                <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-lg">
                                                    <p>Processing target survey data...</p>
                                                    <p class="text-sm mt-2">If you continue to see this message, please try refreshing the page.</p>
                                                </div>
                                            @endif

                                            <details class="mt-4">
                                                <summary class="cursor-pointer text-blue-500">View Raw Data</summary>
                                                <pre class="mt-2 p-2 bg-gray-100 dark:bg-gray-800 rounded text-xs overflow-x-auto">{{ json_encode($stat['data']['json_responses'], JSON_PRETTY_PRINT) }}</pre>
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
                        <div class="p-4 bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-lg">
                            <p>No questions found for this survey.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>