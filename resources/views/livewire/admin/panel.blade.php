<div class="flex flex-col gap-2 p-20">
    <!-- Header -->
    <h1 class="text-2xl font-bold text-gray-700 dark:text-gray-200 px-2">
        {{ __('admin.admin_panel') }}
    </h1>

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-10 p-10">
        <!-- Users Section -->
        <section>
            <a href="admin/users">
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-xl font-bold text-gray-700 dark:text-gray-200">{{ __('admin.users') }}</h2>
                    <x-fas-arrow-right class="w-4 h-4" />
                </div>
            </a>
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
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $user->name }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Invite Tokens Section -->
        <section class="mt-8">
            <a href="{{ route('admin.invite-token') }}" class="flex flex-row gap-2 items-center text-gray-700 dark:text-gray-200 mb-4">
                <h2 class="text-xl font-bold">{{ __('admin.invite_tokens') }}</h2>
                <x-fas-arrow-right class="w-4 h-4"/>
            </a>

            <x-invite-token-list :items="$registerKeys" />
        </section>

        <!-- Add Subjects Section -->
        <section class="mt-8">
            <a href="{{ route('admin.options') }}">
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-xl font-bold text-gray-700 dark:text-gray-200">{{ __('admin.edit_options') }}</h2>
                    <x-fas-arrow-right class="w-4 h-4" />
                </div>
            </a>
        </section>
    </div>
</div>
