<div x-data="toastManager()"
     @show-toast.window="add($event.detail.message, $event.detail.type, $event.detail.duration)"
     class="toast-container position-fixed top-0 end-0 p-3"
     style="z-index: 9999;"
     role="region"
     aria-label="Notifications">
    <template x-for="toast in toasts" :key="toast.id">
        <div class="toast show align-items-center border-0 shadow-lg"
             :class="`text-bg-${toast.type}`"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             x-show="true"
             x-transition:enter="fade"
             x-transition:leave="fade">
            <div class="d-flex">
                <div class="toast-body fw-bold d-flex align-items-center gap-2">
                    <i class="fa-solid" :class="{
                        'fa-check-circle': toast.type === 'success',
                        'fa-exclamation-circle': toast.type === 'danger',
                        'fa-info-circle': toast.type === 'info',
                        'fa-exclamation-triangle': toast.type === 'warning'
                    }"></i>
                    <span x-text="toast.message"></span>
                </div>
                <button type="button"
                        class="btn-close me-2 m-auto"
                        :class="{ 'btn-close-white': ['success', 'danger', 'primary', 'warning'].includes(toast.type) }"
                        @click="remove(toast.id)"
                        aria-label="Close notification"></button>
            </div>
        </div>
    </template>
</div>
