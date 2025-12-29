<div id="command-bar" class="modal fade" tabindex="-1" role="dialog" aria-modal="true" data-bs-keyboard="true"
    data-bs-backdrop="true" x-data="commandBarSearch()">
    <!-- Modal Dialog -->
    <div class="modal-dialog modal-lg modal-dialog-centered mt-5 pt-md-5">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-white">
            <!-- Search Input -->
            <div class="p-3 border-bottom d-flex align-items-center gap-3">
                <i class="fa-solid fa-magnifying-glass text-muted fs-5 ms-2"
                   :class="{ 'fa-spin fa-spinner': loading }"></i>
                <input type="text"
                    x-model="query"
                    @input.debounce.300ms="performSearch()"
                    @keydown="handleKeyDown($event)"
                    class="form-control form-control-lg border-0 shadow-none outfit fw-medium"
                    placeholder="Search anything or type a command..."
                    style="font-size: 1.1rem;"
                    x-ref="searchInput">
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Search Results or Command List -->
            <div class="modal-body p-0" style="max-height: 450px; overflow-y: auto;">
                <!-- Search Results Table -->
                <div x-show="searchResults.length > 0" x-cloak>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0"
                               id="search-results-list"
                               role="listbox"
                               aria-label="Search results">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 extra-small fw-bold text-uppercase text-muted tracking-wider">Type</th>
                                    <th class="extra-small fw-bold text-uppercase text-muted tracking-wider">Name</th>
                                    <th class="extra-small fw-bold text-uppercase text-muted tracking-wider">Details</th>
                                    <th class="extra-small fw-bold text-uppercase text-muted tracking-wider">Amount/Info</th>
                                    <th class="extra-small fw-bold text-uppercase text-muted tracking-wider">Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(result, index) in searchResults" :key="index">
                                    <tr @click="selectResult(index)"
                                        :class="{ 'table-active': focusedIndex === index }"
                                        :data-index="index"
                                        class="search-result-row"
                                        style="cursor: pointer;">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div :class="`bg-${result.color} bg-opacity-10 text-${result.color} rounded-3 p-2 d-flex align-items-center justify-content-center`"
                                                     style="width: 36px; height: 36px;">
                                                    <i :class="`fa-solid ${result.icon}`"></i>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark" x-text="result.title"></div>
                                            <div class="extra-small text-muted" x-text="result.date" x-show="result.date"></div>
                                        </td>
                                        <td>
                                            <div class="small text-muted" x-text="result.subtitle"></div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold"
                                                 :class="result.type === 'transaction' && result.meta.includes('-') ? 'text-danger' : 'text-success'"
                                                 x-text="result.meta"></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border" x-text="result.badge"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 bg-light border-top text-center">
                        <span class="extra-small text-muted fw-bold">
                            <span x-text="searchResults.length"></span> results found
                            <span x-show="focusedIndex >= 0" class="text-primary">
                                • Result <span x-text="focusedIndex + 1"></span> of <span x-text="searchResults.length"></span> focused
                            </span>
                            <br>
                            <kbd class="bg-white border px-2 py-1 rounded small">Tab</kbd> or <kbd class="bg-white border px-2 py-1 rounded small">↓</kbd> to navigate •
                            <kbd class="bg-white border px-2 py-1 rounded small">↑ ↓</kbd> to move •
                            <kbd class="bg-white border px-2 py-1 rounded small">Enter</kbd> to select •
                            <kbd class="bg-white border px-2 py-1 rounded small">Esc</kbd> to return
                        </span>
                    </div>
                </div>

                <!-- No Results Message -->
                <div x-show="query.length >= 2 && searchResults.length === 0 && !loading" x-cloak class="text-center py-5">
                    <i class="fa-solid fa-magnifying-glass fs-1 text-muted opacity-25 mb-3"></i>
                    <h6 class="fw-bold text-dark">No results found</h6>
                    <p class="text-muted small">Try searching with different keywords</p>
                </div>

                <!-- Default Command List -->
                <div x-show="query.length < 2 && searchResults.length === 0" x-cloak>
                    <div class="list-group list-group-flush">
                    <!-- Navigation -->
                    <div class="bg-light py-2 px-4 border-bottom border-top flex-column d-flex">
                        <span class="extra-small fw-bold text-muted text-uppercase tracking-wider">Navigation</span>
                    </div>
                    <button
                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                        @click="window.location.href='{{ route('dashboard') }}'">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-chart-pie text-primary fs-5" style="width: 25px;"></i>
                            <span class="fw-semibold text-dark">Go to Dashboard</span>
                        </div>
                        <span class="badge bg-light text-muted fw-bold border extra-small">G D</span>
                    </button>
                    <button
                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                        @click="window.location.href='{{ route('transactions.index') }}'">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-receipt text-success fs-5" style="width: 25px;"></i>
                            <span class="fw-semibold text-dark">View Transactions</span>
                        </div>
                        <span class="badge bg-light text-muted fw-bold border extra-small">G T</span>
                    </button>

                    <!-- Quick Actions -->
                    <div class="bg-light py-2 px-4 border-bottom border-top">
                        <span class="extra-small fw-bold text-muted text-uppercase tracking-wider">Create New</span>
                    </div>
                    <button
                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                        @click="window.location.href='{{ route('transactions.index') }}?action=add'">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-plus-circle text-success fs-5" style="width: 25px;"></i>
                            <span class="fw-semibold text-dark">New Transaction</span>
                        </div>
                        <span class="badge bg-light text-muted fw-bold border extra-small">N T</span>
                    </button>
                    <button
                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                        @click="window.location.href='{{ route('accounts.index') }}?action=add'">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-plus-circle text-info fs-5" style="width: 25px;"></i>
                            <span class="fw-semibold text-dark">New Account</span>
                        </div>
                        <span class="badge bg-light text-muted fw-bold border extra-small">N A</span>
                    </button>
                    <button
                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                        @click="window.location.href='{{ route('projects.index') }}?action=add'">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-plus-circle text-primary fs-5" style="width: 25px;"></i>
                            <span class="fw-semibold text-dark">New Project</span>
                        </div>
                        <span class="badge bg-light text-muted fw-bold border extra-small">N P</span>
                    </button>
                    <button
                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                        @click="window.location.href='{{ route('categories.index') }}?action=add'">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-plus-circle text-warning fs-5" style="width: 25px;"></i>
                            <span class="fw-semibold text-dark">New Category</span>
                        </div>
                        <span class="badge bg-light text-muted fw-bold border extra-small">N C</span>
                    </button>
                    <button
                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                        @click="window.location.href='{{ route('budgets.index') }}?action=add'">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-plus-circle text-danger fs-5" style="width: 25px;"></i>
                            <span class="fw-semibold text-dark">New Budget</span>
                        </div>
                        <span class="badge bg-light text-muted fw-bold border extra-small">N B</span>
                    </button>

                    <!-- Settings -->
                    <div class="bg-light py-2 px-4 border-bottom border-top">
                        <span class="extra-small fw-bold text-muted text-uppercase tracking-wider">Appearance</span>
                    </div>
                    <button
                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                        @click="document.documentElement.setAttribute('data-bs-theme', document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark'); commandBarOpen = false">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-circle-half-stroke text-secondary fs-5" style="width: 25px;"></i>
                            <span class="fw-semibold text-dark">Toggle Dark Mode</span>
                        </div>
                        <span class="badge bg-light text-muted fw-bold border extra-small">T D</span>
                    </button>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer bg-light border-0 justify-content-between py-2 px-4">
                <div class="d-flex gap-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-muted border py-1">↵</span>
                        <span class="extra-small text-muted fw-bold">Select</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-muted border py-1 px-2">ESC</span>
                        <span class="extra-small text-muted fw-bold">Close</span>
                    </div>
                </div>
                <div class="extra-small text-muted fw-bold opacity-50 text-uppercase tracking-widest">PFinances
                    Bootstrap</div>
            </div>
        </div>
    </div>
