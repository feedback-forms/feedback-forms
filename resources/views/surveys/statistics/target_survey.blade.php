{{-- Target Survey Statistics Component --}}
@php
    $targetItem = null;
    foreach($statisticsData as $stat) {
        if($stat['template_type'] === 'target') {
            $targetItem = $stat;
            break;
        }
    }
@endphp

@if($targetItem && isset($targetItem['data']['segment_statistics']))
    <div class="mb-8 p-6 border rounded-lg bg-gray-50 dark:bg-gray-700 shadow-sm hover:shadow-md transition-shadow duration-300">
        <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-1">{{ __('surveys.target_survey_results') }}</h4>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('surveys.segment_ratings_description') }}</p>

        @if($submissionCount > 0)
            <p class="text-sm mb-4">{{ __('surveys.responses_count', ['count' => $submissionCount]) }}</p>

            <!-- Calculate the maximum rating count across all segments -->
            @php
                $maxRatingCount = 0;
                // Find the maximum count across all segments
                foreach ($targetItem['data']['segment_statistics'] as $segment) {
                    if (isset($segment['rating_counts']) && is_array($segment['rating_counts'])) {
                        foreach ($segment['rating_counts'] as $ratingValue => $count) {
                            $maxRatingCount = max($maxRatingCount, $count);
                        }
                    }
                }

                // Get open feedback responses
                $openFeedbackResponses = collect();
                foreach($statisticsData as $stat) {
                    if(isset($stat['question']) && $stat['question'] &&
                       $stat['template_type'] === 'text' &&
                       $stat['question']->question === 'Open Feedback' &&
                       isset($stat['data']['responses'])) {
                        $openFeedbackResponses = collect($stat['data']['responses']);
                        break;
                    }
                }

                // Setup tabs
                $hasFeedback = $openFeedbackResponses->count() > 0;
            @endphp

            <div x-data="{ activeTab: 'target' }" class="mt-6">
                <!-- Tabs -->
                <div class="border-b border-gray-300 dark:border-gray-600 flex flex-wrap" role="tablist" aria-label="{{ __('surveys.target_survey_results') }}">
                    <button
                        @click="activeTab = 'target'"
                        :class="activeTab === 'target' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="px-4 py-2 font-medium text-sm focus:outline-none"
                        role="tab"
                        id="tab-target"
                        aria-controls="panel-target"
                        :aria-selected="activeTab === 'target'"
                        :tabindex="activeTab === 'target' ? 0 : -1"
                    >
                        {{ __('surveys.target_results') }}
                    </button>

                    <button
                        @click="activeTab = 'feedback'"
                        :class="activeTab === 'feedback' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="px-4 py-2 font-medium text-sm focus:outline-none"
                        role="tab"
                        id="tab-feedback"
                        aria-controls="panel-feedback"
                        :aria-selected="activeTab === 'feedback'"
                        :tabindex="activeTab === 'feedback' ? 0 : -1"
                    >
                        {{ __('surveys.open_feedback') }}
                        @if(!$hasFeedback)
                            <span class="text-xs text-red-500">({{ __('surveys.no_responses_yet') }})</span>
                        @endif
                    </button>
                </div>

                <!-- Target Results Tab -->
                <div
                    x-show="activeTab === 'target'"
                    class="py-4"
                    id="panel-target"
                    role="tabpanel"
                    aria-labelledby="tab-target"
                    :hidden="activeTab !== 'target'"
                >
                    <!-- Target Segments Table -->
                    <div class="overflow-x-auto rounded-lg shadow-md">
                        <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-left font-semibold text-gray-700 dark:text-gray-300">{{ __('surveys.segment') }}</th>
                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">{{ __('surveys.average_rating_short') }}</th>
                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">{{ __('surveys.responses') }}</th>
                                    <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300 hidden sm:table-cell">{{ __('surveys.distribution') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($targetItem['data']['segment_statistics'] as $segmentStat)
                                    @if(isset($segmentStat['statement']) && $segmentStat['statement'] !== 'Open Feedback')
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700">
                                            {{ $segmentStat['statement'] }}
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                            @if(is_numeric($segmentStat['average_rating']))
                                                <span class="text-blue-500 font-medium">{{ $segmentStat['average_rating'] }}</span>
                                            @else
                                                <span class="text-gray-500">{{ $segmentStat['average_rating'] }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                            {{ $segmentStat['submission_count'] }}
                                        </td>
                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 hidden sm:table-cell">
                                            @include('surveys.statistics.components.rating_distribution', [
                                                'ratingCounts' => $segmentStat['rating_counts'] ?? [],
                                                'compactView' => true,
                                                'maxValue' => $maxRatingCount
                                            ])
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Open Feedback Tab -->
                <div
                    x-show="activeTab === 'feedback'"
                    class="py-4"
                    id="panel-feedback"
                    role="tabpanel"
                    aria-labelledby="tab-feedback"
                    :hidden="activeTab !== 'feedback'"
                >
                    <h5 class="font-semibold text-lg mb-3 text-indigo-700 dark:text-indigo-300">{{ __('surveys.open_feedback') }}</h5>

                    @if($openFeedbackResponses->count() > 0)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ __('surveys.text_responses_count', ['count' => $openFeedbackResponses->count()]) }}</p>
                        <div class="space-y-3 max-h-96 overflow-y-auto rounded-lg shadow-md p-4 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
                            @foreach($openFeedbackResponses as $response)
                                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                    {{ $response }}
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-gray-600 dark:text-gray-300">{{ __('surveys.no_open_feedback') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-gray-600 dark:text-gray-300">
                    {{ __('surveys.no_target_responses') }}
                </p>
            </div>
        @endif
    </div>
@endif