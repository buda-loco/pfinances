@extends('layouts.bootstrap')

@section('title', 'Add Account')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 px-4 py-3">
            <h5 class="fw-bold mb-0">Add New Account</h5>
        </div>

        <div class="card-body p-4 pt-0">
            <form id="create-form" action="{{ route('accounts.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <!-- Account Name -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Account Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="form-control @error('name') is-invalid @enderror" placeholder="e.g., ING Daily Account">
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
                            <option value="">Select type...</option>
                            <option value="bank" {{ old('account_type') == 'bank' ? 'selected' : '' }}>Bank Account</option>
                            <option value="credit_card" {{ old('account_type') == 'credit_card' ? 'selected' : '' }}>Credit
                                Card</option>
                            <option value="savings" {{ old('account_type') == 'savings' ? 'selected' : '' }}>Savings</option>
                            <option value="investment" {{ old('account_type') == 'investment' ? 'selected' : '' }}>Investment
                            </option>
                            <option value="travel_money" {{ old('account_type') == 'travel_money' ? 'selected' : '' }}>Travel
                                Money Card</option>
                            <option value="cash" {{ old('account_type') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="other" {{ old('account_type') == 'other' ? 'selected' : '' }}>Other</option>
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
                            <option value="">Select ownership...</option>
                            <option value="buda" {{ old('ownership') == 'buda' ? 'selected' : '' }}>Buda</option>
                            <option value="gupi" {{ old('ownership') == 'gupi' ? 'selected' : '' }}>Gupi</option>
                            <option value="shared" {{ old('ownership') == 'shared' ? 'selected' : '' }}>Shared</option>
                        </select>
                        @error('ownership')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Institution -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Institution/Bank</label>
                        <input type="text" name="institution" value="{{ old('institution') }}"
                            class="form-control @error('institution') is-invalid @enderror" placeholder="e.g., ING Bank">
                        @error('institution')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Currency -->
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">Currency <span
                                class="text-danger">*</span></label>
                        <select name="currency" required class="form-select @error('currency') is-invalid @enderror">
                            <option value="">Select...</option>
                            <option value="AUD" {{ old('currency') == 'AUD' ? 'selected' : '' }}>AUD</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                            <option value="COP" {{ old('currency') == 'COP' ? 'selected' : '' }}>COP</option>
                            <option value="JPY" {{ old('currency') == 'JPY' ? 'selected' : '' }}>JPY</option>
                            <option value="KRW" {{ old('currency') == 'KRW' ? 'selected' : '' }}>KRW</option>
                            <option value="VND" {{ old('currency') == 'VND' ? 'selected' : '' }}>VND</option>
                            <option value="HKD" {{ old('currency') == 'HKD' ? 'selected' : '' }}>HKD</option>
                            <option value="FJD" {{ old('currency') == 'FJD' ? 'selected' : '' }}>FJD</option>
                            <option value="ARS" {{ old('currency') == 'ARS' ? 'selected' : '' }}>ARS</option>
                            <option value="BRL" {{ old('currency') == 'BRL' ? 'selected' : '' }}>BRL</option>
                            <option value="DOP" {{ old('currency') == 'DOP' ? 'selected' : '' }}>DOP</option>
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
                            <input type="number" step="0.01" name="opening_balance" value="{{ old('opening_balance', 0) }}"
                                required class="form-control @error('opening_balance') is-invalid @enderror">
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
                            <input type="number" step="0.01" name="current_balance" value="{{ old('current_balance', 0) }}"
                                required class="form-control @error('current_balance') is-invalid @enderror">
                        </div>
                        @error('current_balance')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Account Number -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Account Number (Optional)</label>
                        <input type="text" name="account_number" value="{{ old('account_number') }}"
                            class="form-control @error('account_number') is-invalid @enderror"
                            placeholder="Last 4 digits or masked number">
                        @error('account_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Notes</label>
                        <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Any additional notes...">{{ old('notes') }}</textarea>
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
                <button type="submit" form="create-form" class="btn btn-primary px-4 fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Create Account
                </button>
            </div>
        </div>
    </div>
@endsection