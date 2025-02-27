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
            @foreach($items as $item)
                <!-- filter-type="running" ist hier nur als Beispiel drinnen. "running" dann zu dem tatsächlichen Status der survey ändern. (expired, running, cancelled) -->
                <div class="flex flex-col gap-2 lg:flex-[1_0_17%] md:flex-[1_0_30%] sm:flex-[1_0_100%] survey-wrapper" filter-type="running">
                    <img src="{{asset('img/preview.png')}}" alt="a" class="rounded-3xl" />
                    <p class="text-ellipsis text-gray-600 dark:text-gray-500"><b>Title</b></p>
                    <p class="text-ellipsis text-gray-500 dark:text-gray-400">Updated today Updated today Updated today Updated today Updated today</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
