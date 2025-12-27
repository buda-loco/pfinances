<div x-data="confirmDialog()"
     @confirm.window="open($event.detail)">
    <x-modal name="confirm-dialog" max-width="sm">
        <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold outfit text-primary-adaptive" x-text="title" id="confirm-dialog-modal-title"></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="close()"></button>
        </div>
        <div class="modal-body">
            <p x-text="message" class="text-secondary-adaptive mb-0"></p>
        </div>
        <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light" @click="close()">Cancel</button>
            <button type="button" class="btn btn-danger" @click="confirm()">
                <span x-text="confirmText"></span>
            </button>
        </div>
    </x-modal>
</div>
