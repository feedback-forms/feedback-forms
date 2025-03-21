{{-- Emotion Feedback Card Component --}}
{{-- Parameters:
    $title - Card title
    $responses - Array of text responses
    $iconClass - Icon class for the emotion (e.g., 'text-green-500' for positive)
    $iconComponent - The component name for the icon (e.g., 'far-face-smile')
    $noResponsesText - Text to show when no responses are available
--}}

<div class="p-6 border rounded-lg bg-white dark:bg-gray-800 shadow-sm">
    <div class="flex items-center mb-4">
        <x-dynamic-component :component="$iconComponent" class="w-8 h-8 {{ $iconClass }} mr-2" />
        <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200">{{ $title }}</h4>
    </div>

    @if(collect($responses)->count() > 0)
        <div class="space-y-2 max-h-96 overflow-y-auto">
            @foreach($responses as $response)
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    {{ $response }}
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400 italic">{{ $noResponsesText }}</p>
    @endif
</div>