</div>

<script>
    function commandBarSearch() {
        return {
            query: '',
            searchResults: [],
            loading: false,
            focusedIndex: -1,

            async performSearch() {
                this.focusedIndex = -1;
                if (this.query.length < 2) {
                    this.searchResults = [];
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch(`{{ route('search') }}?q=${encodeURIComponent(this.query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();
                    this.searchResults = data.results || [];
                } catch (error) {
                    console.error('Search error:', error);
                    this.searchResults = [];
                } finally {
                    this.loading = false;
                }
            },

            handleKeyDown(e) {
                // Handle keyboard navigation
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (this.searchResults.length > 0) {
                        if (this.focusedIndex < this.searchResults.length - 1) {
                            this.focusedIndex++;
                        } else {
                            this.focusedIndex = 0;
                        }
                        this.scrollToFocused();
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (this.searchResults.length > 0) {
                        if (this.focusedIndex > 0) {
                            this.focusedIndex--;
                        } else {
                            this.focusedIndex = this.searchResults.length - 1;
                        }
                        this.scrollToFocused();
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (this.focusedIndex >= 0 && this.searchResults[this.focusedIndex]) {
                        this.selectResult(this.focusedIndex);
                    } else if (this.searchResults.length > 0) {
                        this.selectResult(0);
                    } else {
                        this.handleCommand(this.query);
                    }
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    this.focusedIndex = -1;
                }
            },

            selectResult(index) {
                const result = this.searchResults[index];
                if (result) {
                    this.showDetails(result.type, result.id);
                }
            },

            scrollToFocused() {
                this.$nextTick(() => {
                    const rows = document.querySelectorAll('.search-result-row');
                    if (rows[this.focusedIndex]) {
                        rows[this.focusedIndex].scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    }
                });
            },

            showDetails(type, id) {
                const commandModal = bootstrap.Modal.getInstance(document.getElementById('command-bar'));
                if (commandModal) commandModal.hide();

                if (window.searchDetailModalInstance) {
                    window.searchDetailModalInstance.show(type, id);
                }
            },

            handleCommand(query) {
                query = query.toLowerCase().trim();
                const routes = {
                    'dashboard': '{{ route('dashboard') }}',
                    'transactions': '{{ route('transactions.index') }}',
                    'accounts': '{{ route('accounts.index') }}',
                    'projects': '{{ route('projects.index') }}',
                    'income': '{{ route('income.index') }}',
                    'expenses': '{{ route('expenses.index') }}',
                    'portfolio': '{{ route('portfolio.index') }}',
                    'budgets': '{{ route('budgets.index') }}',
                    'categories': '{{ route('categories.index') }}'
                };

                if (routes[query]) {
                    window.location.href = routes[query];
                } else if (query === 'dark' || query === 'light' || query === 'toggle') {
                    document.documentElement.setAttribute('data-bs-theme',
                        document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('command-bar'));
                    if (modal) modal.hide();
                }
            }
        };
    }

    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        const key = e.key.toLowerCase();
        if (e.altKey) {
            if (key === 'd') window.location.href = '{{ route('dashboard') }}';
            if (key === 't') window.location.href = '{{ route('transactions.index') }}';
        }
    });

    document.getElementById('command-bar').addEventListener('shown.bs.modal', function () {
        const input = this.querySelector('input[type="text"]');
        if (input) {
            setTimeout(() => {
                input.focus();
                input.select();
            }, 100);
        }
    });

    document.getElementById('command-bar').addEventListener('hidden.bs.modal', function () {
        const searchComponent = Alpine.$data(this);
        if (searchComponent) {
            searchComponent.focusedIndex = -1;
            searchComponent.query = '';
            searchComponent.searchResults = [];
        }
    });
</script>

<style>
    #search-results-list tbody tr.table-active {
        background-color: rgba(13, 110, 253, 0.15) !important;
        outline: 2px solid #0d6efd;
        outline-offset: -2px;
    }

    #search-results-list tbody tr {
        transition: all 0.15s ease-in-out;
    }

    kbd {
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1;
    }
</style>