@props([
    'disabled' => false,
    'required' => false,
    'error' => null,
    'id' => null,
    'label' => null
])

@php
$inputId = $id ?? $attributes->get('name') ?? 'input-' . uniqid();
@endphp

@if($label)
<label for="{{ $inputId }}" class="form-label">
    {{ $label }}
    @if($required)
        <span class="text-danger" aria-label="required">*</span>
    @endif
</label>
@endif

<input
    id="{{ $inputId }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    aria-required="{{ $required ? 'true' : 'false' }}"
    {{ $error ? 'aria-invalid=true aria-describedby=' . $inputId . '-error' : '' }}
    {!! $attributes->merge(['class' => 'form-control' . ($error ? ' is-invalid' : '')]) !!}>

@if($error)
    <div class="invalid-feedback d-block" id="{{ $inputId }}-error" role="alert">
        {{ $error }}
    </div>
@endif
