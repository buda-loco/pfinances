@extends('layouts.bootstrap')

@section('title', $project->name)

@section('content')
    <div class="d-flex flex-column gap-4">
        <!-- Header -->
        <div>
            <a href="{{ route('projects.index') }}" class="btn btn-link link-secondary p-0 mb-3 text-decoration-none small">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Projects
            </a>
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" 
                        style="width: 56px; height: 56px; background-color: {{ $project->color ?? '#6366f1' }}20;">
                        <span style="color: {{ $project->color ?? '#6366f1' }}; font-size: 1.5rem;">📁</span>
                    </div>
                    <div>
                        <h3 class="fw-bold text-dark mb-0">{{ $project->name }}</h3>
                        <p class="text-muted small mb-0">{{ $project->code }}</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    @php
                        $statusVariant = match ($project->status) {
                            'active' => 'success',
                            'completed' => 'info',
                            'planning' => 'warning',
                            'archived' => 'secondary',
                            default => 'secondary'
                        };
                    @endphp
                    <x-pill :variant="$statusVariant">{{ ucfirst($project->status) }}</x-pill>
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-light border px-4 fw-bold">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Project
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3">
                    <p class="text-muted small fw-bold text-uppercase mb-1">Budget</p>
                    <h4 class="fw-bold mb-0">${{ number_format($project->budget ?? 0, 2) }}</h4>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3">
                    <p class="text-muted small fw-bold text-uppercase mb-1">Total Spent</p>
                    <h4 class="fw-bold mb-0 text-danger">${{ number_format(abs($totalSpent), 2) }}</h4>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3">
                    <p class="text-muted small fw-bold text-uppercase mb-1">Total Income</p>
                    <h4 class="fw-bold mb-0 text-success">${{ number_format($totalIncome, 2) }}</h4>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-3">
                    <p class="text-muted small fw-bold text-uppercase mb-1">Transactions</p>
                    <h4 class="fw-bold mb-0 text-dark">{{ $transactionCount }}</h4>
                </div>
            </div>
        </div>

        <!-- Progress and Details -->
        <div class="row g-4">
            @if($project->budget)
                @php
                    $spent = abs($totalSpent);
                    $percentage = min(100, ($spent / $project->budget) * 100);
                    $remaining = $project->budget - $spent;
                    $barVariant = $percentage >= 100 ? 'bg-danger' : ($percentage >= 80 ? 'bg-warning' : 'bg-primary');
                @endphp
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Budget Progress</h5>
                            <span class="small fw-bold text-muted">{{ number_format($percentage, 1) }}% used</span>
                        </div>
                        <div class="progress mb-3" style="height: 12px;">
                            <div class="progress-bar {{ $barVariant }}" role="progressbar" style="width: {{ $percentage }}%" 
                                aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid {{ $remaining >= 0 ? 'fa-circle-check text-success' : 'fa-circle-exclamation text-danger' }} small"></i>
                            <span class="small fw-bold {{ $remaining >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $remaining >= 0 ? '$' . number_format($remaining, 2) . ' remaining' : '$' . number_format(abs($remaining), 2) . ' over budget' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            @if($project->description || $project->start_date || $project->end_date)
                <div class="col-12 {{ $project->budget ? 'col-lg-4' : '' }}">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <h5 class="fw-bold mb-3">Project Details</h5>
                        @if($project->description)
                            <p class="text-muted small mb-3">{{ $project->description }}</p>
                        @endif
                        @if($project->start_date || $project->end_date)
                            <div class="d-flex flex-column gap-2">
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="fa-regular fa-calendar text-muted"></i>
                                    <span class="fw-medium text-dark">Period:</span>
                                    <span class="text-muted">
                                        {{ $project->start_date?->format('M d, Y') ?? 'Start N/A' }} 
                                        &mdash; 
                                        {{ $project->end_date?->format('M d, Y') ?? 'Ongoing' }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Transactions Table -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 p-4">
                <h5 class="fw-bold mb-0">Project Transactions</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Description</th>
                            <th>Account</th>
                            <th>Category</th>
                            <th class="text-end pe-4">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="ps-4 fw-medium text-muted small">
                                    {{ $transaction->transaction_date->format('M d, Y') }}
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ Str::limit($transaction->description, 50) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-normal">{{ $transaction->account->name }}</span>
                                </td>
                                <td>
                                    @if($transaction->category)
                                        <x-pill variant="{{ $transaction->category->category_type === 'expense' ? 'danger' : ($transaction->category->category_type === 'income' ? 'success' : 'info') }}">
                                            {{ $transaction->category->name }}
                                        </x-pill>
                                    @else
                                        <x-pill variant="warning">Uncategorized</x-pill>
                                    @endif
                                </td>
                                <td class="text-end pe-4 fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-dark' }}">
                                    {{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-3"><i class="fa-solid fa-receipt fs-1 opacity-25"></i></div>
                                    <h5 class="fw-bold">No transactions yet</h5>
                                    <p class="text-muted small">Assign transactions to this project from the transactions page.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="card-footer bg-white border-0 px-4 py-3">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection