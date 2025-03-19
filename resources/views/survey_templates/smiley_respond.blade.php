<x-survey-layout>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    <!-- Error Message -->
    @if (session('error'))
        <div class="mb-4 font-medium text-sm text-red-600 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div x-data="smileyFeedback()">
        <form method="POST" action="{{ route('surveys.submit', $survey->accesskey) }}">
            @csrf
            <input type="hidden" name="responses" x-bind:value="JSON.stringify({positive: positive, negative: negative})">

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                            <!-- Survey Information -->
                            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <h1 class="text-2xl font-bold mb-4">
                                    {{ $survey->feedback_template->title ?? __('surveys.survey') }}
                                </h1>
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

                            <div class="grid grid-cols-2 gap-4">
                                <div style="display: flex; justify-content: center">
                                    <x-far-face-smile class="w-20 h-20" />
                                </div>
                                <div style="display: flex; justify-content: center">
                                    <x-far-face-frown class="w-20 h-20" />
                                </div>
                                <div>
                                    <textarea id="positive" rows="4" x-model="positive" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{ __('surveys.smiley.positive') }}"></textarea>
                                </div>
                                <div>
                                    <textarea id="negative" rows="4" x-model="negative" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="{{ __('surveys.smiley.negative') }}"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end pr-6 pb-6">
                            <x-primary-button type="submit">
                                {{ __('surveys.smiley.button') }} <x-fas-arrow-right class="w-6 h-6" />
                            </x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('smileyFeedback', () => ({
                positive: '',
                negative: '',
            }));
        });
    </script>
    @endpush
</x-survey-layout>
