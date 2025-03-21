<div class="flex flex-col gap-2 p-20">
    <!-- Header -->
    <a class="flex flex-row gap-2 items-center w-fit text-2xl px-2" href="/admin-panel">
        <x-fas-arrow-left class="w-4 h-4 text-gray-500 dark:text-gray-300" />
        <span class="text-gray-500 dark:text-gray-400">{{__('templates.templates')}}</span>
    </a>

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-10 p-10">
        <!-- Templates Gallery Section -->
        <section>
            <h2 class="text-xl font-bold mb-4 text-gray-700 dark:text-gray-200">{{ __('Templates') }}</h2>
            <div class="relative group" x-data="{
                scroll: 0,
                maxScroll: 0,
                updateScroll() {
                    this.scroll = $refs.scrollContainer.scrollLeft;
                    this.maxScroll = $refs.scrollContainer.scrollWidth - $refs.scrollContainer.clientWidth;
                }
            }" x-init="updateScroll()">
                <!-- Left scroll button -->
                <button
                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 p-2 rounded-full bg-white dark:bg-gray-700 shadow-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-all opacity-0 group-hover:opacity-100 group-hover:translate-x-0 disabled:opacity-0"
                    @click="$refs.scrollContainer.scrollBy({ left: -300, behavior: 'smooth' }); setTimeout(() => updateScroll(), 500)"
                    x-show="scroll > 0">
                    <x-fas-chevron-left class="w-4 h-4 text-gray-400" />
                </button>

                <!-- Right scroll button -->
                <button
                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 p-2 rounded-full bg-white dark:bg-gray-700 shadow-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-all opacity-0 group-hover:opacity-100 group-hover:translate-x-0 disabled:opacity-0"
                    @click="$refs.scrollContainer.scrollBy({ left: 300, behavior: 'smooth' }); setTimeout(() => updateScroll(), 500)"
                    x-show="scroll < maxScroll">
                    <x-fas-chevron-right class="w-4 h-4 text-gray-400" />
                </button>

                <div class="flex gap-6 overflow-x-auto pb-4 scroll-smooth [&::-webkit-scrollbar]:h-1.5
                           [&::-webkit-scrollbar-track]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-200
                           [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-400
                           dark:[&::-webkit-scrollbar-track]:bg-gray-700 dark:[&::-webkit-scrollbar-thumb]:bg-gray-500"
                    x-ref="scrollContainer" @scroll.throttle="updateScroll()">
                    @foreach($templates as $template)
                    <a href="#" title="{{ __('teacherInterface.overview.featured-item') }}" class="flex-none w-20">
                        <div
                            class="w-20 h-20 bg-white dark:bg-gray-700 rounded-full mb-2 overflow-hidden shadow-sm flex items-center justify-center">
                            <img src="{{ asset($template['image']) }}" alt="{{ $template['title'] }}"
                                class="w-full h-full object-cover rounded-full" />
                        </div>
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-300 text-center">{{
                            $template['title'] }}</p>
                    </a>

                    @endforeach
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-bold mb-4 text-gray-700 dark:text-gray-200">{{ __('Surveys') }}</h2>
            <div class="relative group flex flex-row whitespace-nowrap" style="white-space: nowrap;" x-data="{
                scrollSurveys: 0,
                maxScrollSurveys: 0,
                updateScrollSurveys() {
                    this.scrollSurveys = $refs.scrollContainerSurveys.scrollLeft;
                    this.maxScrollSurveys = $refs.scrollContainerSurveys.scrollWidth - $refs.scrollContainerSurveys.clientWidth;
                }
            }" x-init="updateScrollSurveys()">
                <!-- Left scroll button -->
                <button
                    class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 p-2 rounded-full bg-white dark:bg-gray-700 shadow-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-all opacity-0 group-hover:opacity-100 group-hover:translate-x-0 disabled:opacity-0"
                    @click="$refs.scrollContainerSurveys.scrollBy({ left: -300, behavior: 'smooth' }); setTimeout(() => updateScrollSurveys(), 500)"
                    x-show="scrollSurveys > 0">
                    <x-fas-chevron-left class="w-4 h-4 text-gray-400" />
                </button>

                <!-- Right scroll button -->
                <button
                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 p-2 rounded-full bg-white dark:bg-gray-700 shadow-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-all opacity-0 group-hover:opacity-100 group-hover:translate-x-0 disabled:opacity-0"
                    @click="$refs.scrollContainerSurveys.scrollBy({ left: 300, behavior: 'smooth' }); setTimeout(() => updateScrollSurveys(), 500)"
                    x-show="scrollSurveys < maxScrollSurveys">
                    <x-fas-chevron-right class="w-4 h-4 text-gray-400" />
                </button>

                <div class="flex gap-6 overflow-x-auto pb-4 scroll-smooth [&::-webkit-scrollbar]:h-1.5
                           [&::-webkit-scrollbar-track]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-200
                           [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-400
                           dark:[&::-webkit-scrollbar-track]:bg-gray-700 dark:[&::-webkit-scrollbar-thumb]:bg-gray-500"
                    x-ref="scrollContainerSurveys" @scroll.throttle="updateScrollSurveys()">
                    @foreach($featuredItems as $item)
                    <a href="#" title="{{ __('teacherInterface.overview.item') }}" style="width: 15rem;"
                        class="min-w-3xs w-3xs flex items-start gap-4 flex-col p-4 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <div
                            class="w-3xs h-auto min-w-3xs bg-white dark:bg-gray-700 rounded overflow-hidden flex-none shadow-sm">
                            <img src="{{ asset($item['image']) }}" alt="{{ $item['title'] }}"
                                class="w-full h-full object-cover" />
                        </div>
                        <div class="flex-grow" style="white-space: normal;">
                            <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $item['title'] }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item['description'] }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </section>

    </div>
</div>