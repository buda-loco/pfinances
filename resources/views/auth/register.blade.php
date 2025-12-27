<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold text-dark mb-1">{{ __('Create Account') }}</h4>
        <p class="text-muted small">{{ __('Join PFinances today to manage your assets') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name"
                class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('Full Name') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fa-regular fa-user text-muted"></i>
                </span>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    autocomplete="name" class="form-control border-start-0 @error('name') is-invalid @enderror"
                    placeholder="John Doe">
            </div>
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email"
                class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('Email Address') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fa-regular fa-envelope text-muted"></i>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
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
                class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('Password') }}</label>
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
                class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('Confirm Password') }}</label>
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
                {{ __('Register Account') }}
            </button>
        </div>

        <div class="mt-4 text-center">
            <p class="text-muted small mb-0">
                {{ __('Already have an account?') }}
                <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">{{ __('Sign in') }}</a>
            </p>
        </div>
    </form>
</x-guest-layout>