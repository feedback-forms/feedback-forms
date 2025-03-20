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

        <div class="flex flex-row gap-10 flex-wrap justify-start">
            <template x-for="survey in filteredSurveys" :key="survey.id">
                <div class="flex flex-col gap-2 lg:w-[17%] md:w-[30%] sm:w-full min-w-0 survey-wrapper h-full" 
                     :filter-type="survey.isExpired ? 'expired' : 'running'">
                    <!-- Image container with edit button -->
                    <div class="flex flex-row items-start gap-3">
                        <div class="relative flex-grow">
                            <img src="{{asset('img/preview.png')}}" alt="a" class="rounded-3xl w-full" />
                        </div>
                        <!-- Edit button positioned beside the image -->
                        <div class="flex-shrink-0 -mt-3">
                            <a :href="`/surveys/${survey.id}/edit`" 
                               class="inline-flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-full transition-colors shadow-lg">
                                <x-fas-edit class="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                    
                    <!-- Content section -->
                    <div class="flex flex-col flex-grow">
                        <p class="text-ellipsis text-gray-600 dark:text-gray-500">
                            <b x-text="survey.name || (survey.feedback_template ? survey.feedback_template.title : 'Untitled Survey')"></b>
                        </p>
                        <p class="text-ellipsis text-gray-500 dark:text-gray-400" x-text="`Updated ${survey.updated_at_diff}`"></p>
                        
                        <!-- Responses section -->
                        <div class="flex items-center mt-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                <span x-text="survey.already_answered"></span> / <span x-text="survey.limit == -1 ? '∞' : survey.limit"></span> {{__('surveys.responses')}}
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
                        <a :href="`/surveys/${survey.id}/statistics`" class="text-green-500 hover:text-green-600 text-sm">
                            {{__('surveys.statistics')}} →
                        </a>
                        <a :href="`/surveys/${survey.id}/edit`" class="text-blue-500 hover:text-blue-600 text-sm">
                            {{__('surveys.edit')}} →
                        </a>
                    </div>
                </div>
            </template>

            <!-- Empty state when no surveys match filters -->
            <div x-show="filteredSurveys.length === 0" class="text-center py-16 w-full flex flex-col items-center justify-center">
                <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-6 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-gray-700 dark:text-gray-200 mb-2">{{__('surveys.no_surveys_found')}}</h3>
                <p class="text-gray-500 dark:text-gray-400 max-w-md">{{__('surveys.no_surveys_found_hint')}}</p>
            </div>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div
        x-data="{
            show: false,
            surveyUrl: '',
            currentAccesskey: ''
        }"
        @open-qr-modal.window="
            show = true;
            currentAccesskey = $event.detail.accesskey;
            $nextTick(() => {
                var url = new URL('{{ url(route('surveys.scan')) }}');
                url.searchParams.append('token', currentAccesskey);
                surveyUrl = url.toString();

                if (typeof window.QRCode !== 'undefined') {
                    try {
                        const canvas = document.getElementById('qrcode-canvas');
                        const loadingEl = document.getElementById('qrcode-loading');
                        const errorEl = document.getElementById('qrcode-error');

                        if (loadingEl) loadingEl.style.display = 'flex';
                        if (errorEl) errorEl.style.display = 'none';

                        window.QRCode.toCanvas(canvas, surveyUrl, {
                            width: 200,
                            margin: 1
                        }, function(error) {
                            if (loadingEl) loadingEl.style.display = 'none';

                            if (error) {
                                if (errorEl) errorEl.style.display = 'block';
                                console.error('QR code error:', error);
                            }
                        });
                    } catch(e) {
                        const loadingEl = document.getElementById('qrcode-loading');
                        const errorEl = document.getElementById('qrcode-error');

                        if (loadingEl) loadingEl.style.display = 'none';
                        if (errorEl) errorEl.style.display = 'block';

                        console.error('QR code generation failed:', e);
                    }
                } else {
                    const loadingEl = document.getElementById('qrcode-loading');
                    const errorEl = document.getElementById('qrcode-error');

                    if (loadingEl) loadingEl.style.display = 'none';
                    if (errorEl) errorEl.style.display = 'block';

                    console.error('QRCode library not loaded');
                }
            });
        "
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 flex items-center justify-center z-50 bg-gray-900 bg-opacity-50"
        style="display: none;"
    >
        <div @click.away="show = false" class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md mx-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{__('surveys.qr_code_title')}}</h3>
                <button @click="show = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            <div class="text-center mb-4">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{__('surveys.scan_to_access')}}</p>
                <div class="flex justify-center mb-3">
                    <div id="qrcode-container" class="relative">
                        <canvas id="qrcode-canvas" class="border border-gray-300 dark:border-gray-700"></canvas>
                        <!-- Loading state -->
                        <div id="qrcode-loading" class="absolute inset-0 flex items-center justify-center bg-white dark:bg-gray-800 bg-opacity-90 dark:bg-opacity-90" style="display: flex;">
                            <div class="text-blue-500">
                                <svg class="animate-spin h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="qrcode-error" style="display: none;" class="text-red-500 text-xs mb-3">
                    {{__('surveys.qr_code_error')}}
                </div>
                <div class="text-sm bg-gray-100 dark:bg-gray-700 p-2 rounded">
                    <p class="text-xs text-gray-700 dark:text-gray-300 break-all" x-text="currentAccesskey"></p>
                    <p class="text-xs text-gray-700 dark:text-gray-300 mt-1 break-all" x-text="surveyUrl"></p>
                </div>
            </div>
            <div class="flex justify-center">
                <button @click="show = false" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    {{__('surveys.close')}}
                </button>
            </div>
        </div>
    </div>
</div>
