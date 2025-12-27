@extends('layouts.bootstrap')

@section('title', 'Expenses')

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
                        <h5 class="fw-bold mb-0">Expense Analysis</h5>
                    </div>
                    <div class="col-12 col-lg d-flex justify-content-lg-end gap-2">
                        <button onclick="exportProPDF()" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                            <i class="fa-solid fa-file-pdf text-danger"></i> Export PDF
                        </button>
                        <a href="{{ route('transactions.index', ['action' => 'add', 'type' => 'expense']) }}"
                            class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Add Expense
                        </a>
                    </div>
                </div>

                <!-- Always Visible Filter Bar -->
                <form method="GET" action="{{ route('expenses.index') }}" class="p-3 bg-light rounded-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Month</label>
                            <select name="selected_month" class="form-select">
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                        {{ \DateTime::createFromFormat('!m', $m)->format('F') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Year</label>
                            <select name="selected_year" class="form-select">
                                @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
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

            <!-- Expense Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="expenseTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Category</th>
                            <th class="text-end">Actual</th>
                            <th class="text-end">Budget</th>
                            <th class="text-end">Difference</th>
                            <th class="text-end">Prev. Month</th>
                            <th class="text-end pe-4">Prev. Year</th>
                        </tr>
                        <!-- Totals Row -->
                        <tr class="bg-primary bg-opacity-10 fw-bold border-bottom-0">
                            <td class="ps-4 py-3 text-primary">TOTALS</td>
                            <td class="text-end py-3">${{ number_format($totals['actual'], 2) }}</td>
                            <td class="text-end py-3 text-muted">${{ number_format($totals['budget'], 2) }}</td>
                            <td class="text-end py-3 {{ $totals['diff'] >= 0 ? 'text-success' : 'text-danger' }}">
                                ${{ number_format($totals['diff'], 2) }}
                            </td>
                            <td class="text-end py-3 text-muted small">${{ number_format($totals['prev_month'], 2) }}</td>
                            <td class="text-end py-3 pe-4 text-muted small">${{ number_format($totals['prev_year'], 2) }}
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summaryData as $row)
                            <tr x-show="match('{{ addslashes($row->category->name) }}')"
                                class="{{ !$row->category->parent_id ? 'table-light fw-bold' : '' }}">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-light rounded-3 d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px;">
                                            <i
                                                class="{{ (isset($row->category->icon) && strtolower($row->category->icon) !== 'tag') ? $row->category->icon : 'fa-solid fa-folder' }} text-muted small"></i>
                                        </div>
                                        <span class="text-dark">{{ $row->category->name }}</span>
                                    </div>
                                </td>
                                <td class="text-end font-monospace text-dark">${{ number_format($row->actual, 2) }}</td>
                                <td class="text-end font-monospace text-muted">${{ number_format($row->budget, 2) }}</td>
                                <td class="text-end">
                                    <span
                                        class="badge border {{ $row->diff >= 0 ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle' }} px-2 py-1">
                                        ${{ number_format($row->diff, 2) }}
                                    </span>
                                </td>
                                <td class="text-end font-monospace text-muted small">${{ number_format($row->prev_month, 2) }}
                                </td>
                                <td class="text-end pe-4 font-monospace text-muted small">
                                    ${{ number_format($row->prev_year, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-3"><i class="fa-solid fa-receipt fs-1 opacity-25"></i></div>
                                    <h5 class="fw-bold">No expenses found</h5>
                                    <p class="text-muted small">Adjust your filters to see more.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <script>
        window.exportProPDF = function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Header
            doc.setFontSize(22);
            doc.setTextColor(13, 110, 253); // Bootstrap Primary
            doc.text('Expense Report', 14, 25);

            doc.setFontSize(10);
            doc.setTextColor(100);
            doc.text(`Report Period: {{ $periodText }}`, 14, 32);
            doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 14, 38);

            // Table
            doc.autoTable({
                html: '#expenseTable',
                startY: 50,
                theme: 'striped',
                headStyles: { fillColor: [13, 110, 253] },
                bodyStyles: { fontSize: 9 },
                columnStyles: {
                    0: { fontStyle: 'bold' },
                    1: { halign: 'right' },
                    2: { halign: 'right' },
                    3: { halign: 'right' },
                    4: { halign: 'right' },
                    5: { halign: 'right' }
                }
            });

            doc.save(`expenses-{{ $selectedMonth }}-{{ $selectedYear }}.pdf`);
        }
    </script>
@endpush