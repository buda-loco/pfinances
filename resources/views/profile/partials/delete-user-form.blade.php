<section>
    <!-- Header -->
    <div
        class="alert alert-danger border-0 shadow-none bg-danger bg-opacity-10 d-flex align-items-start gap-3 p-4 rounded-4 mb-4">
        <i class="fa-solid fa-triangle-exclamation fs-4 mt-1"></i>
        <div>
            <h6 class="fw-bold mb-1">{{ __('Delete Account') }}</h6>
            <p class="mb-0 small opacity-75">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
            </p>
        </div>
    </div>

    <!-- Trigger Button -->
    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="btn btn-danger px-4 fw-bold rounded-3">
        <i class="fa-solid fa-trash-can me-2"></i> {{ __('Permanently Delete Account') }}
    </button>

    <!-- Modal -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" maxWidth="md">
        <form method="post" action="{{ route('profile.destroy') }}" class="py-2">
            @csrf
            @method('delete')

            <h5 class="fw-bold text-dark mb-3">
                {{ __('Confirm Account Deletion') }}
            </h5>

            <p class="text-muted small mb-4">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mb-4">
                <label for="password" class="form-label small fw-bold text-muted text-uppercase tracking-wider">
                    {{ __('Password') }}
                </label>
                <input type="password" id="password" name="password" placeholder="{{ __('Your password') }}"
                    class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif">

                @if($errors->userDeletion->has('password'))
                    <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                @endif
            </div>

            <div class="d-flex justify-content-end gap-2 pt-2">
                <button type="button" class="btn btn-light border px-4 fw-bold"
                    x-on:click="$dispatch('close-modal', 'confirm-user-deletion')">
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="btn btn-danger px-4 fw-bold">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>