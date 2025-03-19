{{-- Open Feedback Component --}}

<div class="space-y-6">
    @if(isset($responses) && count($responses) > 0)
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            {{ __('surveys.responses_count', ['count' => count($responses)]) }}
        </p>

        <div class="space-y-4">
            @foreach($responses as $index => $response)
                <div class="bg-white dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                    <p class="text-gray-800 dark:text-gray-200 font-medium mb-1">{{ __('surveys.response') }} #{{ $index + 1 }}</p>
                    <div class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap break-words">{{ $response }}</div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 text-center">
            <p class="text-gray-500 dark:text-gray-400">{{ __('surveys.no_feedback_responses') }}</p>
        </div>
    @endif
</div>