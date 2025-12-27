@props([
    'disabled' => false,
    'checked' => false,
    'error' => null,
    'id' => null,
    'label' => null
])

@php
$inputId = $id ?? $attributes->get('name') ?? 'checkbox-' . uniqid();
@endphp

<div class="form-check">
    <input
        type="checkbox"
        id="{{ $inputId }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $checked ? 'checked' : '' }}
        {{ $error ? 'aria-invalid=true aria-describedby=' . $inputId . '-error' : '' }}
        {{ $attributes->merge(['class' => 'form-check-input' . ($error ? ' is-invalid' : '')]) }}
    >
    @if($label)
    <label class="form-check-label" for="{{ $inputId }}">
        {{ $label }}
    </label>
    @endif

    @if($error)
        <div class="invalid-feedback d-block" id="{{ $inputId }}-error" role="alert">
            {{ $error }}
        </div>
    @endif
</div>