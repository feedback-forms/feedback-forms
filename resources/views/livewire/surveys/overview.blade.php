<div class="flex flex-col gap-2 p-20">
    <!-- '/admin-panel' -->
    <a class="flex flex-row gap-2 items-center w-fit text-2xl px-2" href="/admin-panel">
        <x-fas-arrow-left class="w-4 h-4 text-gray-500 dark:text-gray-300" />
        <span class="text-gray-500 dark:text-gray-400">{{__('surveys.surveys')}}</span>
    </a>

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-10 p-10">
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
                    <p class="text-ellipsis text-gray-600 dark:text-gray-500"><b>{{ $survey->name ?: ($survey->feedback_template->title ?? 'Untitled Survey') }}</b></p>
                    <p class="text-ellipsis text-gray-500 dark:text-gray-400">Updated {{ $survey->updated_at->diffForHumans() }}</p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $survey->already_answered }} / {{ $survey->limit == -1 ? '∞' : $survey->limit }} {{__('surveys.responses')}}
                        </span>
                        <div class="flex gap-2">
                            <a href="{{ route('surveys.statistics', ['survey' => $survey->id]) }}" class="text-green-500 hover:text-green-600 text-sm">
                                {{__('surveys.statistics')}} →
                            </a>
                            <a href="{{ route('surveys.edit', ['id' => $survey->id]) }}" class="text-blue-500 hover:text-blue-600 text-sm">
                                {{__('surveys.edit')}} →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
