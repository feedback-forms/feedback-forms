@php
    use Carbon\CarbonInterval;
@endphp
<div class="flex flex-col gap-2 p-20">
    <!-- Header with back button -->
    <a href="/admin-panel" class="flex flex-row gap-2 items-center w-fit text-2xl px-2">
        <x-fas-arrow-left class="w-4 h-4 text-gray-500 dark:text-gray-300" />
        <span class="text-gray-500 dark:text-gray-400">{{ __('admin.users_management') }}</span>
    </a>

    <!-- Success message for temporary password -->
    @if(session()->has('temporary_password_success'))
        @php
            $successData = session('temporary_password_success');
        @endphp
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">{{ __('admin.temporary_password_generated') }}!</strong>
            <span class="block sm:inline">
                {{ __('admin.temporary_password_for_user', ['name' => $successData['userName']]) }}:
                <span class="font-mono text-green-500 font-bold">{{ $successData['password'] }}</span>.
                {{ __('admin.temporary_password_notice') }}
            </span>
            <p class="mt-2 text-sm font-semibold">{{ __('admin.temporary_password_one_time_use') }}</p>
        </div>
    @endif

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
                        <span class="font-bold text-gray-900 dark:text-gray-100">{{ $user->name }}</span>
                        <span class="text-gray-500 dark:text-gray-400 text-ellipsis overflow-hidden">{{ $user->email }}</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            @php
                                $passwordChanged = Carbon\Carbon::parse($user->updated_at);
                                $passwordChangedDiff = $passwordChanged->diffForHumans();
                            @endphp
                            {{ __('admin.password_changed') }} {{ $passwordChangedDiff }}
                        </span>
                    </div>
                </div>

                <!-- Password Section -->
                <div class="flex items-center gap-2">
                    <div class="relative flex items-center">
                        <div class="font-mono text-gray-700 dark:text-gray-300 w-28 flex justify-end overflow-hidden">
                            @if(session()->has('temporary_password_success') && session('temporary_password_success')['userId'] === $user->id)
                                <span class="text-green-500">{{ session('temporary_password_success')['password'] }}</span>
                            @else
                                <span>••••••••</span>
                            @endif
                        </div>
                        <div class="w-8 h-8 flex items-center justify-center" x-data="{ showTooltip: false }">
                            <button class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-full transition-colors relative"
                                    wire:click="generateTemporaryPassword({{ $user->id }})"
                                    @mouseenter="showTooltip = true"
                                    @mouseleave="showTooltip = false">
                                <x-fas-key class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                                <div x-show="showTooltip" x-cloak class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-800 text-white text-xs rounded shadow-lg z-10 w-48">
                                    {{ __('admin.generate_temporary_password') }}
                                </div>
                            </button>
                        </div>
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