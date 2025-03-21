{{-- Survey Information Component --}}
{{-- Parameters:
    $survey - The survey object
--}}

<h3 class="text-xl font-semibold mb-4 text-indigo-700 dark:text-indigo-300">{{ __('surveys.survey_details') }}</h3>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-10">
    <div class="bg-gray-50 dark:bg-gray-700 p-5 rounded-lg shadow-sm">
        <p class="mb-2"><span class="font-semibold">{{ __('surveys.survey_title') }}:</span> {{ $survey->name ?: ($survey->feedback_template->title ?? 'N/A') }}</p>
        <p class="mb-2"><span class="font-semibold">{{ __('surveys.access_key') }}:</span> {{ $survey->accesskey }}</p>
        <p><span class="font-semibold">{{ __('surveys.created_at') }}:</span> {{ $survey->created_at->format('d.m.Y') }}</p>
    </div>
    <div class="bg-gray-50 dark:bg-gray-700 p-5 rounded-lg shadow-sm">
        <p class="mb-2"><span class="font-semibold">{{ __('surveys.responses') }}:</span> {{ $survey->submission_count }} / {{ $survey->limit == -1 ? '∞' : $survey->limit }}</p>
        <p class="mb-2"><span class="font-semibold">{{ __('surveys.expires') }}:</span> {{ $survey->expire_date->format('d.m.Y H:i') }}</p>
        <p><span class="font-semibold">{{ __('surveys.status') }}:</span>
            @if($survey->expire_date->isPast())
                <span class="text-red-500 font-medium">{{ __('surveys.expired') }}</span>
            @else
                <span class="text-green-500 font-medium">{{ __('surveys.active') }}</span>
            @endif
        </p>
    </div>
</div>