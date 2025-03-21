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
            <!-- Survey Name -->
            <div class="flex flex-col gap-2">
                <label for="name" class="text-gray-700 dark:text-gray-300">{{__('surveys.survey_name')}}</label>
                <input type="text" id="name" wire:model="name" class="form-input rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
            </div>

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
                            <option value="{{ $year->id }}" wire:key="year-{{$year->id}}">{{ $year->name }}</option>
                        @endforeach
                    </select>
                    @error('school_year') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="department" class="text-gray-700 dark:text-gray-300">{{__('surveys.select_department')}}</label>
                    <select id="department" wire:model="department" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{__('surveys.select_department_placeholder')}}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" wire:key="department-{{$dept->id}}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    @error('department') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="grade_level" class="text-gray-700 dark:text-gray-300">{{__('surveys.select_grade_level')}}</label>
                    <select id="grade_level" wire:model="grade_level" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{__('surveys.select_grade_level_placeholder')}}</option>
                        @foreach($gradeLevels as $level)
                            <option value="{{ $level->id }}" wire:key="gradelevel-{{$level->id}}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                    @error('grade_level') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="class" class="text-gray-700 dark:text-gray-300">{{__('surveys.select_class')}}</label>
                    <select id="class" wire:model="class" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{__('surveys.select_class_placeholder')}}</option>
                        @foreach($schoolClasses as $schoolClass)
                            <option value="{{ $schoolClass->id }}" wire:key="schoolclass-{{$schoolClass->id}}">{{ $schoolClass->name }}</option>
                        @endforeach
                    </select>
                    @error('class') <span class="text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label for="subject" class="text-gray-700 dark:text-gray-300">{{__('surveys.select_subject')}}</label>
                    <select id="subject" wire:model="subject" class="form-select rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">{{__('surveys.select_subject_placeholder')}}</option>
                        @foreach($subjects as $subj)
                            <option value="{{ $subj->id }}" wire:key="subject-{{$subj->id}}">{{ $subj->name }}</option>
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
                        <p class="text-gray-600 dark:text-gray-400">{{__('surveys.access_key')}}: <span class="font-mono font-bold">{{ $survey->accesskey }}</span>
                            <button type="button" @click="window.dispatchEvent(new CustomEvent('open-qr-modal'))" class="ml-2 px-2 py-1 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded">
                                {{__('surveys.show_qr')}}
                            </button>
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">{{__('surveys.responses')}}: {{ $survey->submission_count }} / {{ $survey->limit == -1 ? '∞' : $survey->limit }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">{{__('surveys.created_at')}}: {{ $survey->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">{{__('surveys.updated_at')}}: {{ $survey->updated_at->format('d.m.Y H:i') }}</p>
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

    <!-- QR Code Modal -->
    <div
        x-data="{
            show: false,
            surveyUrl: '{{ url(route('surveys.scan', ['token' => $survey->accesskey], false)) }}'
        }"
        @open-qr-modal.window="
            show = true;
            $nextTick(() => {
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
                    <p class="text-xs text-gray-700 dark:text-gray-300 break-all">{{ $survey->accesskey }}</p>
                    <p class="text-xs text-gray-700 dark:text-gray-300 mt-1 break-all">{{ url(route('surveys.scan', ['token' => $survey->accesskey], false)) }}</p>
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
