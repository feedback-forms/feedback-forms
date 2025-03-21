{{-- Statistics Card Component --}}
{{-- Parameters:
    $title - Card title
    $subtitle (optional) - Additional description text
    $class (optional) - Additional CSS classes
--}}

<div class="p-6 border rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow duration-300 {{ $class ?? '' }}">
    <h4 class="font-semibold text-lg text-gray-800 dark:text-gray-200 mb-1">{{ $title }}</h4>

    @if(isset($subtitle))
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $subtitle }}</p>
    @endif

    {{ $slot }}
</div>