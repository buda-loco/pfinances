<x-guest-layout>
    <div class="text-center mb-4">
        <h4 class="fw-bold text-dark mb-1">{{ __('Verify Email') }}</h4>
        <p class="text-muted small">{{ __('Check your inbox for a verification link') }}</p>
    </div>

    <div class="alert alert-info border-0 shadow-none bg-info bg-opacity-10 small mb-4">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success border-0 shadow-none bg-success bg-opacity-10 small fw-bold mb-4">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="d-flex flex-column gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <div class="d-grid">
                <button type="submit" class="btn btn-primary py-2 fw-bold rounded-3">
                    {{ __('Resend Verification Email') }}
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <button type="submit" class="btn btn-link link-secondary small fw-bold text-decoration-none">
                <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>