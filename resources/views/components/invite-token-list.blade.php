@props([
    'items' => []
])

<div class="flex flex-col gap-4">
    @foreach($items as $item)
        <div class="flex items-center justify-between p-4 hover:bg-white dark:hover:bg-gray-700 rounded-lg transition-colors"
             x-data="{ showToken: false }">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-2">
                    <div class="font-mono text-gray-700 dark:text-gray-300 w-28 flex">
                        <span x-show="!showToken">•••••••••</span>
                        <span x-show="showToken" x-cloak>{{ $item->code }}</span>
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
                    @if ($item->expire_at)
                        @if ($item->expire_at->isPast())
                            <span class="text-red-500 back:text-red-700 font-bold">{{ __('invite_token.expired_time_ago', ['time' => $item->expire_at->diffForHumans(null, true)]) }},</span>
                        @else
                            {{ __('invite_token.expires_in', ['time' => $item->expire_at->diffForHumans(null, true)]) }},
                        @endif
                    @endif

                    {{ __('invite_token.created_time_ago', ['time' => $item->created_at->diffForHumans(null, true)]) }}
                </span>
            </div>
            <button class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                <x-fas-ellipsis-v class="w-4 h-4 text-gray-400 dark:text-gray-500" />
            </button>
        </div>
    @endforeach
</div>
