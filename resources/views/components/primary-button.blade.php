<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary fw-bold px-4']) }}>
    {{ $slot }}
</button>