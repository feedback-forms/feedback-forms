{{-- Tabbed Categories Component --}}
{{-- Parameters:
    $categories - Array of categories with their questions
    $submissionCount - Number of submissions
    $maxRatingCount (optional) - Maximum rating count for consistent scaling
--}}

@if(count($categories) > 0)
    @php
        // Find the first category that has responses
        $firstCategoryKey = null;
        foreach($categories as $key => $category) {
            if(!empty($category['questions']) && ($category['hasResponses'] ?? false)) {
                $firstCategoryKey = $key;
                break;
            }
        }
        // If no category has responses, use the first category
        if ($firstCategoryKey === null && !empty($categories)) {
            $firstCategoryKey = array_key_first($categories);
        }
    @endphp

    <div x-data="{ activeTab: '{{ $firstCategoryKey }}' }" class="mt-6">
        <!-- Tabs -->
        <div class="border-b border-gray-300 dark:border-gray-600 flex flex-wrap" role="tablist">
            @foreach($categories as $catKey => $category)
                @if(!empty($category['questions'] ?? []))
                    <button
                        @click="activeTab = '{{ $catKey }}'"
                        :class="activeTab === '{{ $catKey }}' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="px-4 py-2 font-medium text-sm focus:outline-none"
                        role="tab"
                    >
                        {{ $category['title'] ?? __('surveys.category') }}
                        @if(($category['hasResponses'] ?? false) === false)
                            <span class="text-xs text-red-500">({{ __('surveys.no_responses_yet') }})</span>
                        @endif
                    </button>
                @endif
            @endforeach
        </div>

        <!-- Tab Panels -->
        @foreach($categories as $catKey => $category)
            @if(!empty($category['questions'] ?? []))
                <div
                    x-show="activeTab === '{{ $catKey }}'"
                    class="py-4"
                    role="tabpanel">
                    <h5 class="font-semibold text-lg mb-3 text-indigo-700 dark:text-indigo-300">{{ $category['title'] ?? __('surveys.category') }}</h5>

                    {{ $slot }}

                    @if(isset($category) && isset($catKey))
                        @if($category['hasResponses'] ?? false)
                            <!-- Check if this is the Open Feedback category -->
                            @php
                                $isOpenFeedbackCategory = false;
                                $categoryTitle = $category['title'] ?? '';
                                if ($categoryTitle === 'Offenes Feedback' || $categoryTitle === 'Open Feedback') {
                                    $isOpenFeedbackCategory = true;
                                }
                            @endphp

                            @if($isOpenFeedbackCategory)
                                <!-- Special display for Open Feedback -->
                                <div class="space-y-4">
                                    @foreach($category['questions'] as $questionStat)
                                        @if(($questionStat['template_type'] === 'text' || $questionStat['template_type'] === 'textarea') &&
                                            isset($questionStat['data']['response_count']) &&
                                            $questionStat['data']['response_count'] > 0)
                                            <div class="mb-6">
                                                <h6 class="font-medium text-gray-800 dark:text-gray-200 mb-2">{{ $questionStat['question']->question }}</h6>
                                                <x-surveys.statistics.text-responses
                                                    :responses="$questionStat['data']['responses'] ?? []"
                                                    :count="$questionStat['data']['response_count'] ?? 0"
                                                />
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <!-- Standard table for other categories -->
                                <div class="overflow-x-auto rounded-lg shadow-md mb-4">
                                    <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
                                        <thead>
                                            <tr>
                                                <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-left font-semibold text-gray-700 dark:text-gray-300">{{ __('surveys.question') }}</th>
                                                <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">{{ __('surveys.average_rating_short') }}</th>
                                                <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">{{ __('surveys.responses') }}</th>
                                                <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300 hidden sm:table-cell">{{ __('surveys.distribution') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($category['questions'] as $questionStat)
                                                @if($questionStat['template_type'] === 'range' &&
                                                   isset($questionStat['data']['average_rating']) &&
                                                   is_numeric($questionStat['data']['average_rating']))
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700">
                                                            {{ $questionStat['question']->question }}
                                                        </td>
                                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                            <span class="text-blue-500 font-medium">{{ $questionStat['data']['average_rating'] }}</span>
                                                        </td>
                                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 text-center">
                                                            {{ $questionStat['data']['submission_count'] }}
                                                        </td>
                                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700 hidden sm:table-cell">
                                                            @include('surveys.statistics.components.rating_distribution', [
                                                                'ratingCounts' => $questionStat['data']['rating_counts'] ?? [],
                                                                'compactView' => true,
                                                                'maxValue' => $maxRatingCount ?? null
                                                            ])
                                                        </td>
                                                    </tr>
                                                @elseif(($questionStat['template_type'] === 'text' || $questionStat['template_type'] === 'textarea') &&
                                                      isset($questionStat['data']['response_count']) &&
                                                      $questionStat['data']['response_count'] > 0)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700" colspan="4">
                                                            <div class="mb-2 font-medium">{{ $questionStat['question']->question }}</div>
                                                            <x-surveys.statistics.text-responses
                                                                :responses="$questionStat['data']['responses'] ?? []"
                                                                :count="$questionStat['data']['response_count'] ?? 0"
                                                            />
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @else
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg mb-4">
                                <p class="text-gray-600 dark:text-gray-300">{{ __('surveys.no_category_responses') }}</p>
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        @endforeach
    </div>
@else
    <div class="p-4 bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-lg my-4">
        <h5 class="font-semibold">{{ __('surveys.no_categories_available') }}</h5>
        <p class="text-sm mt-1">{{ __('surveys.no_categories_generated_description') }}</p>
    </div>
@endif