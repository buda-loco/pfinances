@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$alignmentClasses = match ($align) {
    'left' => 'dropdown-menu-start',
    'top' => '',
    default => 'dropdown-menu-end',
};

$width = match ($width) {
    '48' => 'w-100',
    default => $width,
};
@endphp

<div class="dropdown" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div class="dropdown-menu {{ $alignmentClasses }} {{ $width }}"
         x-bind:class="{ 'show': open }"
         @click="open = false">
        {{ $content }}
    </div>
</div>

