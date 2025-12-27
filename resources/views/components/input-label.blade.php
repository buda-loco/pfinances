@props(['value'])

<label {{ $attributes->merge(['class' => 'form-label small fw-bold text-muted text-uppercase']) }}>
    {{ $value ?? $slot }}
</label>