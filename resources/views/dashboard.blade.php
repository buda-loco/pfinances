@extends('layouts.bootstrap')

@section('title', 'Dashboard')

@section('content')
    <div class="row g-4 mb-4">
        @foreach($totalsByCurrency as $currency => $total)
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm hover-lift h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="flex-grow-1">
                                <p class="text-secondary-adaptive extra-small fw-bold text-uppercase mb-2">
                                    {{ $currency }} Balance
                                </p>
                                <h3 class="fw-bold mb-2 outfit text-primary-adaptive">
                                    {{ $currency }} {{ number_format($total, 2) }}
                                </h3>
                                {{-- TODO: Add real trend data from backend --}}
                                <div class="d-flex align-items-center gap-1 small">
                                    <i class="fa-solid fa-arrow-up text-success"></i>
                                    <span class="fw-bold text-success">12.5%</span>
                                    <span class="text-secondary-adaptive">vs last month</span>
                                </div>
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
        <h4 class="fw-bold outfit text-primary-adaptive mb-1">Financial Overview</h4>
        <p class="text-secondary-adaptive small mb-0">Track your spending patterns and top categories</p>
    </div>

    <div class="row g-4 mb-5">
        <!-- Monthly Chart -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-1 outfit text-primary-adaptive">Monthly Insights</h5>
                            <p class="extra-small text-secondary-adaptive mb-0 fw-bold text-uppercase">
                                Spending Trends Over 6 Months
                            </p>
                        </div>
                        {{-- TODO: Add period selector functionality --}}
                        <div class="btn-group btn-group-sm" role="group" aria-label="Time period selector">
                            <button type="button" class="btn btn-light active">6M</button>
                            <button type="button" class="btn btn-light">1Y</button>
                            <button type="button" class="btn btn-light">All</button>
                        </div>
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