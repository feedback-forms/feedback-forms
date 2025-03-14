<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('surveys.list') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('surveys.list')" :active="request()->routeIs('surveys.list')" wire:navigate>
                        {{ __('surveys.surveys') }}
                    </x-nav-link>

                    <x-nav-link :href="route('templates.list')" :active="request()->routeIs('templates.list')" wire:navigate>
                        {{ __('templates.templates') }}
                    </x-nav-link>

                    @can('admin')
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <x-dropdown>
                                <x-slot name="trigger">
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                        <div>{{__('admin.admin_tools')}}</div>

                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                      clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('admin.panel')" wire:navigate
                                                     :active="request()->routeIs('admin.panel')">
                                        {{ __('admin.admin_panel') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('admin.users')" wire:navigate
                                                     :active="request()->routeIs('admin.users')">
                                        {{ __('admin.users') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('admin.invite-token')" wire:navigate
                                                     :active="request()->routeIs('admin.invite-token')">
                                        {{ __('admin.invite_tokens') }}
                                    </x-dropdown-link>

                                    <x-dropdown-link :href="route('admin.options')" wire:navigate
                                                     :active="request()->routeIs('admin.options')">
                                        {{ __('admin.options') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile')" wire:navigate>
                                {{ __('navigation.profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <button wire:click="logout" class="w-full text-start">
                                <x-dropdown-link>
                                    {{ __('navigation.logout') }}
                                </x-dropdown-link>
                            </button>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 dark:text-gray-300 underline">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-700 dark:text-gray-300 underline">{{ __('Register') }}</a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('surveys.list')" :active="request()->routeIs('surveys.list')" wire:navigate>
                {{ __('surveys.surveys') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('templates.list')" :active="request()->routeIs('templates.list')" wire:navigate>
                {{ __('templates.templates') }}
            </x-responsive-nav-link>

            @can('admin')
                <details class="text-gray-800 dark:text-gray-400 [&_summary]:open:bg-gray-50 [&_summary]:dark:open:bg-gray-700 [&_svg]:open:-rotate-180"
                 {{ in_array(request()->route()->getName(), ['admin.panel', 'admin.users']) ? 'open' : '' }}
                >
                    <summary class="list-none flex justify-between items-center px-4 py-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                        <div>{{__('admin.admin_tools')}}</div>
                        <x-fas-arrow-down class="w-4 h-4 transition" />
                    </summary>
                    <x-responsive-nav-link :href="route('admin.panel')" wire:navigate :active="request()->routeIs('admin.panel')">
                        {{ __('admin.admin_panel') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.users')" wire:navigate :active="request()->routeIs('admin.users')">
                        {{ __('admin.users') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.invite-token')" wire:navigate :active="request()->routeIs('admin.invite-token')">
                        {{ __('admin.invite_tokens') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('admin.options')" wire:navigate :active="request()->routeIs('admin.options')">
                        {{ __('admin.options') }}
                    </x-responsive-nav-link>
                </details>
            @endcan
        </div>

        @auth
            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        {{ __('navigation.profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            {{ __('navigation.logout') }}
                        </x-responsive-nav-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-responsive-nav-link>
                                {{ __('Log Out') }}
                            </x-responsive-nav-link>
                        </button>
                    </button>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('Log in') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                </div>
            </div>
        @endauth
    </div>
</nav>
