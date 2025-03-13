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
        <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-1">Target Survey Results</h4>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Segment ratings for target diagram</p>

        @if($submissionCount > 0)
            <p class="text-sm mb-4">{{ $submissionCount }} response(s) received</p>

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
            @endphp

            <!-- Target Segments Table -->
            <div class="overflow-x-auto rounded-lg shadow-md mb-4">
                <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr>
                            <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-left font-semibold text-gray-700 dark:text-gray-300">Segment</th>
                            <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Avg. Rating</th>
                            <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Responses</th>
                            <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300 hidden sm:table-cell">Distribution</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($targetItem['data']['segment_statistics'] as $segmentStat)
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-gray-600 dark:text-gray-300">
                    No responses have been received for this target survey yet.
                </p>
            </div>
        @endif
    </div>
@endif