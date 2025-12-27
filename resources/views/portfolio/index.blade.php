@extends('layouts.bootstrap')

@section('title', 'Portfolio Performance')

@section('content')
    <div class="d-flex flex-column gap-4">

        <!-- Header -->
        <div class="card border-0 shadow-sm p-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md">
                    <h5 class="fw-bold mb-0">Portfolio Performance</h5>
                    <p class="small text-muted mb-0">Track your asset growth and account history.</p>
                </div>
                <div class="col-12 col-md-auto">
                    <div class="btn-group shadow-sm">
                        <a href="{{ route('portfolio.index', ['group' => 'monthly']) }}"
                            class="btn {{ $group === 'monthly' ? 'btn-primary' : 'btn-light border' }} btn-sm px-3 fw-bold">Monthly</a>
                        <a href="{{ route('portfolio.index', ['group' => 'quarterly']) }}"
                            class="btn {{ $group === 'quarterly' ? 'btn-primary' : 'btn-light border' }} btn-sm px-3 fw-bold">Quarterly</a>
                        <a href="{{ route('portfolio.index', ['group' => 'annually']) }}"
                            class="btn {{ $group === 'annually' ? 'btn-primary' : 'btn-light border' }} btn-sm px-3 fw-bold">Annually</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Portfolio Growth Chart -->
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <h6 class="fw-bold mb-0">Portfolio Growth</h6>
                    <div class="d-flex flex-wrap gap-3">
                        @php $colors = ['#3C50E0', '#80CAEE', '#10B981', '#F59E0B', '#6366F1', '#EC4899', '#8B5CF6']; @endphp
                        @foreach($currencies as $index => $currency)
                            @php $color = $colors[$index % count($colors)]; @endphp
                            <div class="form-check form-check-inline me-0">
                                <input class="form-check-input currency-toggle" type="checkbox" value="{{ $currency }}"
                                    id="chk_{{ $currency }}" checked>
                                <label class="form-check-label small fw-bold text-muted cursor-pointer"
                                    for="chk_{{ $currency }}">
                                    <span class="d-inline-block rounded-circle me-1"
                                        style="width: 8px; height: 8px; background-color: {{ $color }};"></span>
                                    {{ $currency }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div id="portfolioChart" style="min-height: 400px;"></div>
            </div>
        </div>

        <!-- Monthly Performance (By Currency) -->
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h6 class="fw-bold mb-0">Monthly Performance (By Currency)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Month</th>
                            @foreach($currencies as $currency)
                                <th class="text-end">{{ $currency }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tableRows as $row)
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $row['month'] }}</td>
                                @foreach($currencies as $currency)
                                    <td class="text-end font-mono small">{{ number_format($row[$currency], 2) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Balance History Matrix -->
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h6 class="fw-bold mb-0">Balance History Matrix</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Account</th>
                            <th>Curr</th>
                            @foreach($matrixDates as $date)
                                <th class="text-end">{{ \Carbon\Carbon::parse($date)->format('M d') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matrixAccounts as $account)
                            <tr>
                                <td class="ps-4 fw-bold text-dark small">{{ $account->name }}</td>
                                <td class="text-muted fw-bold extra-small uppercase">{{ $account->currency }}</td>
                                @foreach($matrixDates as $index => $date)
                                    @php
                                        $bal = $account->balance_map[$date] ?? 0;
                                        $prevDate = $index > 0 ? $matrixDates[$index - 1] : null;
                                        $prevBal = $prevDate ? ($account->balance_map[$prevDate] ?? 0) : 0;
                                        $pctChange = ($prevBal != 0) ? (($bal - $prevBal) / abs($prevBal)) * 100 : 0;
                                    @endphp
                                    <td class="text-end">
                                        <div class="d-flex flex-column align-items-end">
                                            <span class="font-mono fs-7 {{ $bal < 0 ? 'text-danger fw-bold' : '' }}">
                                                {{ $bal != 0 ? number_format($bal, 0) : '-' }}
                                            </span>
                                            @if($index > 0 && $prevBal != 0)
                                                <x-growth-indicator :value="$pctChange" />
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td class="ps-4" colspan="2">TOTAL</td>
                            @foreach($matrixDates as $date)
                                @php
                                    $sum = 0;
                                    foreach ($matrixAccounts as $acc) {
                                        $sum += ($acc->balance_map[$date] ?? 0);
                                    }
                                @endphp
                                <td class="text-end font-mono small text-dark border-start">{{ number_format($sum, 0) }}</td>
                            @endforeach
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const defaults = ['EUR', 'USD', 'ARS', 'AUD'];
            const chartColors = @json($colors);
            const rawSeries = @json($chartSeries);
            const chartLabels = @json($chartLabels);

            const getTheme = () => document.documentElement.getAttribute('data-bs-theme');

            // Sync Checkboxes
            document.querySelectorAll('.currency-toggle').forEach(chk => {
                if (!defaults.includes(chk.value)) { chk.checked = false; }
            });

            const options = {
                series: rawSeries,
                chart: {
                    type: 'area',
                    height: 400,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    theme: { mode: getTheme() },
                    events: {
                        mounted: function (chartContext) {
                            rawSeries.forEach(s => {
                                if (!defaults.includes(s.name)) { chartContext.hideSeries(s.name); }
                            });
                        }
                    }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2.5 },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.25,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: chartLabels,
                    labels: { style: { fontSize: '12px' } }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => val ? val.toLocaleString(undefined, { maximumFractionDigits: 0 }) : '0'
                    }
                },
                grid: {
                    borderColor: getTheme() === 'dark' ? '#334155' : '#e2e8f0',
                    strokeDashArray: 4
                },
                tooltip: {
                    theme: getTheme(),
                    x: { show: true },
                    y: { formatter: (val) => val ? val.toLocaleString() : '0' }
                },
                colors: chartColors,
                legend: { show: false }
            };

            const chart = new ApexCharts(document.querySelector("#portfolioChart"), options);
            chart.render();

            document.querySelectorAll('.currency-toggle').forEach(chk => {
                chk.addEventListener('change', function () {
                    this.checked ? chart.showSeries(this.value) : chart.hideSeries(this.value);
                });
            });

            const observer = new MutationObserver(() => {
                const newTheme = getTheme();
                chart.updateOptions({
                    theme: { mode: newTheme },
                    grid: { borderColor: newTheme === 'dark' ? '#334155' : '#e2e8f0' },
                    tooltip: { theme: newTheme }
                });
            });
            observer.observe(document.documentElement, { attributes: true });
        });
    </script>
@endpush