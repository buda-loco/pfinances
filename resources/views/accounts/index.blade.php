@extends('layouts.bootstrap')

@section('title', 'Accounts')

@section('content')
    <div x-data="{ 
                search: '',
                match(name, institution) {
                    const s = this.search.toLowerCase();
                    return name.toLowerCase().includes(s) || (institution && institution.toLowerCase().includes(s));
                }
            }">
        <div class="d-flex flex-column gap-4">

            <!-- Header & Controls -->
            <div class="card border-0 shadow-sm p-4">
                <div class="row align-items-center g-3 mb-3">
                    <div class="col-12 col-md">
                        <h5 class="fw-bold mb-0 outfit text-dark">Financial Portfolio</h5>
                        <p class="small text-muted mb-0">Unified management of bank accounts, liquid assets, and cash flow.</p>
                    </div>
                    <div class="col-12 col-md-auto d-flex justify-content-md-end">
                        <a href="{{ route('accounts.create') }}"
                            class="btn btn-primary d-flex align-items-center gap-2 px-4">
                            <i class="fa-solid fa-plus"></i> Create Account
                        </a>
                    </div>
                </div>

                <!-- Always Visible Filter Bar -->
                <div class="p-3 bg-light rounded-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" x-model="search" class="form-control" placeholder="Search accounts...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accounts Table -->
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4 text-muted extra-small fw-bold text-uppercase tracking-wider">Account Name
                                </th>
                                <th class="text-muted extra-small fw-bold text-uppercase tracking-wider">Type</th>
                                <th class="text-muted extra-small fw-bold text-uppercase tracking-wider">Institution</th>
                                <th class="text-muted extra-small fw-bold text-uppercase tracking-wider">Owner</th>
                                <th class="text-muted extra-small fw-bold text-uppercase tracking-wider text-end">Balance
                                </th>
                                <th class="text-muted extra-small fw-bold text-uppercase tracking-wider text-center">Status
                                </th>
                                <th class="text-muted extra-small fw-bold text-uppercase tracking-wider text-center">Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tableAccounts as $account)
                                <tr
                                    x-show="match('{{ addslashes($account->name) }}', '{{ addslashes($account->institution ?? '') }}')">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center bg-light rounded-4"
                                                style="width: 48px; height: 48px; border: 1px solid rgba(0,0,0,0.05);">
                                                @if($account->account_type == 'bank') <i
                                                    class="fa-solid fa-building-columns text-primary fs-5"></i>
                                                @elseif($account->account_type == 'travel_money') <i
                                                    class="fa-solid fa-plane-departure text-info fs-5"></i>
                                                @elseif($account->account_type == 'cash') <i
                                                    class="fa-solid fa-money-bill-1-wave text-success fs-5"></i>
                                                @else <i class="fa-solid fa-folder text-muted fs-5"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $account->name }}</div>
                                                <div
                                                    class="extra-small text-muted fw-bold text-uppercase tracking-wider opacity-75">
                                                    {{ $account->account_number }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <x-pill variant="secondary">
                                            {{ str_replace('_', ' ', $account->account_type) }}
                                        </x-pill>
                                    </td>
                                    <td>
                                        <span class="text-muted small fw-medium">{{ $account->institution ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <x-pill
                                            variant="{{ $account->ownership === 'shared' ? 'primary' : ($account->ownership === 'buda' ? 'success' : 'danger') }}">
                                            {{ $account->ownership }}
                                        </x-pill>
                                    </td>
                                    <td class="text-end">
                                        <div class="fw-bold text-dark outfit fs-6">
                                            {{ number_format($account->current_balance, 2) }}</div>
                                        <div class="extra-small text-muted fw-bold text-uppercase tracking-widest">
                                            {{ $account->currency }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($account->is_active)
                                            <x-pill variant="success">Active</x-pill>
                                        @else
                                            <x-pill variant="secondary">Inactive</x-pill>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('accounts.edit', $account) }}"
                                            class="btn btn-light btn-sm border d-inline-flex align-items-center gap-1">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted mb-3"><i class="fa-solid fa-wallet fs-1 opacity-25"></i></div>
                                        <h5 class="fw-bold">No accounts found</h5>
                                        <p class="text-muted small">Start by adding your first financial account.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($tableAccounts->hasPages())
                <div class="d-flex justify-content-center mt-2">
                    {{ $tableAccounts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection