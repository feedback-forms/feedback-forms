<div class="flex flex-col gap-2 p-20">
    <!-- Header -->
    <h1 class="text-2xl font-bold text-gray-700 dark:text-gray-200 px-2">
        {{ __('admin.admin_panel') }}
    </h1>

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-10 p-10">
        <!-- Users Section -->
        <section>
            <h2 class="text-xl font-bold mb-4 text-gray-700 dark:text-gray-200">{{ __('admin.users') }}</h2>
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
                    @foreach($users as $user)
                        <div class="flex-none">
                            <div class="flex flex-col items-center gap-2">
                                <div class="w-16 h-16 rounded-full bg-white dark:bg-gray-700 flex items-center justify-center shadow-sm">
                                    <x-fas-user class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                                </div>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $user['name'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Invite Tokens Section -->
        <section class="mt-8">
            <h2 class="text-xl font-bold mb-4 text-gray-700 dark:text-gray-200">{{ __('admin.invite_tokens') }}</h2>
            <div class="flex flex-col gap-4">
                @for($i = 0; $i < 3; $i++)
                    <div class="flex items-center justify-between p-4 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition-colors"
                         x-data="{ showToken: false }">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <div class="font-mono text-gray-700 dark:text-gray-300">
                                    <span x-show="!showToken">••••••••••</span>
                                    <span x-show="showToken" x-cloak>EXAMPLE-TOKEN-{{ $i }}</span>
                                </div>
                                <button class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-full transition-colors"
                                        @click="showToken = !showToken">
                                    <x-fas-eye x-show="!showToken" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                                    <x-fas-eye-slash x-show="showToken" x-cloak class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                                </button>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('admin.created_days_ago', ['days' => rand(1, 10)]) }}
                            </span>
                        </div>
                        <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                            <x-fas-ellipsis-v class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                        </button>
                    </div>
                @endfor
            </div>
        </section>
    </div>
</div>