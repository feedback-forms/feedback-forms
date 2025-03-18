<div class="flex flex-col gap-4 p-6">
    <h1 class="text-2xl font-bold text-gray-700 dark:text-gray-200">
        {{ __('admin.survey_aggregation') }}
    </h1>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <!-- Filter Controls -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('admin.select_category') }}
                </label>
                <select
                    id="category"
                    wire:model.live="selectedCategory"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                >
                    <option value="">{{ __('admin.select_category_placeholder') }}</option>
                    @foreach ($availableCategories as $value => $label)
                        <option value="{{ $value }}">{{ $label }} ({{ __('admin.min') }} {{ $thresholds[$value] }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('admin.select_value') }}
                </label>
                <select
                    id="value"
                    wire:model.live="selectedValue"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white"
                    @if(!$selectedCategory) disabled @endif
                >
                    <option value="">{{ __('admin.select_value_placeholder') }}</option>
                    @foreach ($availableValues as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Error Message -->
        @if($errorMessage)
            <div class="bg-red-50 dark:bg-red-900 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-fas-exclamation-circle class="w-5 h-5 text-red-400 dark:text-red-300" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ __('admin.error') }}
                        </h3>
                        <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                            <p>{{ $errorMessage }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Loading State -->
        <div wire:loading wire:target="loadAggregatedData, loadAvailableValues" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
        </div>

        <!-- No Data Selected State -->
        @if(!$selectedCategory || !$selectedValue)
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-8 text-center">
                <p class="text-gray-500 dark:text-gray-400">
                    {{ __('admin.select_filters_message') }}
                </p>
            </div>
        @endif

        <!-- Threshold Not Met -->
        @if($aggregatedData && !$aggregatedData['threshold_met'] && !isset($aggregatedData['error']))
            <div class="bg-yellow-50 dark:bg-yellow-900 rounded-lg p-8 text-center">
                <div class="flex flex-col items-center">
                    <x-fas-triangle-exclamation class="w-16 h-16 text-yellow-500 mb-4" />
                    <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-200">
                        {{ __('admin.threshold_not_met') }}
                    </h3>
                    <p class="mt-2 text-yellow-700 dark:text-yellow-300">
                        {{ __('admin.threshold_not_met_description', [
                            'category' => __('admin.' . $aggregatedData['category']),
                            'value' => $aggregatedData['value'],
                            'count' => $aggregatedData['submission_count'],
                            'required' => $aggregatedData['min_threshold']
                        ]) }}
                    </p>
                </div>

                <!-- Visualization placeholder when threshold is not met -->
                <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg p-4 max-w-lg mx-auto opacity-50">
                    <p class="text-sm text-center text-gray-500 dark:text-gray-400 mb-3">{{ __('admin.visualization_placeholder') }}</p>
                    <div class="grid grid-cols-5 gap-1 justify-items-center mb-3">
                        @for ($i = 1; $i <= 5; $i++)
                            <div class="h-20 w-full bg-gray-200 dark:bg-gray-600 rounded"></div>
                        @endfor
                    </div>
                    <div class="h-2 w-full bg-gray-200 dark:bg-gray-600 rounded-full mx-auto"></div>
                </div>
            </div>
        @endif

        <!-- Results Display with Tabs -->
        @if($aggregatedData && $aggregatedData['threshold_met'])
            <div class="space-y-8">
                <!-- Display the responses count -->
                <div class="mb-6 px-1">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $aggregatedData['submission_count'] }} {{ __('admin.responses_received') }}
                    </p>
                </div>

                @if(isset($aggregatedData['categories']) && count($aggregatedData['categories']) > 0)
                    <!-- Tab Navigation -->
                    <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                        <ul class="flex flex-wrap -mb-px">
                            @foreach($aggregatedData['categories'] as $categoryId => $category)
                                <li class="mr-2">
                                    <button
                                        wire:click="setActiveTab('{{ $categoryId }}')"
                                        class="inline-block p-4 text-sm font-medium {{ $activeTab === $categoryId ? 'text-indigo-600 border-b-2 border-indigo-600 dark:text-indigo-400 dark:border-indigo-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                                    >
                                        {{ __('admin.category.' . $categoryId, ['default' => $category['name']]) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Tab Content -->
                    @foreach($aggregatedData['categories'] as $categoryId => $category)
                        <div class="{{ $activeTab === $categoryId ? 'block' : 'hidden' }}">
                            <!-- Range Questions -->
                            @if(isset($category['results']['range']) && count($category['results']['range']) > 0)
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                                        {{ __('admin.rating_questions') }}
                                    </h2>
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        @foreach($category['results']['range'] as $questionData)
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                                <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-3">
                                                    {{ $questionData['question'] }}
                                                </h3>
                                                <div class="flex justify-between items-center mb-4">
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ __('admin.responses') }}: {{ $questionData['count'] }}
                                                    </span>
                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ __('admin.average') }}: <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $questionData['average'] }}</span>
                                                    </span>
                                                </div>

                                                <!-- Bar Chart -->
                                                <div class="mt-4 space-y-3">
                                                    @foreach($questionData['distribution'] as $rating => $count)
                                                        <div>
                                                            <div class="flex justify-between items-center mb-1">
                                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $rating }}</span>
                                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $count }}</span>
                                                            </div>
                                                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                                                                @php
                                                                    $percentage = $questionData['count'] > 0 ? ($count / $questionData['count']) * 100 : 0;
                                                                @endphp
                                                                <div class="bg-indigo-600 dark:bg-indigo-500 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Checkbox Questions -->
                            @if(isset($category['results']['checkboxes']) && count($category['results']['checkboxes']) > 0)
                                <div class="mt-8">
                                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                                        {{ __('admin.checkbox_questions') }}
                                    </h2>
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        @foreach($category['results']['checkboxes'] as $questionData)
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                                <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-3">
                                                    {{ $questionData['question'] }}
                                                </h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                                    {{ __('admin.total_responses') }}: {{ $questionData['total_responses'] }}
                                                </p>

                                                <div class="space-y-3 mt-4">
                                                    @if(isset($questionData['options']))
                                                        @foreach($questionData['options'] as $option => $count)
                                                            <div>
                                                                <div class="flex justify-between items-center mb-1">
                                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $option }}</span>
                                                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                                                        {{ $count }} ({{ $questionData['percentages'][$option] ?? 0 }}%)
                                                                    </span>
                                                                </div>
                                                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                                                                    <div class="bg-green-500 dark:bg-green-600 h-2.5 rounded-full"
                                                                        style="width: {{ $questionData['percentages'][$option] ?? 0 }}%"></div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(empty($category['results']) || (empty($category['results']['range']) && empty($category['results']['checkboxes'])))
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-8 text-center">
                                    @if($categoryId === 'target_feedback')
                                        <div class="flex flex-col items-center">
                                            <x-fas-comment-alt class="w-16 h-16 text-blue-500 mb-4" />
                                            <p class="text-gray-600 dark:text-gray-300 max-w-lg mx-auto">
                                                {{ __('admin.text_answers_excluded') }}
                                            </p>
                                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('admin.target_feedback_note', ['default' => 'Textliche Rückmeldungen werden hier nicht angezeigt, um die Anonymität zu wahren.']) }}
                                            </p>
                                        </div>
                                    @else
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ __('admin.no_question_data_for_category') }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <!-- Backward compatibility - display results without tabs -->
                    <!-- Range Questions -->
                    @if(isset($aggregatedData['results']['range']) && count($aggregatedData['results']['range']) > 0)
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                                {{ __('admin.rating_questions') }}
                            </h2>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                @foreach($aggregatedData['results']['range'] as $questionData)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-3">
                                            {{ $questionData['question'] }}
                                        </h3>
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('admin.responses') }}: {{ $questionData['count'] }}
                                            </span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ __('admin.average') }}: <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $questionData['average'] }}</span>
                                            </span>
                                        </div>

                                        <!-- Bar Chart -->
                                        <div class="mt-4 space-y-3">
                                            @foreach($questionData['distribution'] as $rating => $count)
                                                <div>
                                                    <div class="flex justify-between items-center mb-1">
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $rating }}</span>
                                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $count }}</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                                                        @php
                                                            $percentage = $questionData['count'] > 0 ? ($count / $questionData['count']) * 100 : 0;
                                                        @endphp
                                                        <div class="bg-indigo-600 dark:bg-indigo-500 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Checkbox Questions -->
                    @if(isset($aggregatedData['results']['checkboxes']) && count($aggregatedData['results']['checkboxes']) > 0)
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">
                                {{ __('admin.checkbox_questions') }}
                            </h2>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                @foreach($aggregatedData['results']['checkboxes'] as $questionData)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-3">
                                            {{ $questionData['question'] }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                            {{ __('admin.total_responses') }}: {{ $questionData['total_responses'] }}
                                        </p>

                                        <div class="space-y-3 mt-4">
                                            @if(isset($questionData['options']))
                                                @foreach($questionData['options'] as $option => $count)
                                                    <div>
                                                        <div class="flex justify-between items-center mb-1">
                                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $option }}</span>
                                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                                {{ $count }} ({{ $questionData['percentages'][$option] ?? 0 }}%)
                                                            </span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                                                            <div class="bg-green-500 dark:bg-green-600 h-2.5 rounded-full"
                                                                style="width: {{ $questionData['percentages'][$option] ?? 0 }}%"></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(empty($aggregatedData['results']) || (empty($aggregatedData['results']['range']) && empty($aggregatedData['results']['checkboxes'])))
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-8 text-center">
                            <p class="text-gray-500 dark:text-gray-400">
                                {{ __('admin.no_question_data') }}
                            </p>
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>

    <!-- Information about excluded data -->
    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <x-fas-info-circle class="w-5 h-5 text-blue-500" />
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    {{ __('admin.information') }}
                </h3>
                <p class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    {{ __('admin.text_answers_excluded') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Debug Information (only visible in non-production) -->
    @if(config('app.debug'))
        <div class="mt-4 p-4 bg-gray-100 dark:bg-gray-900 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Debug Information</h3>
            <dl class="space-y-2">
                <div>
                    <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Selected Category:</dt>
                    <dd class="text-sm text-gray-800 dark:text-gray-200">{{ $selectedCategory ?? 'null' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Selected Value:</dt>
                    <dd class="text-sm text-gray-800 dark:text-gray-200">{{ $selectedValue ?? 'null' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Available Values Count:</dt>
                    <dd class="text-sm text-gray-800 dark:text-gray-200">{{ count($availableValues) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Available Values:</dt>
                    <dd class="text-sm text-gray-800 dark:text-gray-200">
                        @if(count($availableValues) > 0)
                            {{ implode(', ', $availableValues) }}
                        @else
                            None
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Has Aggregated Data:</dt>
                    <dd class="text-sm text-gray-800 dark:text-gray-200">{{ !empty($aggregatedData) ? 'Yes' : 'No' }}</dd>
                </div>
                @if(!empty($aggregatedData))
                    <div>
                        <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Threshold Met:</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-200">{{ $aggregatedData['threshold_met'] ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Submission Count:</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-200">{{ $aggregatedData['submission_count'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Min Threshold:</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-200">{{ $aggregatedData['min_threshold'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Has Categories:</dt>
                        <dd class="text-sm text-gray-800 dark:text-gray-200">{{ isset($aggregatedData['categories']) ? 'Yes' : 'No' }}</dd>
                    </div>
                    @if(isset($aggregatedData['categories']))
                        <div>
                            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Categories:</dt>
                            <dd class="text-sm text-gray-800 dark:text-gray-200">{{ implode(', ', array_keys($aggregatedData['categories'])) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Tab:</dt>
                            <dd class="text-sm text-gray-800 dark:text-gray-200">{{ $activeTab ?? 'None' }}</dd>
                        </div>
                    @endif
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">Error Message:</dt>
                    <dd class="text-sm text-gray-800 dark:text-gray-200">{{ $errorMessage ?? 'None' }}</dd>
                </div>
            </dl>
        </div>
    @endif
</div>