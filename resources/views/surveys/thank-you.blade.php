<x-guest-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>

                        <h1 class="mt-3 text-2xl font-bold">{{ __('surveys.thank_you') }}</h1>

                        <p class="mt-4">{{ __('surveys.response_received') }}</p>

                        <div class="mt-8">
                            <a href="{{ route('surveys.access') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ __('surveys.access_another_survey') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>