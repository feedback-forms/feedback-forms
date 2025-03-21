<div class="flex flex-col gap-2 p-20">

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
                <x-surveys.filter-dropdown
                    id="schoolYear"
                    label="{{__('surveys.school_year')}}"
                    :options="$schoolYears"
                    model="schoolYear"
                    allLabel="{{__('surveys.all_school_years')}}"
                />

                <!-- Department Filter -->
                <x-surveys.filter-dropdown
                    id="department"
                    label="{{__('surveys.department')}}"
                    :options="$departments"
                    model="department"
                    allLabel="{{__('surveys.all_departments')}}"
                />

                <!-- Grade Level Filter -->
                <x-surveys.filter-dropdown
                    id="gradeLevel"
                    label="{{__('surveys.grade_level')}}"
                    :options="$gradeLevels"
                    model="gradeLevel"
                    allLabel="{{__('surveys.all_grade_levels')}}"
                />

                <!-- Class Filter -->
                <x-surveys.filter-dropdown
                    id="class"
                    label="{{__('surveys.class')}}"
                    :options="$schoolClasses"
                    model="class"
                    allLabel="{{__('surveys.all_classes')}}"
                />

                <!-- Subject Filter -->
                <x-surveys.filter-dropdown
                    id="subject"
                    label="{{__('surveys.subject')}}"
                    :options="$subjects"
                    model="subject"
                    allLabel="{{__('surveys.all_subjects')}}"
                />
            </div>
        </div>

        <div class="flex flex-row gap-2 flex-wrap just-start">
            <x-surveys.filter-button
                type="expired"
                label="{{__('surveys.expired')}}"
            />
            <x-surveys.filter-button
                type="running"
                label="{{__('surveys.running')}}"
            />
        </div>

        <div class="flex flex-row gap-10 flex-wrap justify-start">
            <!-- Survey Cards -->
            <template x-for="survey in filteredSurveys" :key="survey.id">
                <div class="flex flex-col gap-2 lg:w-[17%] md:w-[30%] sm:w-full min-w-0 survey-wrapper h-full"
                     x-bind:filter-type="survey.isExpired ? 'expired' : 'running'">
                    <!-- Image container with edit button -->
                    <div class="flex flex-row items-start gap-3">
                        <div class="relative flex-grow">
                            <img src="{{asset('img/preview.png')}}" alt="Survey preview" class="rounded-3xl w-full h-auto object-contain" style="max-height: 100px;" />
                        </div>
                        <!-- Edit button positioned beside the image -->
                        <div class="flex-shrink-0 -mt-3">
                            <a x-bind:href="`/surveys/${survey.id}/edit`"
                               class="inline-flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-full transition-colors shadow-lg">
                                <x-fas-edit class="w-5 h-5" />
                            </a>
                        </div>
                    </div>

                    <!-- Content section -->
                    <div class="flex flex-col flex-grow">
                        <p class="text-ellipsis text-gray-600 dark:text-gray-500">
                            <b x-text="survey.name || (survey.feedback_template ? survey.feedback_template.title : '{{__('surveys.untitled_survey')}}')"></b>
                        </p>
                        <p class="text-ellipsis text-gray-500 dark:text-gray-400" x-text="`Updated ${survey.updated_at_diff}`"></p>

                        <!-- Display survey status with proper styling -->
                        <p class="text-sm mt-1">
                            <span
                                x-text="survey.isExpired ? '{{__('surveys.status.expired')}}' : (survey.isRunning ? '{{__('surveys.status.running')}}' : '{{__('surveys.status.cancelled')}}')"
                                x-bind:class="{
                                    'text-red-500': survey.isExpired,
                                    'text-green-500': survey.isRunning,
                                    'text-gray-500': !survey.isExpired && !survey.isRunning
                                }"
                                class="font-medium"></span>
                        </p>

                        <!-- Responses section -->
                        <div class="flex items-center mt-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                <span x-text="survey.submission_count"></span> / <span x-text="survey.limit == -1 ? '∞' : survey.limit"></span> {{__('surveys.responses')}}
                            </span>
                            <button type="button"
                                @click="window.dispatchEvent(new CustomEvent('open-qr-modal', { detail: { accesskey: survey.accesskey }}))"
                                class="ml-2 px-2 py-1 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded">
                                {{__('surveys.show_qr')}}
                            </button>
                        </div>
                    </div>

                    <!-- Footer section -->
                    <div class="flex justify-start gap-2 mt-auto pt-2">
                        <a x-bind:href="`/surveys/${survey.id}/statistics`" class="text-green-500 hover:text-green-600 text-sm">
                            {{__('surveys.statistics')}} →
                        </a>
                        <a x-bind:href="`/surveys/${survey.id}/edit`" class="text-blue-500 hover:text-blue-600 text-sm">
                            {{__('surveys.edit')}} →
                        </a>
                    </div>
                </div>
            </template>

            <!-- Empty state when no surveys match filters -->
            <div x-show="filteredSurveys.length === 0">
                <x-surveys.empty-state />
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <x-surveys.qr-code-modal routeName="surveys.scan" />
</div>
