<x-guest-layout>
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

                    <form method="POST" action="{{ route('surveys.submit', $survey->accesskey) }}">
                        @csrf

                        <!-- Checkbox Survey -->
                        <div class="space-y-8 mb-8">
                            @foreach([
                                'Der Unterricht ist gut vorbereitet.',
                                'Die Aufgaben sind klar formuliert.',
                                'Die Lehrkraft erklärt verständlich.',
                                'Die Lehrkraft geht auf Fragen ein.',
                                'Die Lehrkraft gibt konstruktives Feedback.',
                                'Die Lehrkraft ist fair und respektvoll.',
                                'Die Unterrichtsmaterialien sind hilfreich.',
                                'Der Unterricht ist interessant gestaltet.'
                            ] as $index => $statement)
                                <div class="p-4 border rounded-lg bg-white dark:bg-gray-800">
                                    <h3 class="font-semibold mb-3">{{ $statement }}</h3>
                                    <div class="space-y-2">
                                        @foreach(['Ja', 'Nein', 'Keine Angabe'] as $optionIndex => $option)
                                            <label class="flex items-center space-x-2 cursor-pointer">
                                                <input
                                                    type="radio"
                                                    name="responses[{{ $index }}]"
                                                    value="{{ $option }}"
                                                    class="form-radio"
                                                    required
                                                >
                                                <span>{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
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

                        <div class="flex justify-end mt-6">
                            <x-primary-button>
                                {{ __('surveys.submit_response') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>