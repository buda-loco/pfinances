<div id="command-bar" class="modal fade" tabindex="-1" role="dialog" aria-modal="true" data-bs-keyboard="true"
    data-bs-backdrop="true">
    <!-- Modal Dialog -->
    <div class="modal-dialog modal-lg modal-dialog-centered mt-5 pt-md-5">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-white">
            <!-- Search Input -->
            <div class="p-3 border-bottom d-flex align-items-center gap-3">
                <i class="fa-solid fa-magnifying-glass text-muted fs-5 ms-2"></i>
                <input type="text" class="form-control form-control-lg border-0 shadow-none outfit fw-medium"
                    placeholder="Search or type a command..." @keydown.enter="handleCommand($event.target.value)"
                    style="font-size: 1.1rem;">
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Command List -->
            <div class="modal-body p-0" style="max-height: 450px; overflow-y: auto;">
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
    function handleCommand(query) {
        query = query.toLowerCase().trim();
        const routes = {
            'dashboard': '{{ route('dashboard') }}',
            'transactions': '{{ route('transactions.index') }}',
            'accounts': '{{ route('accounts.index') }}',
            'projects': '{{ route('projects.index') }}',
            'income': '{{ route('income.index') }}',
            'expenses': '{{ route('expenses.index') }}',
            'portfolio': '{{ route('portfolio.index') }}'
        };

        if (routes[query]) {
            window.location.href = routes[query];
        } else if (query === 'dark' || query === 'light' || query === 'toggle') {
            document.documentElement.setAttribute('data-bs-theme', document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
            if (typeof commandBarOpen !== 'undefined') commandBarOpen = false;
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        const key = e.key.toLowerCase();
        if (e.altKey) {
            if (key === 'd') window.location.href = '{{ route('dashboard') }}';
            if (key === 't') window.location.href = '{{ route('transactions.index') }}';
        }
    });
</script>