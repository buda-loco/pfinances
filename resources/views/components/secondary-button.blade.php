<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-light border fw-bold px-4']) }}>
    {{ $slot }}
</button>