@extends('layouts.bootstrap')

@section('title', 'Create Budget')

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Budgets', 'url' => route('budgets.index')],
        ['label' => 'Create New Budget']
    ]" />

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 px-4 py-3">
            <h5 class="fw-bold mb-0">Create New Budget</h5>
        </div>

        <div class="card-body p-4 pt-0">
            <form id="create-form" action="{{ route('budgets.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <!-- Category -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Category <span
                                class="text-danger">*</span></label>
                        <select name="category_id" required class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">Select a category...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                            <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required
                                class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
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
                                <option value="{{ $curr }}" {{ old('currency', 'AUD') == $curr ? 'selected' : '' }}>{{ $curr }}
                                </option>
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
                                <option value="{{ $val }}" {{ old('period_type', 'monthly') == $val ? 'selected' : '' }}>
                                    {{ $label }}</option>
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
                                value="{{ old('period_start', now()->startOfMonth()->format('Y-m-d')) }}" required
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
                                value="{{ old('period_end', now()->endOfMonth()->format('Y-m-d')) }}" required
                                class="form-control @error('period_end') is-invalid @enderror">
                        </div>
                        @error('period_end')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Notes</label>
                        <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Optional notes...">{{ old('notes') }}</textarea>
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
                <button type="submit" form="create-form" class="btn btn-primary px-4 fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Create Budget
                </button>
            </div>
        </div>
    </div>
@endsection