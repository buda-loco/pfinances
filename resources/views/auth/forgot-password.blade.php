<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold text-dark mb-1">{{ __('Reset Password') }}</h4>
        <p class="text-muted small">{{ __('We\'ll send a reset link to your email') }}</p>
    </div>

    <div class="alert alert-info border-0 shadow-none bg-info bg-opacity-10 small mb-4">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.') }}
    </div>

    <!-- Session Status -->
    @if(session('status'))
        <div class="alert alert-success border-0 shadow-none bg-success bg-opacity-10 small fw-bold mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-4">
            <label for="email"
                class="form-label small fw-bold text-muted text-uppercase tracking-wider">{{ __('Email Address') }}</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                    <i class="fa-regular fa-envelope text-muted"></i>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="form-control border-start-0 @error('email') is-invalid @enderror"
                    placeholder="name@example.com">
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3">
                {{ __('Send Reset Link') }}
            </button>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-muted small text-decoration-none fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> {{ __('Back to Login') }}
            </a>
        </div>
    </form>
</x-guest-layout>