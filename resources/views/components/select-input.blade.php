@props([
    'disabled' => false,
    'required' => false,
    'error' => null,
    'id' => null,
    'label' => null
])

@php
$selectId = $id ?? $attributes->get('name') ?? 'select-' . uniqid();
@endphp

@if($label)
<label for="{{ $selectId }}" class="form-label">
    {{ $label }}
    @if($required)
        <span class="text-danger" aria-label="required">*</span>
    @endif
</label>
@endif

<select
    id="{{ $selectId }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    aria-required="{{ $required ? 'true' : 'false' }}"
    {{ $error ? 'aria-invalid=true aria-describedby=' . $selectId . '-error' : '' }}
    {!! $attributes->merge(['class' => 'form-select' . ($error ? ' is-invalid' : '')]) !!}
>
    {{ $slot }}
</select>

@if($error)
    <div class="invalid-feedback d-block" id="{{ $selectId }}-error" role="alert">
        {{ $error }}
    </div>
@endif