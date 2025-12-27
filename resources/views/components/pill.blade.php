@props([
    'variant' => 'default', // default, success, warning, danger, info, primary
    'size' => 'sm' // sm, md
])
@php
    $baseClasses = 'badge rounded-pill fw-bold border-0 px-3';

    $sizeClasses = match ($size) {
        'sm' => 'extra-small',
        'md' => 'small',
        default => 'extra-small'
    };

    $variantClasses = match ($variant) {
        'success' => 'bg-success bg-opacity-10 text-success',
        'warning' => 'bg-warning bg-opacity-10 text-warning',
        'danger' => 'bg-danger bg-opacity-10 text-danger',
        'info' => 'bg-info bg-opacity-10 text-info',
        'primary' => 'bg-primary bg-opacity-10 text-primary',
        default => 'bg-secondary bg-opacity-10 text-secondary'
    };
@endphp

<span {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses", 'style' => 'letter-spacing: 0.02em;']) }}>
    {{ $slot }}
</span>
