<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h2 class="text-2xl font-bold mb-6">{{ __('surveys.create_new_survey') }}</h2>

                    <form method="POST" action="{{ route('surveys.store') }}">
                        @csrf

                        <!-- Survey Name -->
                        <div class="mb-6">
                            <x-input-label for="name" :value="__('surveys.survey_name')" />
                            <x-text-input id="name"
                                         name="name"
                                         type="text"
                                         class="mt-1 block w-full"
                                         required
                                         autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Template Selection (hidden since it's pre-selected) -->
                        <input type="hidden" name="template_id" value="{{ optional($templates->where('name', 'templates.feedback.' . $selectedTemplate)->first())->id ?? '' }}">

                        <!-- School Year -->
                        <div class="mb-6">
                            <x-input-label for="school_year" :value="__('surveys.select_school_year')" />
                            <select id="school_year"
                                    name="school_year_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                <option value="">{{ __('surveys.select_school_year_placeholder') }}</option>
                                @foreach($schoolYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('school_year')" class="mt-2" />
                        </div>

                        <!-- Department -->
                        <div class="mb-6">
                            <x-input-label for="department" :value="__('surveys.select_department')" />
                            <select id="department"
                                    name="department_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                <option value="">{{ __('surveys.select_department_placeholder') }}</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('department')" class="mt-2" />
                        </div>

                        <!-- Grade Level -->
                        <div class="mb-6">
                            <x-input-label for="grade_level" :value="__('surveys.select_grade_level')" />
                            <select id="grade_level"
                                    name="grade_level_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                <option value="">{{ __('surveys.select_grade_level_placeholder') }}</option>
                                @foreach($gradeLevels as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('grade_level')" class="mt-2" />
                        </div>

                        <!-- Class Selection -->
                        <div class="mb-6">
                            <x-input-label for="class" :value="__('surveys.select_class')" />
                            <select id="class"
                                    name="school_class_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                <option value="">{{ __('surveys.select_class_placeholder') }}</option>
                                @foreach($schoolClasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('class')" class="mt-2" />
                        </div>

                        <!-- Subject Selection -->
                        <div class="mb-6">
                            <x-input-label for="subject" :value="__('surveys.select_subject')" />
                            <select id="subject"
                                    name="subject_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                <option value="">{{ __('surveys.select_subject_placeholder') }}</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>

                        <!-- Response Limit -->
                        <div class="mb-6">
                            <x-input-label for="response_limit" :value="__('surveys.response_limit')" />
                            <x-text-input
                                id="response_limit"
                                name="response_limit"
                                type="number"
                                min="-1"
                                class="mt-1 block w-full"
                                :value="old('response_limit', -1)"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('surveys.response_limit_help') }}
                            </p>
                            <x-input-error :messages="$errors->get('response_limit')" class="mt-2" />
                        </div>

                        <!-- Expiration Date -->
                        <div class="mb-6">
                            <x-input-label for="expire_date" :value="__('surveys.expire_date')" />
                            <x-text-input
                                id="expire_date"
                                name="expire_date"
                                type="datetime-local"
                                class="mt-1 block w-full"
                                required
                            />
                            <x-input-error :messages="$errors->get('expire_date')" class="mt-2" />
                        </div>

                        <!-- Template Information Section -->
                        <div class="mb-6">
                            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <h3 class="text-lg font-semibold mb-2">{{ __('surveys.template_information') }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                    {{ __('surveys.template_info_description') }}
                                </p>
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 mr-3">
                                        <img src="{{ asset('img/preview.png') }}" alt="Template preview" class="w-16 h-16 object-cover rounded">
                                    </div>
                                    <div>
                                        <h4 class="font-medium">{{ __('templates.feedback.' . $selectedTemplate) }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('templates.' . $selectedTemplate . '_feedback_description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4">
                            <x-secondary-button type="button" onclick="window.history.back()">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-primary-button>
                                {{ __('surveys.create_survey') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize any needed JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Set default expiration date to 30 days from now
            const expireDate = document.getElementById('expire_date');
            if (expireDate) {
                const date = new Date();
                date.setDate(date.getDate() + 30);
                expireDate.value = date.toISOString().slice(0, 16);
            }
        });
    </script>
    @endpush
</x-app-layout>
