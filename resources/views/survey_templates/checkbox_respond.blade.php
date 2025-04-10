<x-survey-layout>
    <x-slot name="title">
        {{__('title.respond')}}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold text-center mb-8">
                        {{ $survey->feedback_template->title ?? __('surveys.survey') }}
                    </h1>

                    <!-- Survey Information -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                            @if($survey->subject)
                                <div>
                                    <span class="font-semibold">{{ __('surveys.subject') }}:</span>
                                    <span>{{ $survey->subject->name }}</span>
                                </div>
                            @endif

                            @if($survey->grade_level)
                                <div>
                                    <span class="font-semibold">{{ __('surveys.grade_level') }}:</span>
                                    <span>{{ $survey->grade_level->name }}</span>
                                </div>
                            @endif

                            @if($survey->class)
                                <div>
                                    <span class="font-semibold">{{ __('surveys.class') }}:</span>
                                    <span>{{ $survey->class->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="font-medium text-red-600 dark:text-red-400">
                                {{ __('surveys.whoops') }}
                            </div>

                            <ul class="mt-3 list-disc list-inside text-sm text-red-600 dark:text-red-400">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if (session('error'))
                        <div class="mb-4 font-medium text-sm text-red-600 dark:text-red-400">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('surveys.submit', $survey->accesskey) }}">
                        @csrf

                        <!-- General Feedback Section -->
                        @php
                            $generalQuestions = $survey->questions->where('category', 'general_feedback')->sortBy('order');
                            $detailedQuestions = $survey->questions->where('category', 'detailed_feedback')->sortBy('order');
                            $uncategorizedQuestions = $survey->questions->whereNull('category')->sortBy('order');
                        @endphp

                        @if($generalQuestions->count() > 0)
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold mb-4 p-2 bg-gray-100 dark:bg-gray-700 rounded">
                                    1. {{ __('surveys.general_feedback') }}
                                </h2>
                                <div class="space-y-4">
                                    @foreach($generalQuestions as $question)
                                        <div class="p-4 border rounded-lg bg-white dark:bg-gray-800">
                                            <h3 class="font-semibold mb-3">{{ $question->order }}. {{ $question->question }}</h3>

                                            @php
                                                $templateType = $question->question_template->type ?? 'text';
                                            @endphp

                                            @if($templateType === 'checkbox')
                                                <div class="space-y-2">
                                                    @foreach([
                                                        'Yes',
                                                        'No',
                                                        'Not applicable'
                                                    ] as $option)
                                                        <label class="flex items-center space-x-2 cursor-pointer">
                                                            <input
                                                                type="radio"
                                                                name="responses[{{ $question->id }}]"
                                                                value="{{ $option }}"
                                                                class="form-radio"
                                                                required
                                                            >
                                                            <span>{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($templateType === 'checkboxes')
                                                <div class="space-y-2">
                                                    @php
                                                        $options = [
                                                            __('surveys.checkboxes_options.strongly_agree'),
                                                            __('surveys.checkboxes_options.agree'),
                                                            __('surveys.checkboxes_options.neutral'),
                                                            __('surveys.checkboxes_options.disagree'),
                                                            __('surveys.checkboxes_options.strongly_disagree')
                                                        ];
                                                    @endphp

                                                    @foreach($options as $option)
                                                        <label class="flex items-center space-x-2 cursor-pointer">
                                                            <input
                                                                type="checkbox"
                                                                name="responses[{{ $question->id }}][]"
                                                                value="{{ $option }}"
                                                                class="form-checkbox"
                                                            >
                                                            <span>{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Detailed Feedback Section -->
                        @if($detailedQuestions->count() > 0)
                            <div class="mb-8">
                                <h2 class="text-xl font-semibold mb-4 p-2 bg-gray-100 dark:bg-gray-700 rounded">
                                    2. {{ __('surveys.detailed_feedback') }}
                                </h2>
                                <div class="space-y-4">
                                    @foreach($detailedQuestions as $question)
                                        <div class="p-4 border rounded-lg bg-white dark:bg-gray-800">
                                            <h3 class="font-semibold mb-3">{{ $question->question }}</h3>

                                            @php
                                                $templateType = $question->question_template->type ?? 'text';
                                            @endphp

                                            @if($templateType === 'checkbox')
                                                <div class="space-y-2">
                                                    @foreach([
                                                        'Yes',
                                                        'No',
                                                        'Not applicable'
                                                    ] as $option)
                                                        <label class="flex items-center space-x-2 cursor-pointer">
                                                            <input
                                                                type="radio"
                                                                name="responses[{{ $question->id }}]"
                                                                value="{{ $option }}"
                                                                class="form-radio"
                                                                required
                                                            >
                                                            <span>{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($templateType === 'checkboxes')
                                                <div class="space-y-2">
                                                    @php
                                                        $options = [
                                                            __('surveys.checkboxes_options.strongly_agree'),
                                                            __('surveys.checkboxes_options.agree'),
                                                            __('surveys.checkboxes_options.neutral'),
                                                            __('surveys.checkboxes_options.disagree'),
                                                            __('surveys.checkboxes_options.strongly_disagree')
                                                        ];
                                                    @endphp

                                                    @foreach($options as $option)
                                                        <label class="flex items-center space-x-2 cursor-pointer">
                                                            <input
                                                                type="checkbox"
                                                                name="responses[{{ $question->id }}][]"
                                                                value="{{ $option }}"
                                                                class="form-checkbox"
                                                            >
                                                            <span>{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Uncategorized Questions Section -->
                        @if($uncategorizedQuestions->count() > 0)
                            <div class="mb-8">
                                <div class="space-y-4">
                                    @foreach($uncategorizedQuestions as $question)
                                        <div class="p-4 border rounded-lg bg-white dark:bg-gray-800">
                                            <h3 class="font-semibold mb-3">{{ $question->question }}</h3>

                                            @php
                                                $templateType = $question->question_template->type ?? 'text';
                                            @endphp

                                            @if($templateType === 'checkbox')
                                                <div class="space-y-2">
                                                    @foreach(['yes', 'no', 'na'] as $optionKey)
                                                        <label class="flex items-center space-x-2 cursor-pointer">
                                                            <input
                                                                type="radio"
                                                                name="responses[{{ $question->id }}]"
                                                                value="{{ $optionKey }}"
                                                                class="form-radio"
                                                                required
                                                            >
                                                            <span>{{ __('surveys.checkbox_options.' . $optionKey) }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($templateType === 'checkboxes')
                                                <div class="space-y-2">
                                                    @php
                                                        $options = [
                                                            __('surveys.checkboxes_options.strongly_agree'),
                                                            __('surveys.checkboxes_options.agree'),
                                                            __('surveys.checkboxes_options.neutral'),
                                                            __('surveys.checkboxes_options.disagree'),
                                                            __('surveys.checkboxes_options.strongly_disagree')
                                                        ];
                                                    @endphp

                                                    @foreach($options as $option)
                                                        <label class="flex items-center space-x-2 cursor-pointer">
                                                            <input
                                                                type="checkbox"
                                                                name="responses[{{ $question->id }}][]"
                                                                value="{{ $option }}"
                                                                class="form-checkbox"
                                                            >
                                                            <span>{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Open Feedback Section -->
                        <div class="mb-6">
                            <label for="feedback" class="block font-medium mb-2">
                                {{ __('surveys.additional_comments') }}:
                            </label>
                            <textarea
                                id="feedback"
                                name="responses[feedback]"
                                rows="4"
                                class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-center">
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                {{ __('surveys.submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-survey-layout>
