@props([
    'items' => [],
    'showOptions' => false
])

@php
$show = fn($item) => true; // Always show options regardless of expiration status
@endphp

<div class="flex flex-col gap-4">
    @foreach($items as $item)
        <div class="flex items-center justify-between p-4 hover:bg-white dark:hover:bg-gray-700 rounded transition-colors"
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

            @if ($showOptions)
                <x-dropdown :contentClasses="'flex flex-col gap-1 p-2 bg-white dark:bg-gray-700'" :showDropdown="$show($item)">
                    <x-slot name="trigger">
                        <div class="p-2 rounded hover:bg-gray-100 cursor-pointer">
                            <x-fas-ellipsis-v class="w-4 h-4 text-gray-400 dark:text-gray-500 "/>
                        </div>
                    </x-slot>

                    <x-slot name="content">
                        @if (!$item->expire_at || $item->expire_at->isFuture())
                        <x-secondary-button class="w-full" wire:click="revokeToken({{$item->id}})">
                            {{__('invite_token.revoke')}}
                        </x-secondary-button>

                        <x-secondary-button
                            class="w-full"
                            wire:click="changeToCurrentToken({{$item->id}})"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'edit-invite_token')"
                        >
                            {{__('invite_token.change_duration')}}
                        </x-secondary-button>
                        @endif

                        <div x-data="{ isOpen: false }" class="relative">
                            <x-danger-button
                                class="w-full"
                                @click.stop="isOpen = !isOpen"
                            >
                                {{__('invite_token.delete')}}
                            </x-danger-button>

                            <div x-show="isOpen"
                                 @click.outside="isOpen = false"
                                 class="absolute z-10 left-0 mt-2 min-w-full rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 p-4"
                                 x-cloak>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                                    {{ __('invite_token.confirm_delete') }}
                                </p>
                                <div class="flex justify-end gap-2">
                                    <x-secondary-button @click.stop="isOpen = false" class="!px-3 !py-1">
                                        {{ __('invite_token.cancel') }}
                                    </x-secondary-button>
                                    <x-danger-button wire:click="deleteToken({{$item->id}})" @click.stop class="!px-3 !py-1">
                                        {{ __('invite_token.delete') }}
                                    </x-danger-button>
                                </div>
                            </div>
                        </div>
                    </x-slot>
                </x-dropdown>
            @endif
        </div>
    @endforeach
</div>
