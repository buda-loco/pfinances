@extends('layouts.bootstrap')

@section('title', 'Transactions')

@section('content')
    <div x-data="transactionManager()" class="d-flex flex-column">

        <!-- Stats Summary -->
        <div class="row g-4 section-gap">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm p-4 hover-lift">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted extra-small fw-bold text-uppercase mb-1 tracking-wider">Total Transactions
                            </p>
                            <h3 class="fw-bold mb-0 outfit text-dark">{{ number_format($totalTransactions) }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                            <i class="fa-solid fa-file-invoice fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm p-4 hover-lift">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted extra-small fw-bold text-uppercase mb-1 tracking-wider">Categorized</p>
                            <h3 class="fw-bold mb-0 text-success outfit">{{ number_format($categorizedCount) }}</h3>
                            <div class="extra-small text-muted mt-1 fw-bold">
                                {{ $totalTransactions > 0 ? round(($categorizedCount / $totalTransactions) * 100, 1) : 0 }}%
                                OF TOTAL
                            </div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-4 p-3">
                            <i class="fa-solid fa-circle-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <a href="{{ route('transactions.index', ['category_id' => 'uncategorized']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm p-4 hover-lift" style="cursor: pointer;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted extra-small fw-bold text-uppercase mb-1 tracking-wider">Uncategorized</p>
                                <h3 class="fw-bold mb-0 text-warning outfit">{{ number_format($uncategorizedCount) }}</h3>
                                <div class="extra-small text-muted mt-1 fw-bold">
                                    {{ $totalTransactions > 0 ? round(($uncategorizedCount / $totalTransactions) * 100, 1) : 0 }}%
                                    OF TOTAL
                                </div>
                            </div>
                            <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3">
                                <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Filters & Actions -->
        <div class="card border-0 shadow-sm overflow-visible">
            <div class="card-header bg-white border-0 p-4">
                <div class="row align-items-center g-3 mb-3">
                    <div class="col-12 col-lg-auto">
                        <h5 class="fw-bold mb-0">Transactions List</h5>
                    </div>
                    <div class="col-12 col-lg d-flex justify-content-lg-end gap-2">
                        <a href="{{ route('transactions.export', request()->query()) }}"
                            class="btn btn-outline-success d-flex align-items-center gap-2 fw-bold">
                            <i class="fa-solid fa-download"></i> Export CSV
                        </a>
                        <a href="{{ route('transactions.create') }}"
                            class="btn btn-primary d-flex align-items-center gap-2 px-4 fw-bold">
                            <i class="fa-solid fa-plus"></i> Add Transaction
                        </a>
                    </div>
                </div>

                <!-- Always Visible Filter Bar -->
                <form method="GET" action="{{ route('transactions.index') }}" class="p-3 bg-light rounded-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-6 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Month</label>
                            <select name="month" class="form-select">
                                <option value="">All Months</option>
                                @foreach(range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Year</label>
                            <select name="year" class="form-select">
                                <option value="">All Years</option>
                                @foreach(range(date('Y'), date('Y') - 5) as $y)
                                    <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Account</label>
                            <select name="account_id" class="form-select">
                                <option value="">All Accounts</option>
                                @foreach($accounts ?? [] as $account)
                                    <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">All Categories</option>
                                <option value="uncategorized" {{ request('category_id') == 'uncategorized' ? 'selected' : '' }}>
                                    🏷️ Uncategorized
                                </option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-auto d-flex gap-2">
                            <a href="{{ route('transactions.index') }}"
                                class="btn btn-outline-secondary d-flex align-items-center gap-1">
                                <i class="fa-solid fa-rotate-left"></i> Clear
                            </a>
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                                <i class="fa-solid fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Bulk Actions Banner (Alpine) -->
            <div x-show="selectedTransactions.length > 0" x-cloak
                class="bg-primary bg-opacity-10 border-top border-bottom px-4 py-2 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-bold text-primary small">
                        <span x-text="selectedTransactions.length"></span> items selected
                    </span>
                    <button type="button" @click="clearSelection()"
                        class="btn btn-link link-secondary btn-sm p-0 text-decoration-none small">Clear</button>
                    <select x-model="bulkCategoryId" class="form-select form-select-sm border-primary border-opacity-25"
                        style="width: 200px;">
                        <option value="">Move to category...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" @click="bulkUpdate()" class="btn btn-primary btn-sm px-3">Apply</button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" x-model="quickSearchCategory"
                        class="form-control form-control-sm"
                        placeholder="Quick search category..."
                        aria-label="Quick search category"
                        style="width: 200px;"
                        @input="filterCategories()">
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 40px;">
                                <div class="form-check">
                                    <input type="checkbox" @change="toggleAll($event)" class="form-check-input">
                                </div>
                            </th>
                            <x-table-sort-header sortField="transaction_date" label="Date" route="transactions.index" />
                            <x-table-sort-header sortField="description" label="Description" route="transactions.index" style="min-width: 250px;" />
                            <x-table-sort-header sortField="account.name" label="Account" route="transactions.index" />
                            <x-table-sort-header sortField="category.name" label="Category" route="transactions.index" />
                            <x-table-sort-header sortField="amount" label="Amount" route="transactions.index" class="text-end" />
                            <th class="text-center text-muted extra-small fw-bold text-uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr id="transaction-{{ $transaction->id }}"
                                x-data="{
                                                        editing: false,
                                                        date: '{{ $transaction->transaction_date->format('Y-m-d') }}',
                                                        description: '{{ addslashes($transaction->description) }}',
                                                        categoryId: '{{ $transaction->category_id }}',
                                                        accountId: '{{ $transaction->account_id }}',
                                                        saving: false,
                                                        async save() {
                                                            this.saving = true;
                                                            const success = await saveTransaction({{ $transaction->id }}, this.categoryId, this.date, this.description, this.accountId);
                                                            if (success) {
                                                                this.editing = false;
                                                                window.toast('Transaction updated successfully.', 'success', 3000);
                                                                // Reload to show updated data without jarring page reload
                                                                setTimeout(() => window.location.reload(), 500);
                                                            } else {
                                                                window.toast('Failed to update transaction. Please try again.', 'danger', 5000);
                                                            }
                                                            this.saving = false;
                                                        }
                                                    }"
                                class="{{ request('transaction_id') == $transaction->id ? 'table-active' : '' }}">
                                <td class="ps-4">
                                    <div class="form-check">
                                        <input type="checkbox" :checked="selectedTransactions.includes({{ $transaction->id }})"
                                            @change="toggleTransaction({{ $transaction->id }})" class="form-check-input">
                                    </div>
                                </td>
                                <td>
                                    <template x-if="!editing">
                                        <div class="small fw-medium text-muted">
                                            {{ $transaction->transaction_date->format('M d, Y') }}
                                        </div>
                                    </template>
                                    <template x-if="editing">
                                        <input type="date" x-model="date" class="form-control form-control-sm">
                                    </template>
                                </td>
                                <td>
                                    <template x-if="!editing">
                                        <div>
                                            <div class="fw-bold text-dark">{{ Str::limit($transaction->description, 50) }}</div>
                                            @if($transaction->user_description)
                                                <div class="small text-muted italic">
                                                    {{ Str::limit($transaction->user_description, 40) }}
                                                </div>
                                            @endif
                                        </div>
                                    </template>
                                    <template x-if="editing">
                                        <input type="text" x-model="description" class="form-control form-control-sm">
                                    </template>
                                </td>
                                <td>
                                    <template x-if="!editing">
                                        <span
                                            class="badge bg-light text-dark border fw-normal">{{ $transaction->account->name }}</span>
                                    </template>
                                    <template x-if="editing">
                                        <select x-model="accountId" class="form-select form-select-sm">
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </template>
                                </td>
                                <td>
                                    <template x-if="!editing">
                                        <div>
                                            @if ($transaction->category)
                                                <x-pill
                                                    variant="{{ $transaction->category->category_type === 'expense' ? 'danger' : ($transaction->category->category_type === 'income' ? 'success' : 'info') }}">
                                                    {{ $transaction->category->name }}
                                                </x-pill>
                                            @else
                                                <x-pill variant="warning">Uncategorized</x-pill>
                                            @endif
                                        </div>
                                    </template>
                                    <template x-if="editing">
                                        <select x-model="categoryId" @change="save()" class="form-select form-select-sm">
                                            <option value="">Select category...</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </template>
                                </td>
                                <td class="text-end fw-bold {{ $transaction->amount >= 0 ? 'text-success' : 'text-dark' }}">
                                    <i class="fa-solid {{ $transaction->amount >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} small me-1"></i>
                                    {{ $transaction->currency }} {{ number_format(abs($transaction->amount), 2) }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <template x-if="!editing">
                                            <button type="button" @click="editing = true"
                                                class="btn btn-link link-secondary p-0"
                                                aria-label="Edit transaction">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        </template>
                                        <template x-if="editing">
                                            <div class="d-flex align-items-center gap-1">
                                                <button type="button" @click="save()" :disabled="saving"
                                                    class="btn btn-success btn-sm rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 28px; height: 28px;"
                                                    aria-label="Save changes">
                                                    <i class="fa-solid fa-check small" x-show="!saving"></i>
                                                    <i class="fa-solid fa-spinner fa-spin small" x-show="saving"></i>
                                                </button>
                                                <button type="button" @click="editing = false"
                                                    class="btn btn-light border btn-sm rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 28px; height: 28px;"
                                                    aria-label="Cancel editing">
                                                    <i class="fa-solid fa-xmark small"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted mb-3"><i class="fa-solid fa-receipt fs-1 opacity-25"></i></div>
                                    <h5 class="fw-bold">No transactions found</h5>
                                    <p class="text-muted small">Adjust your search or filters to see more.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $transactions->links() }}
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        async function saveTransaction(transactionId, categoryId, date, description, accountId) {
            try {
                const response = await fetch(`/transactions/${transactionId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        category_id: categoryId || null,
                        date: date,
                        description: description,
                        account_id: accountId
                    })
                });
                const data = await response.json();
                return data.success;
            } catch (error) {
                console.error('Error updating transaction:', error);
                return false;
            }
        }

        function transactionManager() {
            return {
                selectedTransactions: [],
                bulkCategoryId: '',
                quickSearchCategory: '',
                allCategories: @json($categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name])),

                toggleTransaction(id) {
                    const index = this.selectedTransactions.indexOf(id);
                    if (index > -1) {
                        this.selectedTransactions.splice(index, 1);
                    } else {
                        this.selectedTransactions.push(id);
                    }
                },

                toggleAll(event) {
                    if (event.target.checked) {
                        this.selectedTransactions = @json($transactions->pluck('id'));
                    } else {
                        this.selectedTransactions = [];
                    }
                },

                clearSelection() {
                    this.selectedTransactions = [];
                    this.bulkCategoryId = '';
                    this.quickSearchCategory = '';
                },

                filterCategories() {
                    const select = this.$refs.categorySelect || document.querySelector('[x-model="bulkCategoryId"]');
                    if (!select) return;
                    
                    const search = this.quickSearchCategory.toLowerCase();
                    const options = select.querySelectorAll('option');
                    
                    options.forEach(option => {
                        if (!option.value) return; // Skip "Move to category..." option
                        const matches = option.textContent.toLowerCase().includes(search);
                        option.style.display = matches ? '' : 'none';
                        
                        // Auto-select first match
                        if (matches && !this.bulkCategoryId && search.length > 0) {
                            this.bulkCategoryId = option.value;
                        }
                    });
                },

                async bulkUpdate() {
                    if (!this.bulkCategoryId || this.selectedTransactions.length === 0) return;
                    try {
                        const response = await fetch('/transactions/bulk-update', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                transaction_ids: this.selectedTransactions,
                                category_id: this.bulkCategoryId
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            window.toast(data.message || 'Transactions updated successfully.', 'success', 3000);
                            setTimeout(() => window.location.reload(), 500);
                        } else {
                            window.toast(data.message || 'Failed to update transactions.', 'danger', 5000);
                        }
                    } catch (error) {
                        console.error('Error bulk updating:', error);
                        window.toast('An error occurred while updating transactions.', 'danger', 5000);
                    }
                }
            }
        }

        // Scroll to and highlight transaction from search
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const transactionId = urlParams.get('transaction_id');

            if (transactionId) {
                const element = document.getElementById(`transaction-${transactionId}`);
                if (element) {
                    // Smooth scroll to the element
                    setTimeout(() => {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        // Add pulsing highlight animation
                        element.style.animation = 'highlight-pulse 2s ease-in-out';

                        // Remove animation after it completes
                        setTimeout(() => {
                            element.style.animation = '';
                        }, 2000);
                    }, 300);
                }
            }
        });
    </script>

    <style>
        @keyframes highlight-pulse {
            0%, 100% {
                background-color: transparent;
                box-shadow: none;
            }
            50% {
                background-color: rgba(13, 110, 253, 0.15);
                box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
            }
        }

        .table-active {
            background-color: rgba(13, 110, 253, 0.08);
        }
    </style>
@endpush