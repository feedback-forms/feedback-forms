<x-app-layout>
    <x-slot name="title">
        {{ __('title.survey.checkbox') }}
    </x-slot>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="fixed bottom-8 right-8 z-50">
        <form action="{{ route('surveys.create') }}" method="GET">
            <input type="hidden" name="template" value="checkbox">
            <x-primary-button class="gap-2 text-base py-3 px-6 shadow-lg">
                {{ __('templates.use_template') }}
                <x-fas-arrow-right class="w-4 h-4" />
            </x-primary-button>
        </form>
    </div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h2 class="text-xl font-semibold mb-4">{{ __('templates.checkbox_feedback') }}</h2>
                    <div class="space-y-4 mb-6">
                        <div class="p-4 border rounded-lg bg-white dark:bg-gray-700">
                            <h3 class="font-semibold mb-3">1. Der Unterricht ist gut vorbereitet.</h3>
                            <div class="space-y-2">
                                @php
                                    $options = [
                                        'Yes' => __('surveys.checkbox_options.yes'),
                                        'No' => __('surveys.checkbox_options.no'),
                                        'Not applicable' => __('surveys.checkbox_options.na')
                                    ];
                                @endphp

                                @foreach($options as $value => $label)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" name="sample_question_1" value="{{ $value }}" class="form-radio">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="p-4 border rounded-lg bg-white dark:bg-gray-700">
                            <h3 class="font-semibold mb-3">2. Die Aufgaben sind klar formuliert.</h3>
                            <div class="space-y-2">
                                @php
                                    $options = [
                                        'Yes' => __('surveys.checkbox_options.yes'),
                                        'No' => __('surveys.checkbox_options.no'),
                                        'Not applicable' => __('surveys.checkbox_options.na')
                                    ];
                                @endphp

                                @foreach($options as $value => $label)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" name="sample_question_2" value="{{ $value }}" class="form-radio">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="p-4 border rounded-lg bg-white dark:bg-gray-700">
                            <h3 class="font-semibold mb-3">3. Die Lehrkraft erklärt verständlich.</h3>
                            <div class="space-y-2">
                                @php
                                    $options = [
                                        'Yes' => __('surveys.checkbox_options.yes'),
                                        'No' => __('surveys.checkbox_options.no'),
                                        'Not applicable' => __('surveys.checkbox_options.na')
                                    ];
                                @endphp

                                @foreach($options as $value => $label)
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" name="sample_question_3" value="{{ $value }}" class="form-radio">
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>