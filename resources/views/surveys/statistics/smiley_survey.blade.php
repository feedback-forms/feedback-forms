{{-- Smiley Survey Statistics Component --}}
<x-surveys.statistics.stat-card
    title="{{ __('surveys.smiley_survey_results') }}"
    subtitle="{{ __('surveys.smiley_feedback_description') }}"
    class="mb-8">

    @if($submissionCount > 0)
        <p class="text-sm mb-4">{{ __('surveys.responses_count', ['count' => $submissionCount]) }}</p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @php
                // Extract positive feedback responses
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

                // Extract negative feedback responses
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

            <!-- Positive Feedback Card -->
            <x-surveys.statistics.emotion-feedback-card
                title="{{ __('surveys.smiley.positive') }}"
                :responses="$positiveResponses->toArray()"
                iconClass="text-green-500"
                iconComponent="far-face-smile"
                noResponsesText="{{ __('surveys.no_positive_feedback') }}"
            />

            <!-- Negative Feedback Card -->
            <x-surveys.statistics.emotion-feedback-card
                title="{{ __('surveys.smiley.negative') }}"
                :responses="$negativeResponses->toArray()"
                iconClass="text-red-500"
                iconComponent="far-face-frown"
                noResponsesText="{{ __('surveys.no_negative_feedback') }}"
            />
        </div>
    @else
        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <p class="text-gray-600 dark:text-gray-300">
                {{ __('surveys.no_smiley_responses') }}
            </p>
        </div>
    @endif
</x-surveys.statistics.stat-card>