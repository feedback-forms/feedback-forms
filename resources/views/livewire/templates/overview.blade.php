<div class="flex flex-col gap-2 p-20">

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-10 p-10">
        <!-- Templates Gallery Section -->
        <section>
            <h2 class="text-xl font-bold mb-4 text-gray-700 dark:text-gray-200">{{ __('templates.template_gallery') }}</h2>
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
                    x-show="scroll > 0"
                >
                    <x-fas-chevron-left class="w-4 h-4 text-gray-400" />
                </button>

                <!-- Right scroll button -->
                <button
                    class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 p-2 rounded-full bg-white dark:bg-gray-700 shadow-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-all opacity-0 group-hover:opacity-100 group-hover:translate-x-0 disabled:opacity-0"
                    @click="$refs.scrollContainer.scrollBy({ left: 300, behavior: 'smooth' }); setTimeout(() => updateScroll(), 500)"
                    x-show="scroll < maxScroll"
                >
                    <x-fas-chevron-right class="w-4 h-4 text-gray-400" />
                </button>

                <div
                    class="flex gap-6 overflow-x-auto pb-4 scroll-smooth [&::-webkit-scrollbar]:h-1.5
                           [&::-webkit-scrollbar-track]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-200
                           [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-400
                           dark:[&::-webkit-scrollbar-track]:bg-gray-700 dark:[&::-webkit-scrollbar-thumb]:bg-gray-500"
                    x-ref="scrollContainer"
                    @scroll.throttle="updateScroll()"
                >
                    @foreach($templates as $template)
                        <div class="flex-none w-64">
                            <div class="flex items-center aspect-video bg-white dark:bg-gray-700 rounded-lg mb-2 overflow-hidden shadow-sm relative group">
                                <img
                                    src="{{ asset($template['image']) }}"
                                    alt="{{ $template['title'] }}"
                                    class="w-auto h-[80%] mx-auto object-cover"
                                />

                                <!-- Preview and Create buttons overlay -->
                                <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-center items-center gap-2 p-4">
                                    @if(isset($template['route']))
                                        <a href="{{ $template['route'] }}" class="w-full px-3 py-1.5 bg-gray-800 text-white text-sm rounded hover:bg-gray-700 transition flex items-center justify-center">
                                            <x-fas-eye class="w-3 h-3 mr-1.5" />
                                            {{ __('templates.preview') }}
                                        </a>
                                    @endif

                                    @if(isset($template['create_url']))
                                        <a href="{{ $template['create_url'] }}" class="w-full px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-500 transition flex items-center justify-center">
                                            <x-fas-plus class="w-3 h-3 mr-1.5" />
                                            {{ __('templates.create_survey') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $template['title'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Featured Section -->
        <section class="mt-8">
            <h2 class="text-xl font-bold mb-4 text-gray-700 dark:text-gray-200">{{ __('templates.featured') }}</h2>
            <div class="flex flex-col gap-4">
                @foreach($featuredItems as $item)
                    <div class="flex items-start gap-4 p-4 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition-colors">
                        <div class="w-16 h-16 bg-white dark:bg-gray-700 rounded overflow-hidden flex-none shadow-sm">
                            @if(isset($item['route']))
                                <a href="{{ $item['route'] }}">
                                    <img
                                        src="{{ asset($item['image']) }}"
                                        alt="{{ $item['title'] }}"
                                        class="w-full h-full object-cover"
                                    />
                                </a>
                            @else
                                <img
                                    src="{{ asset($item['image']) }}"
                                    alt="{{ $item['title'] }}"
                                    class="w-full h-full object-cover"
                                />
                            @endif
                        </div>
                        <div class="flex-grow">
                            <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $item['title'] }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item['description'] ?? '' }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            @if(isset($item['route']))
                                <a href="{{ $item['route'] }}" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded flex items-center">
                                    <x-fas-eye class="w-4 h-4 text-gray-400 dark:text-gray-500 mr-1" />
                                    <span class="text-xs">{{ __('templates.preview') }}</span>
                                </a>
                            @endif

                            @if(isset($item['create_url']))
                                <a href="{{ $item['create_url'] }}" class="p-2 bg-blue-500 hover:bg-blue-600 text-white rounded flex items-center">
                                    <x-fas-plus class="w-4 h-4 mr-1" />
                                    <span class="text-xs">{{ __('templates.create_survey') }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</div>