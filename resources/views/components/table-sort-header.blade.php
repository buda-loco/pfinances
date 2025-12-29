@props(['sortField', 'label', 'route'])

@php
    $currentSortBy = request('sort_by');
    $currentSortOrder = request('sort_order', 'asc');
    $isActive = $currentSortBy === $sortField;
    $newSortOrder = $isActive && $currentSortOrder === 'asc' ? 'desc' : 'asc';

    // Build URL preserving all existing query parameters
    $queryParams = array_merge(request()->query(), [
        'sort_by' => $sortField,
        'sort_order' => $newSortOrder
    ]);
@endphp

<th {{ $attributes }}>
    <a href="{{ route($route, $queryParams) }}"
       class="text-decoration-none text-muted extra-small fw-bold text-uppercase tracking-wider d-inline-flex align-items-center gap-1 hover-primary"
       aria-sort="{{ $isActive ? ($currentSortOrder === 'asc' ? 'ascending' : 'descending') : 'none' }}">
        {{ $label }}
        @if($isActive)
            <i class="fa-solid fa-sort-{{ $currentSortOrder === 'asc' ? 'up' : 'down' }} small"></i>
        @else
            <i class="fa-solid fa-sort small opacity-25"></i>
        @endif
    </a>
</th>
