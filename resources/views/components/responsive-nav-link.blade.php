@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'list-group-item list-group-item-action border-0 py-2 px-3 fw-semibold text-primary bg-primary bg-opacity-10'
        : 'list-group-item list-group-item-action border-0 py-2 px-3 text-secondary';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>