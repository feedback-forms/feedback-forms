@props(['message' => null, 'hint' => null])

<div class="text-center py-16 w-full flex flex-col items-center justify-center">
    <div class="bg-gray-100 dark:bg-gray-700 rounded-full p-6 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    <h3 class="text-xl font-medium text-gray-700 dark:text-gray-200 mb-2">{{ $message ?? __('surveys.no_surveys_found') }}</h3>
    <p class="text-gray-500 dark:text-gray-400 max-w-md">{{ $hint ?? __('surveys.no_surveys_found_hint') }}</p>
</div>