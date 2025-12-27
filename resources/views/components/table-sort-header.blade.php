@props(['sortField', 'currentField' => '', 'direction' => 'asc', 'label'])

<th {{ $attributes }}>
    <a href="#"
       class="text-decoration-none text-muted d-flex align-items-center gap-1"
       aria-sort="{{ $currentField === $sortField ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none' }}">
        {{ $label }}
        @if($currentField === $sortField)
            <i class="fa-solid fa-sort-{{ $direction === 'asc' ? 'up' : 'down' }}"></i>
        @endif
    </a>
</th>
