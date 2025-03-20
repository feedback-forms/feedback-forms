@props(['id', 'label', 'options' => [], 'model', 'allLabel'])

<div>
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
    <select id="{{ $id }}"
            x-model="filters.{{ $model }}"
            @change="logFilters()"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        <option value="">{{ $allLabel }}</option>
        @foreach($options as $option)
            <option value="{{ $option->name }}">{{ $option->name }}</option>
        @endforeach
    </select>
</div>