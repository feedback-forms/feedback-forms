{{-- Table Survey Statistics Component --}}
<x-surveys.statistics.stat-card
    title="{{ __('surveys.table_survey_results') }}"
    subtitle="{{ __('surveys.ratings_grouped_by_category') }}"
    class="mb-8">

    @if($submissionCount > 0)
        <p class="text-sm mb-4">{{ __('surveys.responses_count', ['count' => $submissionCount]) }}</p>

        <!-- Calculate the maximum rating count across all categories -->
        @php
            $maxRatingCount = 0;
            foreach ($tableCategories as $category) {
                if (!empty($category['questions'])) {
                    foreach ($category['questions'] as $qs) {
                        if (isset($qs['data']['rating_counts']) && is_array($qs['data']['rating_counts'])) {
                            foreach ($qs['data']['rating_counts'] as $ratingValue => $count) {
                                $maxRatingCount = max($maxRatingCount, $count);
                            }
                        }
                    }
                }
            }
        @endphp

        <x-surveys.statistics.tabbed-categories
            :categories="$tableCategories"
            :submissionCount="$submissionCount"
            :maxRatingCount="$maxRatingCount"
        />
    @else
        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-gray-600 dark:text-gray-300">{{ __('surveys.no_table_responses') }}</p>
        </div>
    @endif
</x-surveys.statistics.stat-card>