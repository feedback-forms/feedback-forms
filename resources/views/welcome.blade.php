<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans">
        <div class="bg-gray-100 text-black/50 dark:bg-gray-900 dark:text-white/50">
            <main>
                <div class="min-h-dvh flex flex-col items-center justify-center gap-12 selection:bg-[#FF2D20] selection:text-white">
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>

                    <h2 class="text-2xl font-medium text-gray-900 dark:text-gray-100">
                        {{__('welcome.vote_now')}}!
                    </h2>

                    <div class="lg:max-w-1/2 sm:max-w-2/3">
                        <form method="POST" action="/">
                            @csrf
                            <div class="flex flex-row gap-2 justify-center">
                                <x-text-input id="token"
                                              class="border-t-0 border-l-0 border-r-0 border-b-2 bg-transparent !shadow-none rounded-none focus:ring-0 focus:!border-gray-500 dark:focus:!border-gray-500 text-center"
                                              placeholder="{{__('welcome.key')}}" type="text" name="token"
                                              required
                                              autocomplete="off"
                                />
                                <x-primary-button>
                                    <x-fas-arrow-right class="w-4 h-4"/>
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
