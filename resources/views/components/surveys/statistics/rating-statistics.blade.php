{{-- Rating Statistics Component --}}
{{-- Parameters:
    $averageRating - The average rating value
    $submissionCount - Number of submissions
    $ratingCounts - Array of rating counts by value
    $maxValue (optional) - Maximum value for consistent scaling
--}}

@if(isset($averageRating) && is_numeric($averageRating))
    <div class="mb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ __('surveys.average_rating_short') }}</p>
        <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $averageRating }}</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ __('surveys.responses_count', ['count' => $submissionCount ?? 0]) }}
        </p>
    </div>

    @if(isset($ratingCounts) && count($ratingCounts) > 0)
        <div class="mt-4">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('surveys.distribution') }}</p>
            @include('surveys.statistics.components.rating_distribution', [
                'ratingCounts' => $ratingCounts,
                'maxValue' => $maxValue ?? null
            ])
        </div>
    @endif
@else
    <p class="text-gray-500 dark:text-gray-400">{{ __('surveys.no_responses_yet') }}</p>
@endif