<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head style="padding: 5px 5%; border-block-end: 1px solid black;">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="min-height: 100vh;height: 100vh;">

        <div class="min-h-screen pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900 h-full flex flex-col justify-between items-center">
            <div class="flex flex-col items-center justify-center m-auto gap-4">
                <a href="/" class="w-20 h-20">
                    <x-application-logo class="w-full fill-current text-gray-500" style="margin-inline: auto;height: 100%;width: fit-content;" />
                </a>

                <div class="w-full sm:max-w-md px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                    {{ $slot }}
                </div>
            </div>

            <x-footer />
        </div>
    </body>
</html>
