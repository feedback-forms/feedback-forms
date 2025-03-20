{{-- Checkbox Distribution Component with Improved Visualization --}}

<div class="space-y-4">
    @if(isset($optionCounts) && is_array($optionCounts) && !empty($optionCounts))
        @php
            // Define valid options with their translations
            $validOptions = [
                'Yes' => __('surveys.checkbox_options.yes'),
                'No' => __('surveys.checkbox_options.no'),
                'Not applicable' => __('surveys.checkbox_options.na')
            ];

            // Map English option keys to their translations for display
            $translationMap = [
                'Yes' => __('surveys.checkbox_options.yes'),
                'No' => __('surveys.checkbox_options.no'),
                'Not applicable' => __('surveys.checkbox_options.na')
            ];

            // Initialize valid options with zero values if they don't exist
            $filteredCounts = [];
            foreach (array_keys($validOptions) as $option) {
                $filteredCounts[$option] = $optionCounts[$option] ?? 0;
            }

            $totalResponses = array_sum($filteredCounts);
            $maxCount = max($filteredCounts ?: [0]);

            // Special handling for Yes/No/NA responses
            $isYesNoQuestion = true;
            $colors = [
                'Yes' => 'bg-gradient-to-r from-green-400 to-green-600',
                'No' => 'bg-gradient-to-r from-red-400 to-red-600',
                'Not applicable' => 'bg-gradient-to-r from-gray-400 to-gray-500',
            ];
        @endphp

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
            {{ __('surveys.responses_count', ['count' => $submissionCount ?? $totalResponses]) }}
        </p>

        <div class="w-full bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
            @foreach(array_keys($validOptions) as $option)
                <div class="mb-3">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $translationMap[$option] }}</span>
                        <div class="flex items-center">
                            <span class="text-sm font-medium">
                                {{ $filteredCounts[$option] ?? 0 }}
                            </span>
                            <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">
                                @if($totalResponses > 0)
                                    ({{ round((($filteredCounts[$option] ?? 0) / $totalResponses) * 100) }}%)
                                @else
                                    (0%)
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-4 overflow-hidden">
                            <div class="{{ $colors[$option] }} h-4 rounded-full transition-all duration-500 relative"
                                 style="width: {{ $totalResponses > 0 ? (($filteredCounts[$option] ?? 0) / max(1, $maxCount)) * 100 : 0 }}%">
                                 @if(($filteredCounts[$option] ?? 0) > 0)
                                 <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white drop-shadow-md">
                                     {{ round((($filteredCounts[$option] ?? 0) / $totalResponses) * 100) }}%
                                 </span>
                                 @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-5 pt-3 border-t border-gray-200 dark:border-gray-600">
                <div class="flex justify-center items-center">
                    <div class="flex items-center space-x-1">
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ __('surveys.checkbox_options.yes') }}</span>
                    </div>
                    <div class="mx-3 text-gray-300 dark:text-gray-600">|</div>
                    <div class="flex items-center space-x-1">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ __('surveys.checkbox_options.no') }}</span>
                    </div>
                    <div class="mx-3 text-gray-300 dark:text-gray-600">|</div>
                    <div class="flex items-center space-x-1">
                        <div class="w-3 h-3 rounded-full bg-gray-500"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400">{{ __('surveys.checkbox_options.na') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400">{{ __('surveys.no_responses_yet') }}</p>
    @endif
</div>