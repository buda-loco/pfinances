@props([
    'disabled' => false,
    'required' => false,
    'error' => null,
    'id' => null,
    'label' => null
])

@php
$textareaId = $id ?? $attributes->get('name') ?? 'textarea-' . uniqid();
@endphp

@if($label)
<label for="{{ $textareaId }}" class="form-label">
    {{ $label }}
    @if($required)
        <span class="text-danger" aria-label="required">*</span>
    @endif
</label>
@endif

<textarea
    id="{{ $textareaId }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $required ? 'required' : '' }}
    aria-required="{{ $required ? 'true' : 'false' }}"
    {{ $error ? 'aria-invalid=true aria-describedby=' . $textareaId . '-error' : '' }}
    {!! $attributes->merge(['class' => 'form-control' . ($error ? ' is-invalid' : '')]) !!}
>{{ $slot }}</textarea>

@if($error)
    <div class="invalid-feedback d-block" id="{{ $textareaId }}-error" role="alert">
        {{ $error }}
    </div>
@endif
