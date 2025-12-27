<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold text-dark mb-1">{{ __('Set New Password') }}</h4>
        <p class="text-muted small">{{ __('Enter your new password below') }}</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email"
                class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('Email Address') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fa-regular fa-envelope text-muted"></i>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required
                    autofocus autocomplete="username"
                    class="form-control border-start-0 @error('email') is-invalid @enderror"
                    placeholder="name@example.com">
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password"
                class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('New Password') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fa-solid fa-lock text-muted"></i>
                </span>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="form-control border-start-0 @error('password') is-invalid @enderror" placeholder="••••••••">
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label for="password_confirmation"
                class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('Confirm New Password') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fa-solid fa-shield text-muted"></i>
                </span>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    autocomplete="new-password"
                    class="form-control border-start-0 @error('password_confirmation') is-invalid @enderror"
                    placeholder="••••••••">
            </div>
            @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3">
                {{ __('Update Password') }}
            </button>
        </div>
    </form>
</x-guest-layout>