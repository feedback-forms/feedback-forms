<div class="flex flex-col gap-2 p-20">
    <!-- '/admin-panel' -->
    <a class="flex flex-row gap-2 items-center w-fit text-2xl px-2" href="/admin-panel">
        <x-fas-arrow-left class="w-4 h-4 text-gray-500 dark:text-gray-300" />
        <span class="text-gray-500 dark:text-gray-400">{{__('surveys.surveys')}}</span>
    </a>

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-10 p-10">
        <div class="flex flex-col gap-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{__('surveys.umfragen')}}</h2>

            <!-- Filter dropdowns -->
            <div class="flex flex-row gap-4 flex-wrap">
                <div class="flex flex-col gap-1 min-w-60">
                    <label for="school-year" class="text-sm text-gray-600 dark:text-gray-400">{{__('surveys.schuljahr')}}</label>
                    <div class="relative">
                        <select
                            id="school-year"
                            wire:model="selectedSchoolYear"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-md p-2 pr-8 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 appearance-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">{{__('surveys.all_school_years')}}</option>
                            @foreach($schoolYears as $schoolYear)
                                <option value="{{ $schoolYear->name }}">{{ $schoolYear->name }}</option>
                            @endforeach
                        </select>
                        @if($selectedSchoolYear !== '')
                            <button
                                type="button"
                                class="absolute inset-y-0 right-2 flex items-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                                wire:click="clearSchoolYearFilter"
                            >
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-1 min-w-60">
                    <label for="department" class="text-sm text-gray-600 dark:text-gray-400">{{__('surveys.abteilung')}}</label>
                    <div class="relative">
                        <select
                            id="department"
                            wire:model="selectedDepartment"
                            class="w-full border border-gray-300 dark:border-gray-700 rounded-md p-2 pr-8 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 appearance-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">{{__('surveys.all_departments')}}</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->name }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                        @if($selectedDepartment !== '')
                            <button
                                type="button"
                                class="absolute inset-y-0 right-2 flex items-center text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                                wire:click="clearDepartmentFilter"
                            >
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-row gap-2 flex-wrap just-start">
            <button class="filter-button" id="surveys-filter-expired" filter-type="expired">{{__('surveys.expired')}}</button>
            <button class="filter-button" id="surveys-filter-running" filter-type="running">{{__('surveys.running')}}</button>
            <button class="filter-button" id="surveys-filter-cancelled" filter-type="cancelled">{{__('surveys.cancelled')}}</button>
        </div>

        <div class="flex flex-row gap-10 flex-wrap justify-center">
            @foreach($surveys as $survey)
                <div class="flex flex-col gap-2 lg:flex-[1_0_17%] md:flex-[1_0_30%] sm:flex-[1_0_100%] survey-wrapper" filter-type="{{ $survey->status ?? 'running' }}">
                    <div class="relative">
                        <img src="{{asset('img/preview.png')}}" alt="a" class="rounded-3xl" />
                        <div class="absolute top-2 right-2 flex gap-2">
                            <a href="{{ route('surveys.edit', ['id' => $survey->id]) }}" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full transition-colors">
                                <x-fas-edit class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                    <p class="text-ellipsis text-gray-600 dark:text-gray-500"><b>{{ $survey->feedback_template->title ?? 'Title' }}</b></p>
                    <p class="text-ellipsis text-gray-500 dark:text-gray-400">Updated {{ $survey->updated_at->diffForHumans() }}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $survey->already_answered }} / {{ $survey->limit == -1 ? '∞' : $survey->limit }} {{__('surveys.responses')}}
                        </span>
                        <a href="{{ route('surveys.edit', ['id' => $survey->id]) }}" class="text-blue-500 hover:text-blue-600 text-sm">
                            {{__('surveys.edit')}} →
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
