<x-app-layout>
    <x-slot name="title">
        {{ __('title.survey.statistics', ['name' => $survey->name ?? $survey->feedback_template->title]) }}
    </x-slot>

    <x-slot name="header">
        <div class="bg-indigo-100 dark:bg-indigo-900 py-4 px-6 rounded-md shadow-md">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('surveys.survey_statistics') }} - {{ $survey->name ?: ($survey->feedback_template->title ?? __('surveys.survey')) }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-8">
                        <a href="{{ route('surveys.list') }}" class="flex flex-row gap-2 items-center w-fit text-lg px-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500 dark:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-gray-500 dark:text-gray-400">{{ __('surveys.back_to_overview') }}</span>
                        </a>
                    </div>

                    <h3 class="text-xl font-semibold mb-4 text-indigo-700 dark:text-indigo-300">{{ __('surveys.survey_details') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
                        <div class="bg-gray-50 dark:bg-gray-700 p-5 rounded-lg shadow-sm">
                            <p class="mb-2"><span class="font-semibold">{{ __('surveys.survey_title') }}:</span> {{ $survey->name ?: ($survey->feedback_template->title ?? 'N/A') }}</p>
                            <p class="mb-2"><span class="font-semibold">{{ __('surveys.access_key') }}:</span> {{ $survey->accesskey }}</p>
                            <p><span class="font-semibold">{{ __('surveys.created_at') }}:</span> {{ $survey->created_at->format('d.m.Y') }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-5 rounded-lg shadow-sm">
                            <p class="mb-2"><span class="font-semibold">{{ __('surveys.responses') }}:</span> {{ $survey->submission_count }} / {{ $survey->limit == -1 ? '∞' : $survey->limit }}</p>
                            <p class="mb-2"><span class="font-semibold">{{ __('surveys.expires') }}:</span> {{ $survey->expire_date->format('d.m.Y H:i') }}</p>
                            <p><span class="font-semibold">{{ __('surveys.status') }}:</span>
                                @if($survey->expire_date->isPast())
                                    <span class="text-red-500 font-medium">{{ __('surveys.expired') }}</span>
                                @else
                                    <span class="text-green-500 font-medium">{{ __('surveys.active') }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <h3 class="text-xl font-semibold mt-10 mb-6 text-indigo-700 dark:text-indigo-300">{{ __('surveys.question_statistics') }}</h3>

                    @if(count($statisticsData) > 0)
                        <!-- Test Alpine.js is working -->
                        <div x-data="{ testMessage: 'If you can see this, Alpine.js is working' }" class="hidden">
                            <div x-text="testMessage"></div>
                        </div>

                        <!-- Handle table surveys -->
                        @if($isTableSurvey)
                            @include('surveys.statistics.table_survey')
                        @endif

                        <!-- Handle target surveys -->
                        @if($isTargetTemplate)
                            @include('surveys.statistics.target_survey')
                        @endif

                        <!-- Handle smiley surveys -->
                        @if($isSmileyTemplate)
                            @include('surveys.statistics.smiley_survey')
                        @endif

                        <!-- Handle checkbox surveys -->
                        @if($isCheckboxTemplate)
                            @include('surveys.statistics.checkbox_survey')
                        @endif

                        <!-- Display statistics for non-table surveys -->
                        @if(!$isTableSurvey && !$isSmileyTemplate && !$isCheckboxTemplate)
                            @include('surveys.statistics.default_survey', ['statisticsData' => $statisticsData])
                        @endif
                    @else
                        <div class="p-6 border rounded-lg bg-yellow-50 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                            <h4 class="text-lg font-semibold mb-2">{{ __('surveys.no_responses_yet') }}</h4>
                            <p>{{ __('surveys.no_responses_explanation') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
