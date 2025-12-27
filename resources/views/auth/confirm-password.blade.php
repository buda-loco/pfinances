<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold text-dark mb-1">{{ __('Confirm Access') }}</h4>
        <p class="text-muted small">{{ __('Please confirm your password to continue') }}</p>
    </div>

    <div class="alert alert-warning border-0 shadow-none bg-warning bg-opacity-10 small mb-4">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="mb-4">
            <label for="password"
                class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('Password') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fa-solid fa-lock text-muted"></i>
                </span>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="form-control border-start-0 @error('password') is-invalid @enderror" placeholder="••••••••">
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3">
                {{ __('Confirm Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>