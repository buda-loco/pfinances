<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4 overflow-visible">
        @csrf
        @method('patch')

        <div class="row g-4">
            <!-- Name -->
            <div class="col-12">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">
                    {{ __('Name') }}
                </label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus
                    autocomplete="name" class="form-control @error('name') is-invalid @enderror">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email -->
            <div class="col-12">
                <label class="form-label small fw-bold text-muted text-uppercase tracking-wider">
                    {{ __('Email') }}
                </label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                    autocomplete="username" class="form-control @error('email') is-invalid @enderror">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                    <div
                        class="alert alert-warning border-0 shadow-none bg-warning bg-opacity-10 mt-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation fs-5"></i>
                        <div>
                            <p class="mb-0 small fw-bold">Your email address is unverified.</p>
                            <button form="send-verification"
                                class="btn btn-link link-warning p-0 small fw-bold text-decoration-underline">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </div>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success border-0 shadow-none bg-success bg-opacity-10 mt-2 small fw-bold">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Submit -->
        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary px-4 fw-bold">
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-success small fw-bold">
                    <i class="fa-solid fa-check-circle me-1"></i> {{ __('Saved successfully.') }}
                </div>
            @endif
        </div>
    </form>
</section>