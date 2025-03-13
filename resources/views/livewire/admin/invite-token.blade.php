<div class="flex flex-col gap-2 p-20">
    <div class="flex justify-between">
        <a href="{{ route('admin.panel') }}" class="flex flex-row gap-2 items-center w-fit text-2xl px-2">
            <x-fas-arrow-left class="w-4 h-4 text-gray-500 dark:text-gray-300"/>
            <span class="text-gray-500 dark:text-gray-400">{{ __('admin.invite_tokens') }}</span>
        </a>

        <button
            class="p-2 rounded-lg bg-indigo-100 dark:bg-indigo-900"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'add-invite_token')"
            wire:click="generateToken"
        >
            <x-fas-plus class="w-4 h-4 text-gray-500 dark:text-gray-300"/>
        </button>
    </div>

    <div class="bg-gray-50 dark:bg-gray-800 flex flex-col gap-6 p-10">
        <x-invite-token-list :items="$registerKeys" />
    </div>

    <x-modal name="add-invite_token" focusable>
        <form wire:submit="addToken" class="p-6">

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('invite_token.add_token') }}
            </h2>

            <div class="flex flex-col gap-4 mt-6">
                <div class="flex justify-between dark:text-gray-100">
                    <span>{{__('invite_token.token')}}</span>
                    <span class="font-mono">{{ $token }}</span>
                </div>

                <div class="flex justify-between dark:text-gray-100">
                    <span>{{__('invite_token.duration')}}</span>

                    <x-text-input wire:model="duration" type="number" name="duration" required min="1" />
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('invite_token.cancel') }}
                </x-secondary-button>

                <x-primary-button class="ms-3" x-on:click="$dispatch('close')">
                    {{ __('invite_token.create') }}
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
