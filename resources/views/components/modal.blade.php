@props([
    'name',
    'show' => false,
    'maxWidth' => 'md',
    'title' => ''
])

<div class="modal fade"
     id="{{ $name }}"
     tabindex="-1"
     role="dialog"
     aria-modal="true"
     aria-labelledby="{{ $name }}-modal-title"
     data-bs-keyboard="true"
     data-bs-backdrop="true"
>
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down {{ $maxWidth ? 'modal-' . $maxWidth : '' }}">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background-color: var(--pf-modal-bg);">
            @if($title)
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-primary-adaptive outfit" id="{{ $name }}-modal-title">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            @endif
            <div class="modal-body p-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

@if($show)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('{{ $name }}');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl, {
            keyboard: true,
            backdrop: true
        });
        modal.show();
        
        // When modal is hidden, navigate back to remove the action param
        modalEl.addEventListener('hidden.bs.modal', function() {
            // Remove action parameter from URL and navigate
            const url = new URL(window.location.href);
            url.searchParams.delete('action');
            url.searchParams.delete('edit');
            window.location.href = url.toString();
        });
    }
});
</script>
@endif
