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
                    @keydown.enter.prevent="handleEnter()"
                    @keydown.tab.prevent="focusFirstResult()"
                    @keydown.arrow-down.prevent="focusFirstResult()"
                    class="form-control form-control-lg border-0 shadow-none outfit fw-medium"
                    placeholder="Search anything or type a command..."
                    style="font-size: 1.1rem;"
                    x-ref="searchInput"
                    role="combobox"
                    aria-autocomplete="list"
                    :aria-expanded="searchResults.length > 0"
                    aria-controls="search-results-list">
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
                                    <tr @click="showDetails(result.type, result.id)"
                                        @keydown.enter.prevent="showDetails(result.type, result.id)"
                                        @keydown.arrow-up.prevent="focusPreviousResult(index)"
                                        @keydown.arrow-down.prevent="focusNextResult(index)"
                                        @keydown.escape.prevent="focusSearchInput()"
                                        :tabindex="focusedIndex === index ? 0 : -1"
                                        :class="{ 'table-active': focusedIndex === index }"
                                        :ref="'result-' + index"
                                        role="option"
                                        :aria-selected="focusedIndex === index"
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
                            <span x-text="searchResults.length"></span> results found •
                            <kbd class="bg-white border px-2 py-1 rounded">Tab</kbd> or <kbd class="bg-white border px-2 py-1 rounded">↓</kbd> to navigate •
                            <kbd class="bg-white border px-2 py-1 rounded">Enter</kbd> to select •
                            <kbd class="bg-white border px-2 py-1 rounded">Esc</kbd> to return
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
                        <span class="extra-small fw-bold text-muted text-uppercase tracking-wider">Quick Actions</span>
                    </div>
                    <button
                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                        @click="window.location.href='{{ route('accounts.index') }}?action=add'">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-plus text-info fs-5" style="width: 25px;"></i>
                            <span class="fw-semibold text-dark">Add Account</span>
                        </div>
                        <span class="badge bg-light text-muted fw-bold border extra-small">N A</span>
                    </button>
                    <button
                        class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                        @click="window.location.href='{{ route('transactions.index') }}?action=add'">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid fa-plus text-warning fs-5" style="width: 25px;"></i>
                            <span class="fw-semibold text-dark">New Transaction</span>
                        </div>
                        <span class="badge bg-light text-muted fw-bold border extra-small">N T</span>
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
                this.focusedIndex = -1; // Reset focus when new search is performed
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

            navigateTo(url) {
                window.location.href = url;
            },

            showDetails(type, id) {
                // Close command bar
                const commandModal = bootstrap.Modal.getInstance(document.getElementById('command-bar'));
                if (commandModal) commandModal.hide();

                // Show detail modal
                if (window.searchDetailModalInstance) {
                    window.searchDetailModalInstance.show(type, id);
                }
            },

            handleEnter() {
                // If a result is focused, select it
                if (this.focusedIndex >= 0 && this.searchResults[this.focusedIndex]) {
                    const result = this.searchResults[this.focusedIndex];
                    this.showDetails(result.type, result.id);
                    return;
                }

                // If there are search results but none focused, select the first one
                if (this.searchResults.length > 0) {
                    const result = this.searchResults[0];
                    this.showDetails(result.type, result.id);
                    return;
                }

                // Otherwise, try command handling
                this.handleCommand(this.query);
            },

            focusFirstResult() {
                if (this.searchResults.length > 0) {
                    this.focusedIndex = 0;
                    this.$nextTick(() => {
                        this.focusResultAtIndex(0);
                    });
                }
            },

            focusPreviousResult(currentIndex) {
                if (currentIndex > 0) {
                    this.focusedIndex = currentIndex - 1;
                    this.focusResultAtIndex(this.focusedIndex);
                } else {
                    // If at the first result, go back to search input
                    this.focusSearchInput();
                }
            },

            focusNextResult(currentIndex) {
                if (currentIndex < this.searchResults.length - 1) {
                    this.focusedIndex = currentIndex + 1;
                    this.focusResultAtIndex(this.focusedIndex);
                }
            },

            focusResultAtIndex(index) {
                const element = this.$refs['result-' + index];
                if (element && element[0]) {
                    element[0].focus();
                }
            },

            focusSearchInput() {
                this.focusedIndex = -1;
                this.$refs.searchInput.focus();
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

    // Auto-focus search input when modal opens
    document.getElementById('command-bar').addEventListener('shown.bs.modal', function () {
        const input = this.querySelector('input[type="text"]');
        if (input) {
            setTimeout(() => {
                input.focus();
                input.select();
            }, 100);
        }
    });

    // Reset focus when modal closes
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
    /* Keyboard navigation styles */
    #search-results-list tbody tr:focus {
        outline: 2px solid #0d6efd;
        outline-offset: -2px;
        box-shadow: inset 0 0 0 2px #0d6efd;
    }

    #search-results-list tbody tr.table-active {
        background-color: rgba(13, 110, 253, 0.1);
        font-weight: 500;
    }

    kbd {
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1;
    }

    /* Smooth transitions for focus changes */
    #search-results-list tbody tr {
        transition: background-color 0.15s ease-in-out;
    }
</style>