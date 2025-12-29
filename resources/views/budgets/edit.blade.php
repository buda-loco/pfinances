@extends('layouts.bootstrap')

@section('title', 'Edit Budget')

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Budgets', 'url' => route('budgets.index')],
        ['label' => $budget->category->name]
    ]" />

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Edit Budget: {{ $budget->category->name }}</h5>
            <form id="delete-budget-form" action="{{ route('budgets.destroy', $budget) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="button"
                    @click="$dispatch('confirm', {
                        title: 'Delete Budget',
                        message: 'Are you sure you want to delete this budget? This action cannot be undone.',
                        confirmText: 'Delete',
                        onConfirm: () => document.getElementById('delete-budget-form').submit()
                    })"
                    class="btn btn-link link-danger p-0 text-decoration-none small">
                    <i class="fa-solid fa-trash-can me-1"></i> Delete Budget
                </button>
            </form>
        </div>

        <div class="card-body p-4 pt-0">
            <!-- Progress Summary -->
            <div class="alert alert-light border-0 shadow-none p-4 mb-4 rounded-3 d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="fw-bold mb-0">Current Period Progress</h6>
                    <span class="extra-small fw-bold text-muted uppercase tracking-wider">
                        {{ $budget->period_start->format('M d') }} - {{ $budget->period_end->format('M d, Y') }}
                    </span>
                </div>

                @php
                    $percentage = $budget->amount > 0 ? min(100, ($budget->spent / $budget->amount) * 100) : 0;
                    $remaining = $budget->amount - $budget->spent;
                    $barVariant = $percentage >= 100 ? 'bg-danger' : ($percentage >= 80 ? 'bg-warning' : 'bg-success');
                @endphp

                <div class="d-flex justify-content-between align-items-baseline mb-1">
                    <h3 class="fw-bold mb-0">${{ number_format($budget->spent, 2) }}</h3>
                    <span class="small text-muted">of ${{ number_format($budget->amount, 2) }}</span>
                </div>

                <div class="progress" style="height: 10px;">
                    <div class="progress-bar {{ $barVariant }}" role="progressbar" style="width: {{ $percentage }}%"
                        aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <div class="d-flex align-items-center gap-2 mt-1">
                    <i
                        class="fa-solid {{ $remaining >= 0 ? 'fa-circle-check text-success' : 'fa-circle-exclamation text-danger' }} small"></i>
                    <span class="small fw-bold {{ $remaining >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $remaining >= 0 ? '$' . number_format($remaining, 2) . ' remaining' : '$' . number_format(abs($remaining), 2) . ' over budget' }}
                    </span>
                </div>
            </div>

            <form id="edit-form" action="{{ route('budgets.update', $budget) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Category -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Category <span
                                class="text-danger">*</span></label>
                        <select name="category_id" required class="form-select @error('category_id') is-invalid @enderror">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $budget->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Amount & Currency -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Budget Amount <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="amount" value="{{ old('amount', $budget->amount) }}" step="0.01"
                                min="0.01" required class="form-control @error('amount') is-invalid @enderror">
                        </div>
                        @error('amount')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Currency <span
                                class="text-danger">*</span></label>
                        <select name="currency" required class="form-select @error('currency') is-invalid @enderror">
                            @foreach(['AUD', 'USD', 'EUR'] as $curr)
                                <option value="{{ $curr }}" {{ old('currency', $budget->currency) == $curr ? 'selected' : '' }}>
                                    {{ $curr }}</option>
                            @endforeach
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Period Type -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Period Type <span
                                class="text-danger">*</span></label>
                        <select name="period_type" required class="form-select @error('period_type') is-invalid @enderror">
                            @foreach(['monthly' => 'Monthly', 'weekly' => 'Weekly', 'yearly' => 'Yearly', 'daily' => 'Daily'] as $val => $label)
                                <option value="{{ $val }}" {{ old('period_type', $budget->period_type) == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('period_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Dates -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Start Date <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input type="date" name="period_start"
                                value="{{ old('period_start', $budget->period_start->format('Y-m-d')) }}" required
                                class="form-control @error('period_start') is-invalid @enderror">
                        </div>
                        @error('period_start')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">End Date <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input type="date" name="period_end"
                                value="{{ old('period_end', $budget->period_end->format('Y-m-d')) }}" required
                                class="form-control @error('period_end') is-invalid @enderror">
                        </div>
                        @error('period_end')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Active Status -->
                    <div class="col-12">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $budget->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="is_active">Budget is
                                active</label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Notes</label>
                        <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Optional notes...">{{ old('notes', $budget->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </form>
        </div>

        <div class="card-footer bg-white border-0 p-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('budgets.index') }}" class="btn btn-light border px-4 fw-bold">Cancel</a>
                <button type="submit" form="edit-form" class="btn btn-primary px-4 fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
@endsection