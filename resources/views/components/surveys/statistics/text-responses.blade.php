{{-- Text Responses Component --}}
{{-- Parameters:
    $responses - Array of text responses
    $count - Number of responses
    $maxHeight (optional) - Maximum height for the response container
--}}

@if(isset($responses) && count($responses) > 0)
    <div class="space-y-2 {{ isset($maxHeight) ? 'max-h-' . $maxHeight : 'max-h-96' }} overflow-y-auto">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
            {{ __('surveys.text_responses_count', ['count' => $count ?? count($responses)]) }}
        </p>
        @foreach($responses as $response)
            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                {{ $response }}
            </div>
        @endforeach
    </div>
@else
    <p class="text-gray-500 dark:text-gray-400 italic">{{ $noResponsesText ?? __('surveys.no_responses_yet') }}</p>
@endif