@props(['type', 'label'])

<button class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-medium text-sm transition-colors"
        x-bind:class="filters.{{ $type }} ? 'bg-blue-500 text-white dark:bg-blue-600' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600'"
        id="surveys-filter-{{ $type }}"
        filter-type="{{ $type }}"
        @click="toggleFilter('{{ $type }}')"
        x-bind:aria-pressed="filters.{{ $type }}">
    {{ $label }}
</button>