<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans">
        <div class="bg-gray-100 text-black/50 dark:bg-gray-900 dark:text-white/50">
            <main>
                <div class="min-h-dvh flex flex-col items-center justify-center gap-8 selection:bg-[#FF2D20] selection:text-white">
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>

                    <h2 class="text-2xl font-medium text-gray-900 dark:text-gray-100">
                        {{__('welcome.vote_now')}}!
                    </h2>

                    <div class="lg:max-w-1/2 sm:max-w-2/3 w-full px-6">
                        <!-- Session Status -->
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <!-- Validation Errors -->
                        @if ($errors->any())
                            <div class="mb-4 text-center">
                                <div class="font-medium text-red-600 dark:text-red-400 mb-2">
                                    {{ __('surveys.whoops') }}
                                </div>

                                <div class="mt-2 text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-4 rounded-lg shadow-sm">
                                    @foreach ($errors->all() as $error)
                                        <p class="mb-1 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            {{ $error }}
                                        </p>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Success Message -->
                        @if (session('success'))
                            <div class="mb-4 text-center">
                                <div class="font-medium text-sm text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 p-4 rounded-lg shadow-sm flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ session('success') }}
                                </div>
                            </div>
                        @endif

                        <!-- Error Message -->
                        @if (session('error'))
                            <div class="mb-4 text-center">
                                <div class="font-medium text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-4 rounded-lg shadow-sm flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    {{ session('error') }}
                                </div>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('surveys.access.submit') }}">
                            @csrf
                            <div class="flex flex-row gap-2 justify-center">
                                <x-text-input id="token"
                                              class="border-t-0 border-l-0 border-r-0 border-b-2 bg-transparent !shadow-none rounded-none focus:ring-0 focus:!border-gray-500 dark:focus:!border-gray-500 text-center"
                                              placeholder="{{__('welcome.key')}}" type="text" name="token"
                                              :value="old('token')"
                                              required
                                              autofocus
                                              autocomplete="off"
                                />
                                <x-primary-button>
                                    <x-fas-arrow-right class="w-4 h-4"/>
                                </x-primary-button>
                            </div>
                            <p class="mt-4 text-sm text-center text-gray-600 dark:text-gray-400">{{ __('surveys.enter_access_key_hint') }}</p>
                        </form>
                    </div>
                </div>
            </main>
        </div>

        @php
            // Consider both feedback-forms-test and localhost as test environments
            $appUrl = config('app.url');
            $isTestEnv = str_contains($appUrl, 'feedback-forms-test') || str_contains($appUrl, 'localhost');

            // Get SHA from environment variable (set during container build)
            $gitSha = env('GIT_SHA');

            // If we have a full SHA, trim it to short format (7 characters)
            if ($gitSha && strlen($gitSha) > 7) {
                $gitSha = substr($gitSha, 0, 7);
            }

            // Fallback to reading from .git directory only in local development
            if (!$gitSha && str_contains($appUrl, 'localhost')) {
                $gitHeadPath = base_path('.git/HEAD');
                if (file_exists($gitHeadPath)) {
                    $gitHead = file_get_contents($gitHeadPath);
                    if (strpos($gitHead, 'ref:') === 0) {
                        $ref = trim(substr($gitHead, 5));
                        $gitRefPath = base_path('.git/' . $ref);
                        if (file_exists($gitRefPath)) {
                            $gitSha = trim(file_get_contents($gitRefPath));
                        }
                    } else {
                        $gitSha = trim($gitHead);
                    }
                    if ($gitSha) {
                        $gitSha = substr($gitSha, 0, 7); // Short SHA
                    }
                }
            }
        @endphp

        @if ($isTestEnv && $gitSha)
            <div class="fixed bottom-2 right-2 bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs px-2 py-1 rounded-md opacity-70">
                SHA: {{ $gitSha }}
            </div>
        @endif
    </body>
</html>
