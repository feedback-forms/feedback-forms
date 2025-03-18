{{-- Smiley Survey Statistics Component --}}
<div class="mb-8 p-6 border rounded-lg bg-gray-50 dark:bg-gray-700 shadow-sm hover:shadow-md transition-shadow duration-300">
    <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-1">{{ __('surveys.smiley_survey_results') }}</h4>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ __('surveys.smiley_feedback_description') }}</p>

    @if($submissionCount > 0)
        <p class="text-sm mb-4">{{ __('surveys.responses_count', ['count' => $submissionCount]) }}</p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Positive Feedback Card -->
            <div class="p-6 border rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                <div class="flex items-center mb-4">
                    <x-far-face-smile class="w-8 h-8 text-green-500 mr-2" />
                    <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200">{{ __('surveys.smiley.positive') }}</h4>
                </div>

                @php
                    $positiveResponses = collect();
                    foreach($statisticsData as $stat) {
                        if(isset($stat['question']) && $stat['question'] &&
                           $stat['template_type'] === 'text' &&
                           stripos($stat['question']->question, 'Positive Feedback') !== false &&
                           isset($stat['data']['responses'])) {
                            $positiveResponses = collect($stat['data']['responses']);
                            break;
                        }
                    }
                @endphp

                @if($positiveResponses->count() > 0)
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($positiveResponses as $response)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                {{ $response }}
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 italic">{{ __('surveys.no_positive_feedback') }}</p>
                @endif
            </div>

            <!-- Negative Feedback Card -->
            <div class="p-6 border rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                <div class="flex items-center mb-4">
                    <x-far-face-frown class="w-8 h-8 text-red-500 mr-2" />
                    <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200">{{ __('surveys.smiley.negative') }}</h4>
                </div>

                @php
                    $negativeResponses = collect();
                    foreach($statisticsData as $stat) {
                        if(isset($stat['question']) && $stat['question'] &&
                           $stat['template_type'] === 'text' &&
                           stripos($stat['question']->question, 'Negative Feedback') !== false &&
                           isset($stat['data']['responses'])) {
                            $negativeResponses = collect($stat['data']['responses']);
                            break;
                        }
                    }
                @endphp

                @if($negativeResponses->count() > 0)
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($negativeResponses as $response)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                {{ $response }}
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 italic">{{ __('surveys.no_negative_feedback') }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-gray-600 dark:text-gray-300">
                {{ __('surveys.no_smiley_responses') }}
            </p>
        </div>
    @endif
</div>