@extends('layouts.bootstrap')

@section('title', 'Income')

@section('content')
    <div x-data="{ 
                    search: '',
                    match(name) {
                        return name.toLowerCase().includes(this.search.toLowerCase());
                    }
                }" class="d-flex flex-column gap-4">

        <!-- Header & Filters -->
        <div class="card border-0 shadow-sm overflow-visible">
            <div class="card-header bg-white border-0 p-4">
                <div class="row align-items-center g-3 mb-3">
                    <div class="col-12 col-lg-auto">
                        <h5 class="fw-bold mb-0">Income Overview</h5>
                    </div>
                    <div class="col-12 col-lg d-flex justify-content-lg-end">
                        <a href="{{ route('transactions.index', ['action' => 'add', 'type' => 'income']) }}"
                            class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Add Income
                        </a>
                    </div>
                </div>

                <!-- Always Visible Filter Bar -->
                <form method="GET" action="{{ route('income.index') }}" class="p-3 bg-light rounded-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Month</label>
                            <select name="month" class="form-select">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ \DateTime::createFromFormat('!m', $m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Year</label>
                            <select name="year" class="form-select">
                                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                                <i class="fa-solid fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Income Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <x-table-sort-header sortField="name" label="Category" route="income.index" class="ps-4" />
                            <x-table-sort-header sortField="actual" label="Actual" route="income.index" class="text-end" />
                            <x-table-sort-header sortField="budget" label="Budget" route="income.index" class="text-end" />
                            <x-table-sort-header sortField="difference" label="Difference" route="income.index" class="text-end" />
                            <x-table-sort-header sortField="previous_month" label="Prev. Month" route="income.index" class="text-end" />
                            <x-table-sort-header sortField="previous_year" label="Prev. Year" route="income.index" class="text-end pe-4" />
                        </tr>
                        <!-- Totals Row -->
                        <tr class="bg-primary bg-opacity-10 fw-bold border-bottom-0">
                            <td class="ps-4 py-3 text-primary">TOTALS</td>
                            <td class="text-end py-3 text-primary">${{ number_format($summary['actual']) }}</td>
                            <td class="text-end py-3 text-muted">${{ number_format($summary['planned']) }}</td>
                            <td
                                class="text-end py-3 {{ ($summary['actual'] - $summary['planned']) < 0 ? 'text-danger' : 'text-success' }}">
                                ${{ number_format($summary['actual'] - $summary['planned']) }}
                            </td>
                            <td class="text-end py-3 text-muted small">${{ number_format($summary['prev_month']) }}</td>
                            <td class="text-end py-3 pe-4 text-muted small">${{ number_format($summary['prev_year']) }}</td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr x-show="match('{{ addslashes($row['name']) }}')">
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $row['name'] }}</div>
                                </td>
                                <td class="text-end font-monospace text-dark fw-bold">${{ number_format($row['actual'], 0) }}
                                </td>
                                <td class="text-end font-monospace text-muted">${{ number_format($row['budget'], 0) }}</td>
                                <td class="text-end">
                                    <span
                                        class="badge {{ $row['difference'] < 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} px-2 py-1">
                                        ${{ number_format($row['difference'], 0) }}
                                    </span>
                                </td>
                                <td class="text-end font-monospace text-muted small">
                                    ${{ number_format($row['previous_month'], 2) }}</td>
                                <td class="text-end pe-4 font-monospace text-muted small">
                                    ${{ number_format($row['previous_year'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-3"><i class="fa-solid fa-receipt fs-1 opacity-25"></i></div>
                                    <h5 class="fw-bold">No income data found</h5>
                                    <p class="text-muted small">Adjust your filters to see more.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($data->hasPages())
                <div class="card-footer bg-white border-0 px-4 py-3">
                    {{ $data->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection