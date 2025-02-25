<div class="flex flex-col gap-4 p-20">
    <a class="flex flex-row gap-2 items-center w-fit text-2xl px-2" href="{{ route('surveys.list') }}">
        <x-fas-arrow-left class="w-4 h-4 text-gray-500 dark:text-gray-300" />
        <span class="text-gray-500 dark:text-gray-400">{{__('surveys.back_to_surveys')}}</span>
    </a>

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-6 p-10 rounded-lg">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{__('surveys.edit_survey')}}</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form wire:submit.prevent="save" class="flex flex-col gap-6">
            <!-- Template Information -->
            <div class="flex flex-col gap-2">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300">{{__('surveys.template_information')}}</h2>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $survey->feedback_template->title ?? 'Template' }}
                </p>
            </div>

            <!-- School Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label for="school_year" class="text-gray-700 dark:text-gray-300">{{__('surveys.select_school_year')}}</label>
                    <select id="school_year" wire:model="school_year" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{__('surveys.select_school_year_placeholder')}}</option>
                        @foreach($schoolYears as $year)
                            <option value="{{ $year->name }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                    @error('school_year') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="department" class="text-gray-700 dark:text-gray-300">{{__('surveys.select_department')}}</label>
                    <select id="department" wire:model="department" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{__('surveys.select_department_placeholder')}}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="grade_level" class="text-gray-700 dark:text-gray-300">{{__('surveys.select_grade_level')}}</label>
                    <select id="grade_level" wire:model="grade_level" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{__('surveys.select_grade_level_placeholder')}}</option>
                        @foreach($gradeLevels as $level)
                            <option value="{{ $level->name }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                    @error('grade_level') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="class" class="text-gray-700 dark:text-gray-300">{{__('surveys.select_class')}}</label>
                    <select id="class" wire:model="class" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{__('surveys.select_class_placeholder')}}</option>
                        @foreach($schoolClasses as $schoolClass)
                            <option value="{{ $schoolClass->name }}">{{ $schoolClass->name }}</option>
                        @endforeach
                    </select>
                    @error('class') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="subject" class="text-gray-700 dark:text-gray-300">{{__('surveys.select_subject')}}</label>
                    <select id="subject" wire:model="subject" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{__('surveys.select_subject_placeholder')}}</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->name }}">{{ $subj->name }}</option>
                        @endforeach
                    </select>
                    @error('subject') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Survey Settings -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label for="expire_date" class="text-gray-700 dark:text-gray-300">{{__('surveys.expire_date')}}</label>
                    <input type="datetime-local" id="expire_date" wire:model="expire_date" class="form-input rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    @error('expire_date') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="response_limit" class="text-gray-700 dark:text-gray-300">{{__('surveys.response_limit')}}</label>
                    <input type="number" id="response_limit" wire:model="response_limit" class="form-input rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" min="-1">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{__('surveys.response_limit_help')}}</p>
                    @error('response_limit') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Survey Status Information -->
            <div class="flex flex-col gap-2 bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{__('surveys.survey_status')}}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">{{__('surveys.access_key')}}: <span class="font-mono font-bold">{{ $survey->accesskey }}</span></p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">{{__('surveys.responses')}}: {{ $survey->already_answered }} / {{ $survey->limit == -1 ? '∞' : $survey->limit }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">{{__('surveys.created_at')}}: {{ $survey->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">{{__('surveys.updated_at')}}: {{ $survey->updated_at->format('Y-m-d H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors">
                    {{__('surveys.save_changes')}}
                </button>
            </div>
        </form>
    </div>
</div>
