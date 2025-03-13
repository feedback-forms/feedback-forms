<x-guest-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-2xl font-bold text-center mb-8">
                        {{ $survey->feedback_template->title ?? __('surveys.survey') }}
                    </h1>

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

                        <!-- Survey Information -->
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h1 class="text-2xl font-bold mb-4 text-center">
                                {{ $survey->feedback_template->title ?? __('surveys.survey') }}
                            </h1>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
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

                        <!-- Survey Questions -->
                        <div class="space-y-8">
                            @foreach($survey->questions->sortBy('order') as $question)
                                <div class="p-4 border rounded-lg">
                                    <h3 class="font-semibold mb-3">{{ $question->order }}. {{ $question->question }}</h3>

                                    @php
                                        $templateType = $question->question_template->type ?? 'text';
                                    @endphp

                                    @if($templateType === 'smiley')
                                        <div class="flex justify-between items-center">
                                            @foreach(['😡', '😕', '😐', '🙂', '😄'] as $index => $smiley)
                                                <label class="flex flex-col items-center cursor-pointer">
                                                    <span class="text-3xl mb-2">{{ $smiley }}</span>
                                                    <input type="radio" name="responses[{{ $question->id }}]" value="{{ $index + 1 }}" class="form-radio" required>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($templateType === 'target')
                                        <div class="flex justify-center">
                                            <div class="relative w-64 h-64">
                                                @foreach(range(5, 1, -1) as $value)
                                                    <div class="absolute inset-0 rounded-full border-2 border-gray-300 dark:border-gray-600"
                                                        style="transform: scale({{ $value / 5 }})">
                                                    </div>
                                                @endforeach

                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <div class="space-y-2">
                                                        @foreach(range(1, 5) as $value)
                                                            <label class="flex items-center space-x-2 cursor-pointer">
                                                                <input type="radio" name="responses[{{ $question->id }}]" value="{{ $value }}" class="form-radio" required>
                                                                <span>{{ $value }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($templateType === 'checkbox')
                                        <div class="space-y-2">
                                            @foreach(['Yes', 'No', 'Not applicable'] as $index => $option)
                                                <label class="flex items-center space-x-2 cursor-pointer">
                                                    <input type="radio" name="responses[{{ $question->id }}]" value="{{ $option }}" class="form-radio" required>
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @elseif($templateType === 'table')
                                        <div class="overflow-x-auto">
                                            <table class="w-full">
                                                <thead>
                                                    <tr class="border-b">
                                                        <th class="text-center py-2">{{ __('surveys.strongly_agree') }}</th>
                                                        <th class="text-center py-2">{{ __('surveys.agree') }}</th>
                                                        <th class="text-center py-2">{{ __('surveys.disagree') }}</th>
                                                        <th class="text-center py-2">{{ __('surveys.strongly_disagree') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        @foreach(range(1, 4) as $value)
                                                            <td class="text-center py-2">
                                                                <input type="radio" name="responses[{{ $question->id }}]" value="{{ $value }}" class="form-radio" required>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <textarea name="responses[{{ $question->id }}]" rows="3" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
                                    @endif
                                </div>
                            @endforeach
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