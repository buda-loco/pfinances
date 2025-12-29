@extends('layouts.bootstrap')

@section('title', 'Create Project')

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Projects', 'url' => route('projects.index')],
        ['label' => 'Create New Project']
    ]" />

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 px-4 py-3">
            <h5 class="fw-bold mb-0">Create New Project</h5>
        </div>

        <div class="card-body p-4 pt-0">
            <form id="create-form" action="{{ route('projects.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <!-- Project Name -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Project Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Bali Holiday 2024">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Code -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Project Code <span
                                class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code') }}" required
                            class="form-control @error('code') is-invalid @enderror uppercase" placeholder="e.g., BALI24">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Description</label>
                        <textarea name="description" rows="3"
                            class="form-control @error('description') is-invalid @enderror"
                            placeholder="Brief description of the project...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Dates -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Start Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input type="date" name="start_date" value="{{ old('start_date') }}"
                                class="form-control @error('start_date') is-invalid @enderror">
                        </div>
                        @error('start_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">End Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input type="date" name="end_date" value="{{ old('end_date') }}"
                                class="form-control @error('end_date') is-invalid @enderror">
                        </div>
                        @error('end_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Budget & Status -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Budget</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="budget" value="{{ old('budget') }}" step="0.01" min="0"
                                class="form-control @error('budget') is-invalid @enderror" placeholder="0.00">
                        </div>
                        @error('budget')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Status <span class="text-danger">*</span></label>
                        <select name="status" required class="form-select @error('status') is-invalid @enderror">
                            <option value="planning" {{ old('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Color -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Project Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="color" value="{{ old('color', '#6366f1') }}"
                                class="form-control form-control-color border-0 p-0 shadow-none"
                                style="width: 48px; height: 38px;">
                            <span class="small text-muted">Select a color to represent this project in charts.</span>
                        </div>
                        @error('color')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </form>
        </div>

        <div class="card-footer bg-white border-0 p-4">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('projects.index') }}" class="btn btn-light border px-4 fw-bold">Cancel</a>
                <button type="submit" form="create-form" class="btn btn-primary px-4 fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Create Project
                </button>
            </div>
        </div>
    </div>
@endsection