@extends('layouts.bootstrap')

@section('title', 'Projects')

@section('content')
    <div class="d-flex flex-column gap-4">

        <!-- Header -->
        <div class="card border-0 shadow-sm p-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md">
                    <h5 class="fw-bold mb-0">Projects Tracking</h5>
                    <p class="small text-muted mb-0">Track expenses for trips, purchases, and tax purposes.</p>
                </div>
                <div class="col-12 col-md-auto">
                    <a href="{{ route('projects.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 px-4 fw-bold">
                        <i class="fa-solid fa-plus small"></i> New Project
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Summary -->
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Total Projects</p>
                            <h4 class="fw-bold mb-0">{{ $totalProjects }}</h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3">
                            <i class="fa-solid fa-folder-tree fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Active</p>
                            <h4 class="fw-bold mb-0 text-success">{{ $activeProjects }}</h4>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="fa-solid fa-circle-play fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted small fw-bold text-uppercase mb-1">Completed</p>
                            <h4 class="fw-bold mb-0 text-info">{{ $completedProjects }}</h4>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info rounded-3 p-3">
                            <i class="fa-solid fa-circle-check fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="card border-0 shadow-sm p-4">
            <h6 class="fw-bold mb-4">Top Costing Projects</h6>
            <div id="topProjectsChart" style="min-height: 350px;"></div>
        </div>

        <!-- Projects Table -->
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <div class="row align-items-center g-3">
                    <div class="col-12 col-lg">
                        <form method="GET" action="{{ route('projects.index') }}" class="d-flex gap-2">
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fa-solid fa-magnifying-glass text-muted"></i>
                                </span>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="Search projects...">
                            </div>
                            <button type="submit" class="btn btn-light border">Search</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <x-table-sort-header sortField="name" label="Project" route="projects.index" class="ps-4" />
                            <th class="text-center">Status</th>
                            <x-table-sort-header sortField="total_spent" label="Spent" route="projects.index" class="text-end" />
                            <th class="text-end">Daily Avg</th>
                            <th class="text-center">Txs</th>
                            <th class="text-center">Dates</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background-color: {{ $project->color ?? '#6366f1' }}20; color: {{ $project->color ?? '#6366f1' }};">
                                            <i class="fa-solid fa-folder fs-6"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('projects.show', $project) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                                {{ $project->name }}
                                            </a>
                                            <div class="extra-small text-muted fw-bold uppercase px-1">{{ $project->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
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
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    ${{ number_format(abs($project->total_spent ?? 0), 2) }}
                                    @if($project->budget && $project->total_spent)
                                        @php $percentage = min(100, (abs($project->total_spent) / $project->budget) * 100); @endphp
                                        <div class="progress mt-1" style="height: 4px; width: 80px; margin-left: auto;">
                                            <div class="progress-bar {{ $percentage > 100 ? 'bg-danger' : 'bg-primary' }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($project->start_date && $project->end_date && $project->total_spent)
                                        @php
                                            $days = max(1, $project->start_date->diffInDays($project->end_date) + 1);
                                            $dailyAvg = abs($project->total_spent) / $days;
                                        @endphp
                                        <span class="fw-bold text-muted small">${{ number_format($dailyAvg, 2) }}</span>
                                        <div class="extra-small text-muted">{{ $days }} days</div>
                                    @else
                                        <span class="text-muted opacity-25">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border-0 fw-normal">{{ $project->transactions_count }}</span>
                                </td>
                                <td class="text-center">
                                    @if($project->start_date)
                                        <div class="extra-small fw-bold text-muted">{{ $project->start_date->format('M d, Y') }}</div>
                                        @if($project->end_date)
                                            <div class="text-muted" style="line-height: 0.5;">↓</div>
                                            <div class="extra-small fw-bold text-muted">{{ $project->end_date->format('M d, Y') }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted opacity-25">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ route('projects.show', $project) }}" class="btn btn-link link-secondary p-0" title="View"><i class="fa-solid fa-eye small"></i></a>
                                        <a href="{{ route('projects.edit', $project) }}" class="btn btn-link link-secondary p-0" title="Edit"><i class="fa-solid fa-pen-to-square small"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted mb-3"><i class="fa-solid fa-folder-open fs-1 opacity-25"></i></div>
                                    <h5 class="fw-bold">No projects found</h5>
                                    <p class="text-muted small">Create your first project to start tracking expenses.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($projects->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $projects->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const projects = @json($topProjects);
            if (projects.length === 0) {
                document.querySelector("#topProjectsChart").innerHTML = '<div class="py-5 text-center text-muted small">No expenses recorded yet.</div>';
                return;
            }

            const getTheme = () => document.documentElement.getAttribute('data-bs-theme');

            const options = {
                series: [{
                    name: 'Total Cost',
                    data: projects.map(p => p.cost)
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    theme: { mode: getTheme() }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 6,
                        barHeight: '60%',
                        distributed: true
                    }
                },
                colors: projects.map(p => p.color),
                dataLabels: {
                    enabled: true,
                    formatter: (val) => '$' + val.toLocaleString(),
                    style: { fontSize: '11px' }
                },
                xaxis: {
                    categories: projects.map(p => p.name),
                    labels: { style: { fontSize: '12px' } }
                },
                yaxis: {
                    labels: { style: { fontWeight: 600, fontSize: '13px' } }
                },
                grid: {
                    borderColor: getTheme() === 'dark' ? '#334155' : '#e2e8f0',
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } }
                },
                tooltip: {
                    theme: getTheme(),
                    y: { formatter: (val) => '$' + val.toLocaleString() }
                },
                legend: { show: false }
            };

            const chart = new ApexCharts(document.querySelector("#topProjectsChart"), options);
            chart.render();

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