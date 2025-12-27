@extends('layouts.bootstrap')

@section('title', 'Profile Settings')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="d-flex flex-column gap-4">
                <!-- Update Profile Information -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 px-4 py-3">
                        <h5 class="fw-bold mb-0">Profile Information</h5>
                        <p class="text-muted small mb-0">Update your account's profile information and email address.</p>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Update Password -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 px-4 py-3">
                        <h5 class="fw-bold mb-0">Update Password</h5>
                        <p class="text-muted small mb-0">Ensure your account is using a long, random password to stay
                            secure.</p>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="card border-0 shadow-sm border-danger-subtle">
                    <div class="card-header bg-white border-0 px-4 py-3">
                        <h5 class="fw-bold text-danger mb-0">Delete Account</h5>
                        <p class="text-muted small mb-0">Permanently delete your account and all associated data.</p>
                    </div>
                    <div class="card-body p-4 pt-0">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection