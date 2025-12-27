<div id="search-detail-modal" class="modal fade" tabindex="-1" aria-hidden="true" x-data="searchDetailModal()">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div x-show="details"
                         :class="`bg-${details?.color || 'primary'} bg-opacity-10 text-${details?.color || 'primary'} rounded-3 p-3 d-flex align-items-center justify-content-center`"
                         style="width: 48px; height: 48px;">
                        <i :class="`fa-solid ${details?.icon || 'fa-file'} fs-5`"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="modal-title fw-bold mb-1" x-text="details?.title || 'Loading...'"></h5>
                        <p class="text-muted small mb-0" x-text="details?.type_label || ''"></p>
                    </div>
                    <button x-show="!loading && details?.editable && !editing"
                            @click="enterEditMode()"
                            type="button"
                            class="btn btn-sm btn-outline-primary">
                        <i class="fa-solid fa-pen me-1"></i> Edit
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- Loading State -->
                <div x-show="loading" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted">Loading details...</p>
                </div>

                <!-- Content -->
                <div x-show="!loading && details" x-cloak>
                    <!-- View Mode -->
                    <div x-show="!editing">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <template x-for="(value, key) in details?.info" :key="key">
                                        <tr>
                                            <td class="text-muted fw-semibold text-uppercase extra-small ps-0" style="width: 35%;" x-text="key"></td>
                                            <td class="fw-medium ps-0" x-html="value"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <div x-show="editing" x-cloak>
                        <form @submit.prevent="saveChanges()">
                            <div class="row g-3">
                                <template x-for="field in details?.edit_fields" :key="field.name">
                                    <div :class="field.full_width ? 'col-12' : 'col-md-6'">
                                        <label :for="'edit-' + field.name" class="form-label small fw-bold text-muted text-uppercase" x-text="field.label"></label>

                                        <!-- Text Input -->
                                        <template x-if="field.type === 'text'">
                                            <input type="text"
                                                   :id="'edit-' + field.name"
                                                   x-model="editData[field.name]"
                                                   class="form-control"
                                                   :required="field.required">
                                        </template>

                                        <!-- Number Input -->
                                        <template x-if="field.type === 'number'">
                                            <input type="number"
                                                   :id="'edit-' + field.name"
                                                   x-model="editData[field.name]"
                                                   step="0.01"
                                                   class="form-control"
                                                   :required="field.required">
                                        </template>

                                        <!-- Date Input -->
                                        <template x-if="field.type === 'date'">
                                            <input type="date"
                                                   :id="'edit-' + field.name"
                                                   x-model="editData[field.name]"
                                                   class="form-control"
                                                   :required="field.required">
                                        </template>

                                        <!-- Select Input -->
                                        <template x-if="field.type === 'select'">
                                            <select :id="'edit-' + field.name"
                                                    x-model="editData[field.name]"
                                                    class="form-select"
                                                    :required="field.required">
                                                <option value="">Select...</option>
                                                <template x-for="option in field.options" :key="option.value">
                                                    <option :value="option.value" x-text="option.label"></option>
                                                </template>
                                            </select>
                                        </template>

                                        <!-- Textarea Input -->
                                        <template x-if="field.type === 'textarea'">
                                            <textarea :id="'edit-' + field.name"
                                                      x-model="editData[field.name]"
                                                      class="form-control"
                                                      rows="3"
                                                      :required="field.required"></textarea>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </form>
                    </div>

                    <!-- Related Items (if any) -->
                    <div x-show="details?.related && details.related.length > 0" class="mt-4">
                        <h6 class="fw-bold text-muted extra-small text-uppercase mb-3">
                            <span x-text="details?.related_label || 'Related Items'"></span>
                        </h6>
                        <div class="list-group">
                            <template x-for="item in details?.related" :key="item.id">
                                <div class="list-group-item list-group-item-action border rounded-3 mb-2">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark" x-text="item.title"></div>
                                            <div class="small text-muted" x-text="item.subtitle"></div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold" x-text="item.amount"
                                                 :class="item.amount?.includes('-') ? 'text-danger' : 'text-success'"></div>
                                            <div class="extra-small text-muted" x-text="item.date"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 bg-light">
                <!-- View Mode Footer -->
                <template x-if="!editing">
                    <div class="d-flex gap-2 w-100 justify-content-between">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button"
                                x-show="details?.url"
                                @click="window.location.href = details.url"
                                class="btn btn-primary">
                            <i class="fa-solid fa-arrow-right me-2"></i>View Full Details
                        </button>
                    </div>
                </template>

                <!-- Edit Mode Footer -->
                <template x-if="editing">
                    <div class="d-flex gap-2 w-100 justify-content-between">
                        <button type="button" @click="cancelEdit()" class="btn btn-light">
                            <i class="fa-solid fa-xmark me-1"></i> Cancel
                        </button>
                        <button type="button" @click="saveChanges()" :disabled="saving" class="btn btn-primary">
                            <span x-show="!saving"><i class="fa-solid fa-check me-1"></i> Save Changes</span>
                            <span x-show="saving"><i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function searchDetailModal() {
        return {
            loading: false,
            editing: false,
            saving: false,
            details: null,
            editData: {},
            currentType: null,
            currentId: null,

            async show(type, id) {
                this.loading = true;
                this.details = null;
                this.editing = false;
                this.currentType = type;
                this.currentId = id;

                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('search-detail-modal'));
                modal.show();

                try {
                    const response = await fetch(`/search/details?type=${type}&id=${id}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    this.details = data;
                } catch (error) {
                    console.error('Error loading details:', error);
                    this.details = {
                        title: 'Error',
                        type_label: 'Failed to load details',
                        icon: 'fa-exclamation-triangle',
                        color: 'danger',
                        info: { Error: 'Failed to load details. Please try again.' }
                    };
                } finally {
                    this.loading = false;
                }
            },

            enterEditMode() {
                this.editing = true;
                // Initialize edit data with current values
                this.editData = {};
                if (this.details?.edit_fields) {
                    this.details.edit_fields.forEach(field => {
                        this.editData[field.name] = field.value;
                    });
                }
            },

            cancelEdit() {
                this.editing = false;
                this.editData = {};
            },

            async saveChanges() {
                this.saving = true;

                try {
                    const response = await fetch(`/search/update`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            type: this.currentType,
                            id: this.currentId,
                            data: this.editData
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Refresh the details
                        this.editing = false;
                        await this.show(this.currentType, this.currentId);

                        // Show success message
                        this.showToast('Success', 'Changes saved successfully!', 'success');
                    } else {
                        this.showToast('Error', result.message || 'Failed to save changes', 'danger');
                    }
                } catch (error) {
                    console.error('Error saving changes:', error);
                    this.showToast('Error', 'Failed to save changes. Please try again.', 'danger');
                } finally {
                    this.saving = false;
                }
            },

            showToast(title, message, type) {
                // Simple toast notification (you can replace with your toast system)
                alert(`${title}: ${message}`);
            }
        };
    }
</script>
