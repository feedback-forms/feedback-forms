<x-survey-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold text-center mb-8">
                        {{ $survey->feedback_template->title ?? __('surveys.survey') }}
                    </h1>

                    <!-- Survey Information -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @if($survey->subject)
                                <div>
                                    <span class="font-semibold">{{ __('surveys.subject') }}:</span>
                                    <span>{{ $survey->subject }}</span>
                                </div>
                            @endif

                            @if($survey->grade_level)
                                <div>
                                    <span class="font-semibold">{{ __('surveys.grade_level') }}:</span>
                                    <span>{{ $survey->grade_level }}</span>
                                </div>
                            @endif

                            @if($survey->class)
                                <div>
                                    <span class="font-semibold">{{ __('surveys.class') }}:</span>
                                    <span>{{ $survey->class }}</span>
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

                    <form method="POST" action="{{ route('surveys.submit', $survey->accesskey) }}" id="surveyForm">
                        @csrf

                        <!-- Table Survey -->
                        <div class="overflow-x-auto mb-8">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-700">
                                        <th class="border px-4 py-2 text-left">{{ __('surveys.statement') }}</th>
                                        <th class="border px-4 py-2 text-center">{{ __('surveys.strongly_agree') }}</th>
                                        <th class="border px-4 py-2 text-center">{{ __('surveys.agree') }}</th>
                                        <th class="border px-4 py-2 text-center">{{ __('surveys.disagree') }}</th>
                                        <th class="border px-4 py-2 text-center">{{ __('surveys.strongly_disagree') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($survey->questions as $index => $question)
                                        <tr class="{{ $index % 2 == 0 ? 'bg-white dark:bg-gray-800' : 'bg-gray-50 dark:bg-gray-700' }}" data-question-id="{{ $question->id }}">
                                            <td class="border px-4 py-2">{{ $question->question }}</td>
                                            @foreach(range(1, 4) as $value)
                                                <td class="border px-4 py-2 text-center">
                                                    <input
                                                        type="radio"
                                                        name="responses[{{ $question->id }}]"
                                                        value="{{ $value }}"
                                                        class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out"
                                                        required
                                                    >
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

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

                        <div id="validation-error" class="hidden mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                            {{ __('surveys.please_answer_all_questions') }}
                        </div>

                        <div class="flex justify-end mt-6">
                            <x-primary-button type="submit">
                                {{ __('surveys.submit_response') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <script>
                        document.getElementById('surveyForm').addEventListener('submit', function(e) {
                            // Check if at least one radio button is selected for each question
                            const questions = document.querySelectorAll('tbody tr');
                            let valid = true;

                            questions.forEach(function(question) {
                                const radios = question.querySelectorAll('input[type="radio"]');
                                const checked = Array.from(radios).some(radio => radio.checked);

                                if (!checked) {
                                    valid = false;
                                    // Highlight the question that's missing an answer
                                    question.classList.add('bg-red-100', 'dark:bg-red-900');
                                } else {
                                    question.classList.remove('bg-red-100', 'dark:bg-red-900');
                                }
                            });

                            if (!valid) {
                                e.preventDefault();
                                document.getElementById('validation-error').classList.remove('hidden');
                                // Scroll to the first unanswered question
                                const firstUnanswered = document.querySelector('tr.bg-red-100, tr.dark:bg-red-900');
                                if (firstUnanswered) {
                                    firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
</x-survey-layout>