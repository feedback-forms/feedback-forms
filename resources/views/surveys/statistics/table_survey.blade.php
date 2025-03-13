{{-- Table Survey Statistics Component --}}
<div class="mb-8 p-6 border rounded-lg bg-gray-50 dark:bg-gray-700 shadow-sm hover:shadow-md transition-shadow duration-300">
    <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-1">Table Survey Results</h4>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Ratings grouped by category</p>

    @if($submissionCount > 0)
        <p class="text-sm mb-4">{{ $submissionCount }} response(s) received</p>

        @if(count($tableCategories) > 0)
            @php
                // Find the first category that has responses
                $firstCategoryKey = null;
                foreach($tableCategories as $key => $category) {
                    if(!empty($category['questions']) && ($category['hasResponses'] ?? false)) {
                        $firstCategoryKey = $key;
                        break;
                    }
                }
                // If no category has responses, use the first category
                if ($firstCategoryKey === null && !empty($tableCategories)) {
                    $firstCategoryKey = array_key_first($tableCategories);
                }
            @endphp

            <div x-data="{ activeTab: '{{ $firstCategoryKey }}' }" class="mt-6">
                <!-- Tabs -->
                <div class="border-b border-gray-300 dark:border-gray-600 flex flex-wrap" role="tablist">
                    @foreach($tableCategories as $catKey => $category)
                        @if(!empty($category['questions'] ?? []))
                            <button
                                @click="activeTab = '{{ $catKey }}'"
                                :class="activeTab === '{{ $catKey }}' ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                                class="px-4 py-2 font-medium text-sm focus:outline-none"
                                role="tab"
                            >
                                {{ $category['title'] ?? 'Category' }}
                                @if(($category['hasResponses'] ?? false) === false)
                                    <span class="text-xs text-red-500">(No responses)</span>
                                @endif
                            </button>
                        @endif
                    @endforeach
                </div>

                <!-- Tab Panels -->
                @foreach($tableCategories as $catKey => $category)
                    @if(!empty($category['questions'] ?? []))
                        <div
                            x-show="activeTab === '{{ $catKey }}'"
                            class="py-4"
                            role="tabpanel">
                            <h5 class="font-semibold text-lg mb-3 text-indigo-700 dark:text-indigo-300">{{ $category['title'] ?? 'Category' }}</h5>

                            @if($category['hasResponses'] ?? false)
                                <!-- Calculate the maximum rating count for this category -->
                                @php
                                    $maxRatingCount = 0;
                                    // Find the maximum count across all questions in this category
                                    foreach ($category['questions'] as $qs) {
                                        if (isset($qs['data']['rating_counts']) && is_array($qs['data']['rating_counts'])) {
                                            foreach ($qs['data']['rating_counts'] as $ratingValue => $count) {
                                                $maxRatingCount = max($maxRatingCount, $count);
                                            }
                                        }
                                    }
                                @endphp

                                <!-- Questions with Responses -->
                                <div class="overflow-x-auto rounded-lg shadow-md mb-4">
                                    <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700">
                                        <thead>
                                            <tr>
                                                <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-left font-semibold text-gray-700 dark:text-gray-300">Question</th>
                                                <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Avg. Rating</th>
                                                <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300">Responses</th>
                                                <th class="py-3 px-4 bg-gray-100 dark:bg-gray-900 border-b border-gray-300 dark:border-gray-700 text-center font-semibold text-gray-700 dark:text-gray-300 hidden sm:table-cell">Distribution</th>
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
                                                                'maxValue' => $maxRatingCount
                                                            ])
                                                        </td>
                                                    </tr>
                                                @elseif(($questionStat['template_type'] === 'text' || $questionStat['template_type'] === 'textarea') &&
                                                      isset($questionStat['data']['response_count']) &&
                                                      $questionStat['data']['response_count'] > 0)
                                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-600">
                                                        <td class="py-3 px-4 border-b border-gray-300 dark:border-gray-700" colspan="4">
                                                            <div class="mb-2 font-medium">{{ $questionStat['question']->question }}</div>
                                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $questionStat['data']['response_count'] }} text response(s) received</p>
                                                            @if(isset($questionStat['data']['responses']))
                                                                <div class="mt-2 space-y-2">
                                                                    @foreach($questionStat['data']['responses'] as $response)
                                                                        <div class="p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded">{{ $response }}</div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg mb-4">
                                    <p class="text-gray-600 dark:text-gray-300">No response statistics available for this category yet.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-lg my-4">
                <h5 class="font-semibold">No Categories Available</h5>
                <p class="text-sm mt-1">No categories could be generated from the survey questions.</p>
            </div>
        @endif
    @endif
</div>