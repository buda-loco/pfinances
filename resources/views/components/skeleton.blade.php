@props(['type' => 'text', 'width' => '100%', 'height' => null, 'circle' => false])

<div {{ $attributes->merge(['class' => 'skeleton skeleton-' . $type . ($circle ? ' rounded-circle' : ' rounded-3')]) }}
     style="width: {{ $width }}; {{ $height ? 'height: ' . $height . ';' : '' }}"
     aria-hidden="true">
</div>
