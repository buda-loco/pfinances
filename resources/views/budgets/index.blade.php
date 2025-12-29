@extends('layouts.bootstrap')

@section('title', 'Budgets')

@section('content')
    <div class="d-flex flex-column">
        <!-- Header -->
        <div class="card border-0 shadow-sm p-4 section-gap">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0 outfit">Budgetary Oversight</h5>
                    <p class="text-muted extra-small mb-0">Define fiscal boundaries and monitor category-specific
                        consumption.</p>
                </div>
                <a href="{{ route('budgets.create') }}" class="btn btn-primary d-flex align-items-center gap-2 px-4 fw-bold">
                    <i class="fa-solid fa-plus"></i> Define New Budget
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 section-gap">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-4 hover-lift h-100">
                    <p class="text-muted extra-small fw-bold text-uppercase mb-2 tracking-wider">Total Budgets</p>
                    <h3 class="fw-bold mb-0 text-dark outfit">{{ $totalBudgets }}</h3>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-4 hover-lift h-100">
                    <p class="text-muted extra-small fw-bold text-uppercase mb-2 tracking-wider">On Track</p>
                    <h3 class="fw-bold mb-0 text-success outfit">{{ $onTrack }}</h3>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-4 hover-lift h-100">
                    <p class="text-muted extra-small fw-bold text-uppercase mb-2 tracking-wider">Over Budget</p>
                    <h3 class="fw-bold mb-0 text-danger outfit">{{ $overBudget }}</h3>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm p-4 hover-lift h-100">
                    <p class="text-muted extra-small fw-bold text-uppercase mb-2 tracking-wider">Allocation Status</p>
                    <div class="d-flex align-items-baseline gap-1">
                        <h3 class="fw-bold text-dark outfit mb-0">${{ number_format($totalSpent, 0) }}</h3>
                        <span class="text-muted extra-small fw-bold">/ ${{ number_format($totalBudgeted, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Cards -->
        @if($budgets->count() > 0)
            <div class="row g-4 section-gap">
                @foreach($budgets as $budget)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm h-100 p-4 hover-lift">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-4 d-flex align-items-center justify-content-center"
                                        style="width: 48px; height: 48px; background-color: {{ $budget->category->color ?? '#6366f1' }}15; border: 1px solid {{ $budget->category->color ?? '#6366f1' }}30;">
                                        <span class="fs-5" style="color: {{ $budget->category->color ?? '#6366f1' }};">
                                            @if($budget->category->icon && str_contains($budget->category->icon, 'fa-'))
                                                <i class="{{ $budget->category->icon }}"></i>
                                            @elseif($budget->category->icon && strtolower($budget->category->icon) === 'tag')
                                                <span class="fw-bold">{{ mb_substr(preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $budget->category->name), 0, 1) }}</span>
                                            @else
                                                {{ $budget->category->icon ?? '📊' }}
                                            @endif
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 text-truncate outfit" style="max-width: 150px;">
                                            {{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $budget->category->name) }}
                                        </h6>
                                        <p class="text-muted extra-small fw-bold text-uppercase mb-0 tracking-wider">
                                            {{ $budget->period_type }} Allocation
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('budgets.edit', $budget) }}"
                                    class="btn btn-light btn-sm border d-inline-flex align-items-center gap-1">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                            </div>

                            <!-- Amount -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <h4 class="fw-bold text-dark mb-0 outfit">${{ number_format($budget->spent, 2) }}</h4>
                                    <span class="text-muted extra-small fw-bold text-uppercase tracking-wider">Target:
                                        ${{ number_format($budget->amount, 2) }}</span>
                                </div>
                            </div>

                            <!-- Progress Bar -->
                            <div class="mb-4">
                                @php
                                    $percentage = min(100, $budget->percentage);
                                    $variant = $budget->percentage >= 100 ? 'bg-danger' : ($budget->percentage >= 80 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="progress mb-2" style="height: 6px; background-color: rgba(0,0,0,0.05);">
                                    <div class="progress-bar {{ $variant }} rounded-pill" role="progressbar"
                                        style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <span
                                        class="extra-small fw-bold {{ $budget->percentage >= 100 ? 'text-danger' : 'text-muted' }} text-uppercase tracking-wider">
                                        {{ number_format($budget->percentage, 0) }}% UTILIZED
                                    </span>
                                    <span
                                        class="extra-small fw-bold {{ $budget->remaining >= 0 ? 'text-success' : 'text-danger' }} text-uppercase tracking-wider">
                                        <i class="fa-solid {{ $budget->remaining >= 0 ? 'fa-circle-check' : 'fa-circle-exclamation' }} me-1"></i>
                                        {{ $budget->remaining >= 0 ? '$' . number_format($budget->remaining, 2) . ' LEFT' : '$' . number_format(abs($budget->remaining), 2) . ' OVER' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Period -->
                            <div class="mt-auto d-flex align-items-center gap-2 pt-3 border-top">
                                <i class="fa-regular fa-calendar text-muted extra-small"></i>
                                <span class="extra-small text-muted fw-bold text-uppercase tracking-wider">
                                    {{ $budget->period_start->format('M d') }} &mdash; {{ $budget->period_end->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card border-0 shadow-sm py-5 px-4 text-center">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3"
                    style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-piggy-bank text-muted fs-1 opacity-25"></i>
                </div>
                <h5 class="fw-bold outfit">No benchmarks set</h5>
                <p class="text-muted small px-md-5">Create your first budget to start tracking spending limits and stay on track
                    with your financial goals.</p>
                <div class="mt-3">
                    <a href="{{ route('budgets.create') }}" class="btn btn-primary d-flex align-items-center gap-2 px-4 fw-bold">
                        <i class="fa-solid fa-plus"></i> New Budget
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Show success toast for newly created budget
        @if(session('created_budget_id'))
            window.toast('Budget for "{{ session('created_budget_name') }}" created successfully.', 'success', 5000);
        @endif
    </script>
@endpush