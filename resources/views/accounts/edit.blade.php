@extends('layouts.bootstrap')

@section('title', 'Edit Account')

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Accounts', 'url' => route('accounts.index')],
        ['label' => $account->name]
    ]" />

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Edit Account: {{ $account->name }}</h5>
            <form id="delete-account-form" action="{{ route('accounts.destroy', $account) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="button"
                    @click="$dispatch('confirm', {
                        title: 'Delete Account',
                        message: 'Are you sure you want to delete this account? This action cannot be undone.',
                        confirmText: 'Delete',
                        onConfirm: () => document.getElementById('delete-account-form').submit()
                    })"
                    class="btn btn-link link-danger p-0 text-decoration-none small">
                    <i class="fa-solid fa-trash-can me-1"></i> Delete Account
                </button>
            </form>
        </div>

        <div class="card-body p-4 pt-0">
            <form id="edit-form" action="{{ route('accounts.update', $account) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="row g-4">
                    <!-- Account Name -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Account Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $account->name) }}" required
                            class="form-control @error('name') is-invalid @enderror">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Account Type -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Account Type <span
                                class="text-danger">*</span></label>
                        <select name="account_type" required
                            class="form-select @error('account_type') is-invalid @enderror">
                            @foreach(['bank' => 'Bank Account', 'credit_card' => 'Credit Card', 'savings' => 'Savings', 'investment' => 'Investment', 'travel_money' => 'Travel Money Card', 'cash' => 'Cash', 'other' => 'Other'] as $value => $label)
                                <option value="{{ $value }}" {{ old('account_type', $account->account_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('account_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ownership -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Ownership <span
                                class="text-danger">*</span></label>
                        <select name="ownership" required class="form-select @error('ownership') is-invalid @enderror">
                            @foreach(['buda', 'gupi', 'shared'] as $owner)
                                <option value="{{ $owner }}" {{ old('ownership', $account->ownership) == $owner ? 'selected' : '' }}>{{ ucfirst($owner) }}</option>
                            @endforeach
                        </select>
                        @error('ownership')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Institution -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Institution/Bank</label>
                        <input type="text" name="institution" value="{{ old('institution', $account->institution) }}"
                            class="form-control @error('institution') is-invalid @enderror">
                        @error('institution')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">Currency <span
                                class="text-danger">*</span></label>
                        <select name="currency" required class="form-select @error('currency') is-invalid @enderror">
                            @foreach(['AUD', 'USD', 'EUR', 'GBP', 'COP', 'JPY', 'KRW', 'VND', 'HKD', 'FJD', 'ARS', 'BRL', 'DOP'] as $curr)
                                <option value="{{ $curr }}" {{ old('currency', $account->currency) == $curr ? 'selected' : '' }}>
                                    {{ $curr }}</option>
                            @endforeach
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Opening Balance -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">Opening Balance <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="opening_balance"
                                value="{{ old('opening_balance', $account->opening_balance) }}" required
                                class="form-control @error('opening_balance') is-invalid @enderror">
                        </div>
                        @error('opening_balance')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Current Balance -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">Current Balance <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="current_balance"
                                value="{{ old('current_balance', $account->current_balance) }}" required
                                class="form-control @error('current_balance') is-invalid @enderror">
                        </div>
                        @error('current_balance')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-12">
                        <div class="form-check form-switch p-0 d-flex flex-column gap-2">
                            <label class="form-label small fw-bold text-muted mb-0">Account Status</label>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label small fw-bold" for="is_active">Account is active</label>
                            </div>
                        </div>
                    </div>

                    <!-- Account Number -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Account Number (Optional)</label>
                        <input type="text" name="account_number"
                            value="{{ old('account_number', $account->account_number) }}"
                            class="form-control @error('account_number') is-invalid @enderror">
                        @error('account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Notes</label>
                        <textarea name="notes" rows="3"
                            class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $account->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </form>
        </div>

        <div class="card-footer bg-white border-0 p-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('accounts.index') }}" class="btn btn-light border px-4 fw-bold">Cancel</a>
                <button type="submit" form="edit-form" class="btn btn-primary px-4 fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
@endsection