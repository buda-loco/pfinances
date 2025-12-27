@php
    $action = $transaction ? route('transactions.update', $transaction) : route('transactions.index'); // Controller doesn't have store, so this usually goes to a bulk upload or similar, but let's assume index for now or handle via JS
    $method = $transaction ? 'PATCH' : 'POST';
@endphp

<form id="transaction-form" action="{{ $action }}" method="POST">
    @csrf
    @if($transaction)
        @method('PATCH')
    @endif

    <div class="row g-3">
        <!-- Date -->
        <div class="col-12">
            <label class="form-label small fw-bold text-muted">Transaction Date <span
                    class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                <input type="date" name="date"
                    value="{{ old('date', $transaction?->transaction_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                    required class="form-control @error('date') is-invalid @enderror">
            </div>
            @error('date')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Description -->
        <div class="col-12">
            <label class="form-label small fw-bold text-muted">Description <span class="text-danger">*</span></label>
            <input type="text" name="description" value="{{ old('description', $transaction?->description) }}" required
                class="form-control @error('description') is-invalid @enderror"
                placeholder="Enter transaction description">
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Amount -->
        <div class="col-12">
            <label class="form-label small fw-bold text-muted">Amount <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" name="amount" value="{{ old('amount', $transaction?->amount) }}"
                    required class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
            </div>
            @error('amount')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Account -->
        <div class="col-12">
            <label class="form-label small fw-bold text-muted">Account <span class="text-danger">*</span></label>
            <select name="account_id" required class="form-select @error('account_id') is-invalid @enderror">
                <option value="">Select account...</option>
                @foreach($accounts as $account)
                    <option value="{{ $account->id }}" {{ old('account_id', $transaction?->account_id) == $account->id ? 'selected' : '' }}>
                        {{ $account->name }}
                    </option>
                @endforeach
            </select>
            @error('account_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Category -->
        <div class="col-12">
            <label class="form-label small fw-bold text-muted">Category</label>
            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                <option value="">Select category...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $transaction?->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Project -->
        <div class="col-12">
            <label class="form-label small fw-bold text-muted">Project</label>
            <select name="project_id" class="form-select @error('project_id') is-invalid @enderror">
                <option value="">None</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id', $transaction?->project_id) == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
            @error('project_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- User Note -->
        <div class="col-12">
            <label class="form-label small fw-bold text-muted">Notes</label>
            <textarea name="user_description" rows="2"
                class="form-control @error('user_description') is-invalid @enderror"
                placeholder="Optional notes...">{{ old('user_description', $transaction?->user_description) }}</textarea>
            @error('user_description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('transactions.index') }}" class="btn btn-light border px-4 fw-bold">Cancel</a>
        <button type="submit" class="btn btn-primary px-4 fw-bold">
            <i class="fa-solid fa-floppy-disk me-1"></i> {{ $transaction ? 'Update' : 'Save' }}
        </button>
    </div>
</form>