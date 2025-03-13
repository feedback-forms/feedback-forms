<div class="flex flex-col gap-2 p-20">
    <!-- '/admin-panel' -->
    <a class="flex flex-row gap-2 items-center w-fit text-2xl px-2" href="/admin-panel">
        <x-fas-arrow-left class="w-4 h-4 text-gray-500 dark:text-gray-300" />
        <span class="text-gray-500 dark:text-gray-400">{{__('surveys.surveys')}}</span>
    </a>

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-10 p-10"
         x-data="surveysFilter()"
         x-init="init()"
         data-surveys="{{ json_encode($surveys) }}"
         data-expired="{{ $filterState['expired'] ? 'true' : 'false' }}"
         data-running="{{ $filterState['running'] ? 'true' : 'false' }}"
         data-filter-options="{{ json_encode([
             'schoolYears' => $schoolYears,
             'departments' => $departments,
             'gradeLevels' => $gradeLevels,
             'schoolClasses' => $schoolClasses,
             'subjects' => $subjects
         ]) }}">
        <!-- Debug info (remove in production) -->
        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-xs" x-show="false">
            <pre x-text="JSON.stringify($data, null, 2)"></pre>
        </div>
        <!-- Additional Filter Options -->
        <div class="flex flex-col gap-4">
            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300">{{__('surveys.filter_options')}}</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- School Year Filter -->
                <div>
                    <label for="schoolYear" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('surveys.school_year')}}</label>
                    <select id="schoolYear"
                            x-model="filters.schoolYear"
                            @change="logFilters()"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">{{__('surveys.all_school_years')}}</option>
                        @foreach($schoolYears as $year)
                            <option value="{{ $year->name }}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Department Filter -->
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('surveys.department')}}</label>
                    <select id="department"
                            x-model="filters.department"
                            @change="logFilters()"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">{{__('surveys.all_departments')}}</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->name }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Grade Level Filter -->
                <div>
                    <label for="gradeLevel" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('surveys.grade_level')}}</label>
                    <select id="gradeLevel"
                            x-model="filters.gradeLevel"
                            @change="logFilters()"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">{{__('surveys.all_grade_levels')}}</option>
                        @foreach($gradeLevels as $level)
                            <option value="{{ $level->name }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Class Filter -->
                <div>
                    <label for="class" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('surveys.class')}}</label>
                    <select id="class"
                            x-model="filters.class"
                            @change="logFilters()"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">{{__('surveys.all_classes')}}</option>
                        @foreach($schoolClasses as $class)
                            <option value="{{ $class->name }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Subject Filter -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{__('surveys.subject')}}</label>
                    <select id="subject"
                            x-model="filters.subject"
                            @change="logFilters()"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">{{__('surveys.all_subjects')}}</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->name }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="flex flex-row gap-2 flex-wrap just-start">
            <button class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-medium text-sm transition-colors"
                    :class="filters.expired ? 'bg-blue-500 text-white dark:bg-blue-600' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600'"
                    id="surveys-filter-expired"
                    filter-type="expired"
                    @click="toggleFilter('expired')"
                    :aria-pressed="filters.expired">
                {{__('surveys.expired')}}
            </button>
            <button class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-medium text-sm transition-colors"
                    :class="filters.running ? 'bg-blue-500 text-white dark:bg-blue-600' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600'"
                    id="surveys-filter-running"
                    filter-type="running"
                    @click="toggleFilter('running')"
                    :aria-pressed="filters.running">
                {{__('surveys.running')}}
            </button>
        </div>

        <div class="flex flex-row gap-10 flex-wrap justify-center">
            <template x-for="survey in filteredSurveys" :key="survey.id">
                <div class="flex flex-col gap-2 lg:flex-[1_0_17%] md:flex-[1_0_30%] sm:flex-[1_0_100%] survey-wrapper" :filter-type="survey.isExpired ? 'expired' : 'running'">
                    <div class="relative">
                        <img src="{{asset('img/preview.png')}}" alt="a" class="rounded-3xl" />
                        <div class="absolute top-2 right-2 flex gap-2">
                            <a :href="`/surveys/${survey.id}/edit`" class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full transition-colors">
                                <x-fas-edit class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                    <p class="text-ellipsis text-gray-600 dark:text-gray-500">
                        <b x-text="survey.name || (survey.feedback_template ? survey.feedback_template.title : 'Untitled Survey')"></b>
                    </p>
                    <p class="text-ellipsis text-gray-500 dark:text-gray-400" x-text="`Updated ${survey.updated_at_diff}`"></p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            <span x-text="survey.already_answered"></span> / <span x-text="survey.limit == -1 ? '∞' : survey.limit"></span> {{__('surveys.responses')}}
                        </span>
                        <div class="flex gap-2">
                            <a :href="`/surveys/${survey.id}/statistics`" class="text-green-500 hover:text-green-600 text-sm">
                                {{__('surveys.statistics')}} →
                            </a>
                            <a :href="`/surveys/${survey.id}/edit`" class="text-blue-500 hover:text-blue-600 text-sm">
                                {{__('surveys.edit')}} →
                            </a>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty state when no surveys match filters -->
            <div x-show="filteredSurveys.length === 0" class="text-center py-10 w-full">
                <p class="text-gray-500 dark:text-gray-400">{{__('surveys.no_surveys_found')}}</p>
            </div>
        </div>
    </div>
</div>
