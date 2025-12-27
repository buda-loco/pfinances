@extends('layouts.bootstrap')

@section('title', 'Edit Project')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Edit Project: {{ $project->name }}</h5>
            <form action="{{ route('projects.destroy', $project) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to delete this project? Transactions will be unlinked.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-link link-danger p-0 text-decoration-none small">
                    <i class="fa-solid fa-trash-can me-1"></i> Delete Project
                </button>
            </form>
        </div>

        <div class="card-body p-4 pt-0">
            <form id="edit-form" action="{{ route('projects.update', $project) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Project Name -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Project Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                            class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Bali Holiday 2024">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Code -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Project Code <span
                                class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $project->code) }}" required
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
                            placeholder="Brief description of the project...">{{ old('description', $project->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Dates -->
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Start Date</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                            <input type="date" name="start_date"
                                value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
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
                            <input type="date" name="end_date"
                                value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}"
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
                            <input type="number" name="budget" value="{{ old('budget', $project->budget) }}" step="0.01"
                                min="0" class="form-control @error('budget') is-invalid @enderror" placeholder="0.00">
                        </div>
                        @error('budget')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-bold text-muted">Status <span class="text-danger">*</span></label>
                        <select name="status" required class="form-select @error('status') is-invalid @enderror">
                            @foreach(['planning' => 'Planning', 'active' => 'Active', 'completed' => 'Completed', 'archived' => 'Archived'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $project->status) == $val ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Color -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">Project Color</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="color" name="color" value="{{ old('color', $project->color ?? '#6366f1') }}"
                                class="form-control form-control-color border-0 p-0 shadow-none"
                                style="width: 48px; height: 38px;">
                            <span class="small text-muted">Representation color in charts and lists.</span>
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
                <button type="submit" form="edit-form" class="btn btn-primary px-4 fw-bold">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
@endsection