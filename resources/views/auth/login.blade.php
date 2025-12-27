<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold text-dark mb-1">{{ __('Welcome Back') }}</h4>
        <p class="text-muted small">{{ __('Please enter your details to sign in') }}</p>
    </div>

    <!-- Session Status -->
    @if(session('status'))
        <div class="alert alert-success border-0 shadow-none bg-success bg-opacity-10 small fw-bold mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email"
                class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('Email Address') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fa-regular fa-envelope text-muted"></i>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    autocomplete="username" class="form-control border-start-0 @error('email') is-invalid @enderror"
                    placeholder="name@example.com">
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label for="password"
                    class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('Password') }}</label>
                @if (Route::has('password.request'))
                    <a class="text-primary small text-decoration-none fw-bold" href="{{ route('password.request') }}">
                        {{ __('Forgot?') }}
                    </a>
                @endif
            </div>
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

        <!-- Remember Me -->
        <div class="mb-4">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                <label class="form-check-label small text-muted fw-medium" for="remember_me">
                    {{ __('Keep me signed in') }}
                </label>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3">
                {{ __('Log in') }}
            </button>
        </div>

        @if (Route::has('register'))
            <div class="mt-4 text-center">
                <p class="text-muted small mb-0">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}"
                        class="text-primary fw-bold text-decoration-none">{{ __('Sign up') }}</a>
                </p>
            </div>
        @endif
    </form>
</x-guest-layout>