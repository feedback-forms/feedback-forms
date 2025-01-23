<div class="flex flex-col gap-2 p-20">
    <!-- '/admin-panel' -->
    <a class="flex flex-row gap-2 items-center w-fit text-2xl px-2" href="/admin-panel">
        <x-fas-arrow-left class="w-4 h-4 text-gray-500 dark:text-gray-300" />
        <span class="text-gray-500 dark:text-gray-400">{{__('surveys.surveys')}}</span>
    </a>

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-10 p-10">
        <div class="flex flex-row gap-2 flex-wrap just-start">
            <x-chip chip-checked="{{$filterState['expired'] ? 'true' : 'false'}}" wire:click="filter('expired')">{{__('surveys.expired')}}</x-chip>
            <x-chip chip-checked="{{$filterState['running'] ? 'true' : 'false'}}" wire:click="filter('running')">{{__('surveys.running')}}</x-chip>
            <x-chip chip-checked="{{$filterState['cancelled'] ? 'true' : 'false'}}" wire:click="filter('cancelled')">{{__('surveys.cancelled')}}</x-chip>
        </div>

        <div class="flex flex-row gap-10 flex-wrap justify-center">
            @foreach($items as $item)
                <div class="flex flex-col gap-2 lg:flex-[1_0_17%] md:flex-[1_0_30%] sm:flex-[1_0_100%]">
                    <img src="{{asset('img/preview.png')}}" alt="a" class="rounded-3xl" />
                    <p class="text-ellipsis text-gray-600 dark:text-gray-500"><b>Title</b></p>
                    <p class="text-ellipsis text-gray-500 dark:text-gray-400">Updated today Updated today Updated today Updated today Updated today</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
