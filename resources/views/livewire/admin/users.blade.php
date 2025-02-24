<div class="flex flex-col gap-2 p-20">
    <!-- Header with back button -->
    <a href="/admin-panel" class="flex flex-row gap-2 items-center w-fit text-2xl px-2">
        <x-fas-arrow-left class="w-4 h-4 text-gray-500 dark:text-gray-300" />
        <span class="text-gray-500 dark:text-gray-400">{{ __('admin.users_management') }}</span>
    </a>

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-6 p-10">
        <!-- Users List -->
        @foreach($users as $user)
            <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-6 flex items-center justify-between gap-4"
                 x-data="{ showPassword: false }">
                <!-- User Info Section -->
                <div class="flex items-center gap-4 flex-grow">
                    <!-- Avatar -->
                    <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-600 flex items-center justify-center flex-shrink-0 overflow-hidden">
                        <x-fas-user class="w-8 h-8 text-gray-400 dark:text-gray-500 transform scale-75" />
                    </div>

                    <!-- User Details -->
                    <div class="flex flex-col gap-1 min-w-0">
                        <span class="font-bold text-gray-900 dark:text-gray-100">{{ $user['name'] }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            @if($user['password_changed'] === 0)
                                {{ __('admin.password_changed_today') }}
                            @elseif($user['password_changed'] === 1)
                                {{ __('admin.password_changed_week_ago') }}
                            @else
                                {{ __('admin.password_changed_weeks_ago', ['count' => $user['password_changed']]) }}
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="flex items-center gap-2">
                    <div class="relative flex items-center">
                        <span class="font-mono text-gray-700 dark:text-gray-300">
                            <span x-show="!showPassword">••••••••</span>
                            <span x-show="showPassword" x-cloak>password123</span>
                        </span>
                        <button class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-full transition-colors ml-2"
                                @click="showPassword = !showPassword">
                            <x-fas-eye x-show="!showPassword" class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                            <x-fas-eye-slash x-show="showPassword" x-cloak class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                        </button>
                    </div>

                    <!-- Settings Button -->
                    <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-full transition-colors">
                        <x-fas-cog class="w-5 h-5 text-gray-400 dark:text-gray-500" />
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>