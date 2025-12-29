@extends('layouts.bootstrap')

@section('title', 'Dashboard')

@section('content')
    <!-- Financial Insights Section -->
    <div class="mb-3">
        <h4 class="fw-bold outfit text-primary-adaptive mb-1">Financial Insights</h4>
        <p class="text-secondary-adaptive small mb-0">Your earning power, savings rate, and financial health at a glance</p>
    </div>

    <div class="row g-4 mb-5">
        <!-- Income Metrics -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm hover-lift h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-4 p-3">
                            <i class="fa-solid fa-arrow-trend-up fs-4"></i>
                        </div>
                    </div>
                    <p class="text-muted extra-small fw-bold text-uppercase mb-2 tracking-wider">Income Streams</p>
                    <h4 class="fw-bold text-success mb-3 outfit">${{ number_format($financialMetrics['monthly_income'], 0) }}<span class="text-muted fs-6 fw-normal">/mo</span></h4>
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="extra-small text-muted fw-bold">Daily</span>
                            <span class="small fw-bold text-dark">${{ number_format($financialMetrics['daily_income'], 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="extra-small text-muted fw-bold">Yearly</span>
                            <span class="small fw-bold text-dark">${{ number_format($financialMetrics['yearly_income'], 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Savings Rate -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm hover-lift h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-{{ $financialMetrics['savings_rate'] > 20 ? 'success' : ($financialMetrics['savings_rate'] > 0 ? 'warning' : 'danger') }} bg-opacity-10 text-{{ $financialMetrics['savings_rate'] > 20 ? 'success' : ($financialMetrics['savings_rate'] > 0 ? 'warning' : 'danger') }} rounded-4 p-3">
                            <i class="fa-solid fa-piggy-bank fs-4"></i>
                        </div>
                    </div>
                    <p class="text-muted extra-small fw-bold text-uppercase mb-2 tracking-wider">Savings Rate</p>
                    <h4 class="fw-bold text-{{ $financialMetrics['savings_rate'] > 20 ? 'success' : ($financialMetrics['savings_rate'] > 0 ? 'warning' : 'danger') }} mb-3 outfit">{{ number_format($financialMetrics['savings_rate'], 1) }}%</h4>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-{{ $financialMetrics['savings_rate'] > 20 ? 'success' : ($financialMetrics['savings_rate'] > 0 ? 'warning' : 'danger') }}"
                             role="progressbar"
                             style="width: {{ min(100, abs($financialMetrics['savings_rate'])) }}%"></div>
                    </div>
                    <div class="extra-small text-muted fw-bold">
                        ${{ number_format(abs($financialMetrics['monthly_savings']), 2) }} {{ $financialMetrics['monthly_savings'] >= 0 ? 'saved' : 'deficit' }}/month
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Balance -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm hover-lift h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-info bg-opacity-10 text-info rounded-4 p-3">
                            <i class="fa-solid fa-scale-balanced fs-4"></i>
                        </div>
                    </div>
                    <p class="text-muted extra-small fw-bold text-uppercase mb-2 tracking-wider">Income vs Expenses</p>
                    <h4 class="fw-bold text-{{ $financialMetrics['daily_balance'] > 0 ? 'success' : 'danger' }} mb-3 outfit">${{ number_format(abs($financialMetrics['daily_balance']), 2) }}<span class="text-muted fs-6 fw-normal">/day</span></h4>
                    <div class="d-flex flex-column gap-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="extra-small text-muted fw-bold">Monthly</span>
                            <span class="small fw-bold text-{{ $financialMetrics['monthly_savings'] > 0 ? 'success' : 'danger' }}">${{ number_format(abs($financialMetrics['monthly_savings']), 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="extra-small text-muted fw-bold">Ratio</span>
                            <span class="small fw-bold text-dark">{{ number_format($financialMetrics['income_expense_ratio'], 2) }}:1</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hourly Rate -->
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm hover-lift h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                            <i class="fa-solid fa-clock fs-4"></i>
                        </div>
                    </div>
                    <p class="text-muted extra-small fw-bold text-uppercase mb-2 tracking-wider">Your Worth Per Hour</p>
                    <h4 class="fw-bold text-primary mb-3 outfit">${{ number_format($financialMetrics['hourly_rate'], 2) }}<span class="text-muted fs-6 fw-normal">/hr</span></h4>
                    <div class="extra-small text-muted fw-bold text-uppercase">
                        Based on 8h/day, 22 days/mo
                    </div>
                    <div class="mt-2 extra-small text-muted">
                        <i class="fa-solid fa-calculator me-1"></i>
                        {{ number_format($financialMetrics['monthly_income'], 0) }} ÷ 176 hours
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Financial Metrics Section -->
    <div class="mb-3 mt-5">
        <h4 class="fw-bold outfit text-primary-adaptive mb-1">Financial Health Analysis</h4>
        <p class="text-secondary-adaptive small mb-0">Comprehensive insights into your financial wellbeing</p>
    </div>

    <!-- Row 1: Financial Health Score & Emergency Fund -->
    <div class="row g-4 mb-4">
        <!-- Financial Health Score -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="extra-small fw-bold text-uppercase mb-1 text-white-50 tracking-wider">Financial Health Score</p>
                            <h6 class="text-white mb-0">Overall financial wellbeing assessment</h6>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-4 p-3">
                            <i class="fa-solid fa-heart-pulse fs-4"></i>
                        </div>
                    </div>
                    <div class="text-center py-4">
                        <h1 class="display-1 fw-bold mb-2 outfit">{{ number_format($advancedMetrics['health_score'], 0) }}</h1>
                        <p class="mb-4 text-white-50 fw-bold">out of 100</p>
                        <div class="progress mb-3" style="height: 12px; background-color: rgba(255,255,255,0.2);">
                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $advancedMetrics['health_score'] }}%"
                                aria-valuenow="{{ $advancedMetrics['health_score'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="row g-2 mt-4">
                            <div class="col-6">
                                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                                    <p class="extra-small mb-1 text-white-50 fw-bold text-uppercase">Savings Rate</p>
                                    <p class="mb-0 fw-bold">{{ number_format($financialMetrics['savings_rate'], 1) }}%</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-white bg-opacity-10 rounded-3 p-3">
                                    <p class="extra-small mb-1 text-white-50 fw-bold text-uppercase">Budget Track</p>
                                    <p class="mb-0 fw-bold">{{ number_format($advancedMetrics['budget_adherence'], 0) }}%</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Fund & Cash Runway -->
        <div class="col-12 col-lg-6">
            <div class="row g-4 h-100">
                <!-- Emergency Fund -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm h-100 bg-info bg-opacity-10">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <p class="extra-small fw-bold text-uppercase mb-1 text-muted tracking-wider">Emergency Fund</p>
                                    <h6 class="text-dark mb-0">Financial safety cushion</h6>
                                </div>
                                <div class="bg-info bg-opacity-25 text-info rounded-4 p-3">
                                    <i class="fa-solid fa-shield-halved fs-4"></i>
                                </div>
                            </div>
                            <div class="d-flex align-items-end gap-2 mb-2">
                                <h2 class="fw-bold mb-0 outfit text-info">{{ number_format($advancedMetrics['emergency_fund_months'], 1) }}</h2>
                                <span class="text-muted fw-bold mb-1">months covered</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($advancedMetrics['emergency_fund_status'] === 'excellent')
                                    <span class="badge bg-success">Excellent <i class="fa-solid fa-circle-check ms-1"></i></span>
                                @elseif($advancedMetrics['emergency_fund_status'] === 'good')
                                    <span class="badge bg-primary">Good <i class="fa-solid fa-thumbs-up ms-1"></i></span>
                                @else
                                    <span class="badge bg-warning">Needs Attention <i class="fa-solid fa-triangle-exclamation ms-1"></i></span>
                                @endif
                                <span class="extra-small text-muted">Goal: 6 months</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cash Runway -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm h-100 bg-danger bg-opacity-10">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <p class="extra-small fw-bold text-uppercase mb-1 text-muted tracking-wider">Cash Runway</p>
                                    <h6 class="text-dark mb-0">Time until funds depleted</h6>
                                </div>
                                <div class="bg-danger bg-opacity-25 text-danger rounded-4 p-3">
                                    <i class="fa-solid fa-hourglass-half fs-4"></i>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <p class="extra-small text-muted fw-bold mb-1">Days</p>
                                    <h4 class="fw-bold mb-0 outfit text-danger">{{ number_format($advancedMetrics['cash_runway_days'], 0) }}</h4>
                                </div>
                                <div class="col-6">
                                    <p class="extra-small text-muted fw-bold mb-1">Months</p>
                                    <h4 class="fw-bold mb-0 outfit text-danger">{{ number_format($advancedMetrics['cash_runway_months'], 1) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Budget Adherence & Cash Flow Trend -->
    <div class="row g-4 mb-4">
        <!-- Budget Adherence -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100 bg-success bg-opacity-10">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="extra-small fw-bold text-uppercase mb-1 text-muted tracking-wider">Budget Adherence</p>
                            <h6 class="text-dark mb-0">Staying on track with spending limits</h6>
                        </div>
                        <div class="bg-success bg-opacity-25 text-success rounded-4 p-3">
                            <i class="fa-solid fa-bullseye fs-4"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-end gap-2 mb-3">
                        <h2 class="fw-bold mb-0 outfit text-success">{{ number_format($advancedMetrics['budget_adherence'], 0) }}%</h2>
                        <span class="text-muted fw-bold mb-1">of budgets on track</span>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-4">
                            <div class="bg-white rounded-3 p-3 text-center">
                                <p class="extra-small text-muted mb-1 fw-bold">Total</p>
                                <h5 class="mb-0 fw-bold outfit">{{ $advancedMetrics['total_budgets'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white rounded-3 p-3 text-center">
                                <p class="extra-small text-muted mb-1 fw-bold">On Track</p>
                                <h5 class="mb-0 fw-bold text-success outfit">{{ $advancedMetrics['budgets_on_track'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-white rounded-3 p-3 text-center">
                                <p class="extra-small text-muted mb-1 fw-bold">Over</p>
                                <h5 class="mb-0 fw-bold text-danger outfit">{{ $advancedMetrics['total_budgets'] - $advancedMetrics['budgets_on_track'] }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-3 p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small text-muted fw-bold">Budgeted</span>
                            <span class="small fw-bold">${{ number_format($advancedMetrics['total_budgeted'], 0) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="small text-muted fw-bold">Spent</span>
                            <span class="small fw-bold text-{{ $advancedMetrics['total_actual_spent'] > $advancedMetrics['total_budgeted'] ? 'danger' : 'success' }}">${{ number_format($advancedMetrics['total_actual_spent'], 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cash Flow Trend -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: #f3e8ff;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="extra-small fw-bold text-uppercase mb-1 text-muted tracking-wider">Cash Flow Trend</p>
                            <h6 class="text-dark mb-0">Net cash flow last 6 months</h6>
                        </div>
                        <div class="rounded-4 p-3" style="background-color: #e9d5ff; color: #7c3aed;">
                            <i class="fa-solid fa-chart-line fs-4"></i>
                        </div>
                    </div>
                    <div class="row g-2">
                        @foreach($advancedMetrics['cash_flow_trend'] as $flow)
                            <div class="col-4 col-md-2">
                                <div class="bg-white rounded-3 p-2 text-center">
                                    <p class="extra-small text-muted mb-1 fw-bold">{{ \Carbon\Carbon::parse($flow->month)->format('M') }}</p>
                                    <p class="mb-0 small fw-bold text-{{ $flow->net >= 0 ? 'success' : 'danger' }}">
                                        {{ $flow->net >= 0 ? '+' : '' }}${{ number_format(abs($flow->net), 0) }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @php
                        $avgCashFlow = $advancedMetrics['cash_flow_trend']->avg('net');
                        $trend = $advancedMetrics['cash_flow_trend']->count() >= 2
                            ? $advancedMetrics['cash_flow_trend']->last()->net - $advancedMetrics['cash_flow_trend']->first()->net
                            : 0;
                    @endphp
                    <div class="mt-3 bg-white rounded-3 p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted fw-bold">6-Month Average</span>
                            <span class="fw-bold text-{{ $avgCashFlow >= 0 ? 'success' : 'danger' }}">
                                {{ $avgCashFlow >= 0 ? '+' : '' }}${{ number_format($avgCashFlow, 0) }}/mo
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Month-over-Month & Expense Breakdown -->
    <div class="row g-4 mb-4">
        <!-- Month-over-Month Comparison -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: #ccfbf1;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="extra-small fw-bold text-uppercase mb-1 text-muted tracking-wider">Month-over-Month</p>
                            <h6 class="text-dark mb-0">Current vs last month comparison</h6>
                        </div>
                        <div class="rounded-4 p-3" style="background-color: #99f6e4; color: #0d9488;">
                            <i class="fa-solid fa-calendar-days fs-4"></i>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="bg-white rounded-3 p-3">
                                <p class="extra-small text-muted mb-2 fw-bold text-uppercase tracking-wider">Income Change</p>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h4 class="fw-bold mb-0 outfit text-{{ $advancedMetrics['income_change'] >= 0 ? 'success' : 'danger' }}">
                                        {{ $advancedMetrics['income_change'] >= 0 ? '+' : '' }}{{ number_format($advancedMetrics['income_change'], 1) }}%
                                    </h4>
                                    <i class="fa-solid fa-arrow-{{ $advancedMetrics['income_change'] >= 0 ? 'up' : 'down' }} text-{{ $advancedMetrics['income_change'] >= 0 ? 'success' : 'danger' }}"></i>
                                </div>
                                <p class="extra-small text-muted mb-1">Last: ${{ number_format($advancedMetrics['last_month_income'], 0) }}</p>
                                <p class="extra-small text-muted mb-0">Current: ${{ number_format($financialMetrics['monthly_income'], 0) }}</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-white rounded-3 p-3">
                                <p class="extra-small text-muted mb-2 fw-bold text-uppercase tracking-wider">Expense Change</p>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h4 class="fw-bold mb-0 outfit text-{{ $advancedMetrics['expense_change'] >= 0 ? 'danger' : 'success' }}">
                                        {{ $advancedMetrics['expense_change'] >= 0 ? '+' : '' }}{{ number_format($advancedMetrics['expense_change'], 1) }}%
                                    </h4>
                                    <i class="fa-solid fa-arrow-{{ $advancedMetrics['expense_change'] >= 0 ? 'up' : 'down' }} text-{{ $advancedMetrics['expense_change'] >= 0 ? 'danger' : 'success' }}"></i>
                                </div>
                                <p class="extra-small text-muted mb-1">Last: ${{ number_format($advancedMetrics['last_month_expenses'], 0) }}</p>
                                <p class="extra-small text-muted mb-0">Current: ${{ number_format($financialMetrics['monthly_expenses'], 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Category Breakdown -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100 bg-warning bg-opacity-10">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="extra-small fw-bold text-uppercase mb-1 text-muted tracking-wider">Top Expense Categories</p>
                            <h6 class="text-dark mb-0">Last 30 days breakdown</h6>
                        </div>
                        <div class="bg-warning bg-opacity-25 text-warning rounded-4 p-3">
                            <i class="fa-solid fa-chart-pie fs-4"></i>
                        </div>
                    </div>
                    @php
                        $maxExpense = $advancedMetrics['expense_breakdown']->max('total');
                    @endphp
                    <div class="d-flex flex-column gap-3">
                        @foreach($advancedMetrics['expense_breakdown'] as $expense)
                            <div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="small fw-bold text-dark">
                                        @if($expense->category)
                                            {{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $expense->category->name) }}
                                        @else
                                            Uncategorized
                                        @endif
                                    </span>
                                    <span class="small fw-bold text-warning">${{ number_format($expense->total, 0) }}</span>
                                </div>
                                <div class="progress" style="height: 6px; background-color: rgba(0,0,0,0.05);">
                                    <div class="progress-bar bg-warning" role="progressbar"
                                        style="width: {{ $maxExpense > 0 ? ($expense->total / $maxExpense * 100) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 4: Top Expenses & Recurring Expenses -->
    <div class="row g-4 mb-4">
        <!-- Top Expenses -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: #f1f5f9;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="extra-small fw-bold text-uppercase mb-1 text-muted tracking-wider">Top 5 Expenses</p>
                            <h6 class="text-dark mb-0">Largest transactions this month</h6>
                        </div>
                        <div class="rounded-4 p-3" style="background-color: #cbd5e1; color: #475569;">
                            <i class="fa-solid fa-receipt fs-4"></i>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        @foreach($advancedMetrics['top_expenses'] as $expense)
                            <div class="bg-white rounded-3 p-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="flex-grow-1">
                                        <p class="small fw-bold text-dark mb-1 text-truncate" style="max-width: 250px;">
                                            {{ $expense->description }}
                                        </p>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="extra-small text-muted">
                                                <i class="fa-regular fa-calendar me-1"></i>{{ $expense->transaction_date->format('M d') }}
                                            </span>
                                            @if($expense->category)
                                                <span class="extra-small text-muted">
                                                    <i class="fa-solid fa-tag me-1"></i>{{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $expense->category->name) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="fw-bold text-danger">${{ number_format(abs($expense->amount), 0) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Recurring Expenses -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: #fef3c7;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="extra-small fw-bold text-uppercase mb-1 text-muted tracking-wider">Recurring Expenses</p>
                            <h6 class="text-dark mb-0">Detected subscriptions & recurring costs</h6>
                        </div>
                        <div class="rounded-4 p-3" style="background-color: #fde68a; color: #d97706;">
                            <i class="fa-solid fa-rotate fs-4"></i>
                        </div>
                    </div>
                    @if($advancedMetrics['recurring_expenses']->count() > 0)
                        <div class="d-flex flex-column gap-2 mb-3">
                            @foreach($advancedMetrics['recurring_expenses'] as $recurring)
                                <div class="bg-white rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <p class="small fw-bold text-dark mb-1 text-truncate" style="max-width: 250px;">
                                                {{ $recurring->description }}
                                            </p>
                                            <span class="extra-small text-muted">
                                                <i class="fa-solid fa-arrows-rotate me-1"></i>{{ $recurring->frequency }}x in last 3 months
                                            </span>
                                        </div>
                                        <span class="fw-bold" style="color: #d97706;">${{ number_format($recurring->avg_amount, 0) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="bg-white rounded-3 p-3">
                            <div class="d-flex justify-content-between">
                                <span class="small text-muted fw-bold">Estimated Monthly Recurring</span>
                                <span class="fw-bold" style="color: #d97706;">${{ number_format($advancedMetrics['total_recurring_cost'], 0) }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No recurring expenses detected</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Row 5: Income Sources -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background-color: #e0e7ff;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <p class="extra-small fw-bold text-uppercase mb-1 text-muted tracking-wider">Income Sources</p>
                            <h6 class="text-dark mb-0">Revenue breakdown by category (last 30 days)</h6>
                        </div>
                        <div class="rounded-4 p-3" style="background-color: #c7d2fe; color: #4f46e5;">
                            <i class="fa-solid fa-coins fs-4"></i>
                        </div>
                    </div>
                    @if($advancedMetrics['income_sources']->count() > 0)
                        @php
                            $totalIncomeSources = $advancedMetrics['income_sources']->sum('total');
                        @endphp
                        <div class="row g-3">
                            @foreach($advancedMetrics['income_sources'] as $source)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="bg-white rounded-3 p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <p class="small fw-bold text-dark mb-1">
                                                    @if($source->category)
                                                        {{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $source->category->name) }}
                                                    @else
                                                        Uncategorized
                                                    @endif
                                                </p>
                                                <p class="extra-small text-muted mb-2">{{ $source->count }} {{ $source->count == 1 ? 'transaction' : 'transactions' }}</p>
                                            </div>
                                            <span class="fw-bold text-success">${{ number_format($source->total, 0) }}</span>
                                        </div>
                                        <div class="progress" style="height: 6px; background-color: rgba(0,0,0,0.05);">
                                            <div class="progress-bar" style="background-color: #4f46e5; width: {{ $totalIncomeSources > 0 ? ($source->total / $totalIncomeSources * 100) : 0 }}%"></div>
                                        </div>
                                        <p class="extra-small text-muted mb-0 mt-2">
                                            {{ $totalIncomeSources > 0 ? number_format(($source->total / $totalIncomeSources * 100), 1) : 0 }}% of total income
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No categorized income this month</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Account Balances Section -->
    <div class="mb-3 mt-5">
        <h4 class="fw-bold outfit text-primary-adaptive mb-1">Account Balances</h4>
        <p class="text-secondary-adaptive small mb-0">Current balances across all your accounts</p>
    </div>

    <div class="row g-4 mb-5">
        @foreach($totalsByCurrency as $currency => $total)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm hover-lift h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="flex-grow-1">
                                <p class="text-secondary-adaptive extra-small fw-bold text-uppercase mb-2">
                                    {{ $currency }} Balance
                                </p>
                                <h3 class="fw-bold mb-0 outfit text-primary-adaptive">
                                    {{ $currency }} {{ number_format($total, 2) }}
                                </h3>
                            </div>
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                                <i class="fa-solid fa-wallet fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm text-bg-primary hover-lift h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1">
                            <p class="extra-small fw-bold text-uppercase mb-2 text-white-50">
                                Total Transactions
                            </p>
                            <h3 class="fw-bold mb-2 text-white outfit">
                                {{ number_format($transactionCount) }}
                            </h3>
                            <div class="small text-white-50">
                                Across all accounts
                            </div>
                        </div>
                        <div class="flex-shrink-0 bg-white bg-opacity-25 rounded-4 p-3">
                            <i class="fa-solid fa-receipt fs-4 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Header -->
    <div class="mb-3">
        <h4 class="fw-bold outfit text-primary-adaptive mb-1">Spending Analysis</h4>
        <p class="text-secondary-adaptive small mb-0">Track your spending patterns and top categories</p>
    </div>

    <div class="row g-4 mb-5">
        <!-- Monthly Chart -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <h5 class="fw-bold mb-1 outfit text-primary-adaptive">Monthly Insights</h5>
                        <p class="extra-small text-secondary-adaptive mb-0 fw-bold text-uppercase">
                            Spending Trends (Last 6 Months)
                        </p>
                    </div>
                    <div id="monthlyChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>

        <!-- Category Spending -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 outfit text-primary-adaptive">Top Allocation</h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary border-0 px-3 fw-bold">90 Days</span>
                    </div>
                <div class="list-group list-group-flush">
                    @forelse($categorySpending as $item)
                        <div
                            class="list-group-item px-0 py-3 border-0 d-flex align-items-center justify-content-between transition-all rounded-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 44px; height: 44px; background-color: {{ $item->category->color ?? '#6366f1' }}15; color: {{ $item->category->color ?? '#6366f1' }}; border: 1px solid {{ $item->category->color ?? '#6366f1' }}30;">
                                    @if($item->category->icon && str_contains($item->category->icon, 'fa-'))
                                        <i class="{{ $item->category->icon }} fs-6"></i>
                                    @else
                                        <span class="fs-6">{{ $item->category->icon ?? '📊' }}</span>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold text-primary-adaptive">{{ $item->category->name }}</div>
                                    <div class="text-muted extra-small fw-bold text-uppercase">{{ $item->count }}
                                        activity</div>
                                </div>
                            </div>
                            <div class="text-end fw-bold outfit text-primary-adaptive">{{ number_format($item->total, 2) }}</div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fa-solid fa-chart-pie fs-1 text-secondary-adaptive opacity-25"></i>
                            </div>
                            <h6 class="fw-bold text-primary-adaptive mb-2">No category data yet</h6>
                            <p class="text-secondary-adaptive small mb-3">
                                Start categorizing transactions to see insights
                            </p>
                            <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-primary">
                                View Transactions
                            </a>
                        </div>
                    @endforelse
                </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold mb-1 outfit text-primary-adaptive">Recent Ledger</h5>
                            <p class="extra-small text-secondary-adaptive mb-0">Latest financial activity</p>
                        </div>
                        <a href="{{ route('transactions.index') }}" class="btn btn-light btn-sm fw-bold px-3 py-2 border">
                            Explore Full History <i class="fa-solid fa-arrow-right ms-1 small"></i>
                        </a>
                    </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="text-secondary-adaptive extra-small fw-bold text-uppercase">Date</th>
                                <th class="text-secondary-adaptive extra-small fw-bold text-uppercase">Description</th>
                                <th class="text-secondary-adaptive extra-small fw-bold text-uppercase">Account</th>
                                <th class="text-secondary-adaptive extra-small fw-bold text-uppercase">Category</th>
                                <th class="text-secondary-adaptive extra-small fw-bold text-uppercase text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_date->format('M d, Y') }}</td>
                                    <td>
                                        <div class="fw-bold">{{ Str::limit($transaction->description, 40) }}</div>
                                        @if($transaction->user_description)
                                            <div class="text-muted small">{{ $transaction->user_description }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary-adaptive border">{{ $transaction->account->name }}</span>
                                    </td>
                                    <td>
                                        @if($transaction->category)
                                            <x-pill
                                                variant="{{ $transaction->category->category_type === 'expense' ? 'danger' : ($transaction->category->category_type === 'income' ? 'success' : 'info') }}">
                                                {{ $transaction->category->name }}
                                            </x-pill>
                                        @else
                                            <x-pill variant="warning">Uncategorized</x-pill>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-primary-adaptive' }}">
                                        {{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="fa-solid fa-inbox fs-1 text-secondary-adaptive opacity-25"></i>
                                        </div>
                                        <h6 class="fw-bold text-primary-adaptive mb-2">No transactions yet</h6>
                                        <p class="text-secondary-adaptive small mb-3">
                                            Get started by adding your first transaction
                                        </p>
                                        <a href="{{ route('transactions.create') }}" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-plus me-2"></i>Add Transaction
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const getTheme = () => document.documentElement.getAttribute('data-bs-theme');

            const options = {
                series: [{
                    name: 'Spending',
                    data: @json($chartData['data'])
                }],
                chart: {
                    type: 'area',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    background: 'transparent'
                },
                theme: { mode: getTheme() },
                colors: ['#465fff'],
                stroke: { curve: 'smooth', width: 3 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.45,
                        opacityTo: 0.05,
                        stops: [20, 100, 100, 100]
                    }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: @json($chartData['labels']),
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: { show: false },
                grid: {
                    borderColor: getTheme() === 'dark' ? '#334155' : '#e2e8f0',
                    strokeDashArray: 4,
                },
                tooltip: {
                    theme: getTheme(),
                    y: { formatter: (val) => "$ " + val.toLocaleString() }
                }
            };

            const chart = new ApexCharts(document.querySelector("#monthlyChart"), options);
            chart.render();

            // Watch for theme changes
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'data-bs-theme') {
                        const newTheme = getTheme();
                        chart.updateOptions({
                            theme: { mode: newTheme },
                            grid: { borderColor: newTheme === 'dark' ? '#334155' : '#e2e8f0' },
                            tooltip: { theme: newTheme }
                        });
                    }
                });
            });
            observer.observe(document.documentElement, { attributes: true });
        });
    </script>
@endpush