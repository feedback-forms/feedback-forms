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
            <h2 class="text-xl font-bold mb-4 text-gray-700 dark:text-gray-200">{{ __('admin.invite_tokens') }}</h2>

            <!-- Success Message -->
            @if($successMessage)
                <div class="mb-4 p-3 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 rounded-lg relative">
                    {{ $successMessage }}
                    <button wire:click="clearMessage" class="absolute top-3 right-3 text-green-700 dark:text-green-300 hover:text-green-900 dark:hover:text-green-100">
                        <x-fas-times class="w-4 h-4" />
                    </button>
                </div>
            @endif

            <!-- Create Token Button -->
            <div class="mb-4">
                <button
                    wire:click="createToken"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                >
                    <x-fas-plus class="w-4 h-4" />
                    <span>{{ __('admin.create_new_token') }}</span>
                    <span wire:loading wire:target="createToken" class="ml-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </div>

            <div class="flex flex-col gap-4">
                @forelse($registerkeys as $registerkey)
                    <div class="flex items-center justify-between p-4 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition-colors"
                         x-data="{ showToken: false, showOptions: false }">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <div class="font-mono text-gray-700 dark:text-gray-300 w-36 flex justify-end">
                                    <span x-show="!showToken">••••••••••</span>
                                    <span x-show="showToken" x-cloak>{{ $registerkey->code }}</span>
                                </div>
                                <div class="w-8 h-8 flex items-center justify-center">
                                    <button class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-full transition-colors"
                                            @click="showToken = !showToken">
                                        <x-fas-eye x-show="!showToken" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                                        <x-fas-eye-slash x-show="showToken" x-cloak class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                                    </button>
                                </div>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('admin.created_at', ['date' => $registerkey->created_at->diffForHumans()]) }}
                            </span>
                        </div>
                        <div class="relative">
                            <button @click="showOptions = !showOptions" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                                <x-fas-ellipsis-v class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="showOptions"
                                 @click.away="showOptions = false"
                                 x-cloak
                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 shadow-lg rounded-lg py-1 z-10"
                            >
                                <button
                                    wire:click="revokeToken({{ $registerkey->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="revokeToken({{ $registerkey->id }})"
                                    @click="showOptions = false"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-600 flex items-center gap-2"
                                >
                                    <x-fas-trash-alt class="w-4 h-4" />
                                    <span>{{ __('admin.revoke_token') }}</span>
                                    <span wire:loading wire:target="revokeToken({{ $registerkey->id }})" class="ml-2">
                                        <svg class="animate-spin h-3 w-3 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-gray-500 dark:text-gray-400 text-center">
                        {{ __('admin.no_tokens_available') }}
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>