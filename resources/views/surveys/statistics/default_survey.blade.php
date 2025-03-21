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
            <x-surveys.statistics.stat-card :title="$stat['question']->question">
                @if($stat['template_type'] === 'range')
                    <x-surveys.statistics.rating-statistics
                        :averageRating="$stat['data']['average_rating'] ?? null"
                        :submissionCount="$stat['data']['submission_count'] ?? 0"
                        :ratingCounts="$stat['data']['rating_counts'] ?? []"
                        :maxValue="$maxRatingCount"
                    />
                @elseif($stat['template_type'] === 'text' || $stat['template_type'] === 'textarea')
                    <x-surveys.statistics.text-responses
                        :responses="$stat['data']['responses'] ?? []"
                        :count="count($stat['data']['responses'] ?? [])"
                    />
                @elseif($stat['template_type'] === 'checkbox' || $stat['template_type'] === 'checkboxes')
                    @if(isset($stat['data']['option_counts']) && count($stat['data']['option_counts']) > 0)
                        @include('surveys.statistics.components.checkbox_distribution', [
                            'optionCounts' => $stat['data']['option_counts'],
                            'submissionCount' => $stat['data']['submission_count'] ?? null
                        ])
                    @else
                        <p class="text-gray-500 dark:text-gray-400">{{ __('surveys.no_responses_yet') }}</p>
                    @endif
                @endif
            </x-surveys.statistics.stat-card>
        @endif
    @endforeach
</div>