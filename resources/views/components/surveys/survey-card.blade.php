@props(['survey'])

<div class="flex flex-col gap-2 lg:w-[17%] md:w-[30%] sm:w-full min-w-0 survey-wrapper h-full"
     x-bind:filter-type="survey.isExpired ? 'expired' : 'running'">
    <!-- Image container with edit button -->
    <div class="flex flex-row items-start gap-3">
        <div class="relative flex-grow">
            <img src="{{asset('img/preview.png')}}" alt="Survey preview" class="rounded-3xl w-full h-auto object-contain" style="max-height: 150px;" />
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
                x-text="survey.statusText"
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