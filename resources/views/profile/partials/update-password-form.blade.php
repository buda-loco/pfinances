<section>
    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')

        <div class="row g-4">
            <!-- Current Password -->
            <div class="col-12">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">
                    {{ __('Current Password') }}
                </label>
                <input type="password" name="current_password" autocomplete="current-password"
                    class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif">
                @if($errors->updatePassword->has('current_password'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                @endif
            </div>

            <!-- New Password -->
            <div class="col-12">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">
                    {{ __('New Password') }}
                </label>
                <input type="password" name="password" autocomplete="new-password"
                    class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif">
                @if($errors->updatePassword->has('password'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                @endif
            </div>

            <!-- Confirm Password -->
            <div class="col-12">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">
                    {{ __('Confirm Password') }}
                </label>
                <input type="password" name="password_confirmation" autocomplete="new-password"
                    class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif">
                @if($errors->updatePassword->has('password_confirmation'))
                    <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                @endif
            </div>
        </div>

        <!-- Submit -->
        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary px-4 fw-bold">
                {{ __('Update Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-success small fw-bold">
                    <i class="fa-solid fa-check-circle me-1"></i> {{ __('Updated successfully.') }}
                </div>
            @endif
        </div>
    </form>
</section>