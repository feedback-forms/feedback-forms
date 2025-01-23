<x-secondary-button
    {{$attributes->merge([])}}

    @class([
        '!bg-indigo-100' => $attributes->get('chip-checked') === 'true',
        '!py-1'
    ])
>
    @if ($attributes->get('chip-checked') === 'true')
        <x-fas-check class="w-3 h-3 mr-2" />
    @endif

    {{ $slot }}
</x-secondary-button>
