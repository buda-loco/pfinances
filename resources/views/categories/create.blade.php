@extends('layouts.bootstrap')

@section('title', 'Create Category')

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Categories', 'url' => route('categories.index')],
        ['label' => 'Create New Category']
    ]" />

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 px-4 py-3">
            <h5 class="fw-bold mb-0">Create New Category</h5>
        </div>

        <div class="card-body p-4 pt-0">
            <form id="create-form" action="{{ route('categories.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <!-- Name -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Category Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Groceries">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Code -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Category Code <span
                                class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}" required
                            class="form-control @error('code') is-invalid @enderror" placeholder="GROC">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Type <span class="text-danger">*</span></label>
                        <select name="category_type" required
                            class="form-select @error('category_type') is-invalid @enderror">
                            <option value="expense" {{ old('category_type') == 'expense' ? 'selected' : '' }}>Expense</option>
                            <option value="income" {{ old('category_type') == 'income' ? 'selected' : '' }}>Income</option>
                            <option value="transfer" {{ old('category_type') == 'transfer' ? 'selected' : '' }}>Transfer
                            </option>
                        </select>
                        @error('category_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Group -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Category Group</label>
                        <select name="group_id" class="form-select @error('group_id') is-invalid @enderror">
                            <option value="">Select Group</option>
                            @foreach($categoryGroups as $group)
                                <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('group_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Parent Category -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Parent Category</label>
                        <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                            <option value="">None (Top Level)</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Icon & Color -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Icon Class (FontAwesome)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-icons"></i></span>
                            <input type="text" name="icon" value="{{ old('icon') }}"
                                class="form-control @error('icon') is-invalid @enderror"
                                placeholder="fa-solid fa-cart-shopping">
                        </div>
                        @error('icon')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Indicator Color</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="color" name="color" value="{{ old('color', '#3b82f6') }}"
                                class="form-control form-control-color border-0 p-0 shadow-none"
                                style="width: 48px; height: 38px;">
                            <span class="small text-muted">Choose a representational color.</span>
                        </div>
                        @error('color')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Budget -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Monthly Budget</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="monthly_budget" value="{{ old('monthly_budget') }}"
                                class="form-control @error('monthly_budget') is-invalid @enderror" placeholder="0.00">
                        </div>
                        @error('monthly_budget')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-12 col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold small text-muted" for="is_active">Category is
                                active</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-footer bg-white border-0 p-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('categories.index') }}" class="btn btn-light border px-4 fw-bold">Cancel</a>
                <button type="submit" form="create-form" class="btn btn-primary px-4 fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Create Category
                </button>
            </div>
        </div>
    </div>
@endsection