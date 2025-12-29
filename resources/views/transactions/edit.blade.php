@extends('layouts.bootstrap')

@section('title', 'Edit Transaction')

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Transactions', 'url' => route('transactions.index')],
        ['label' => 'Edit Transaction']
    ]" />

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 p-4 pb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="fw-bold mb-0 outfit text-dark">Edit Transaction</h5>
                    <p class="text-muted extra-small mb-0">Update transaction details</p>
                </div>
                <form id="delete-transaction-form" action="{{ route('transactions.destroy', $transaction) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                        @click="$dispatch('confirm', {
                            title: 'Delete Transaction',
                            message: 'Are you sure you want to delete this transaction? This action cannot be undone.',
                            confirmText: 'Delete',
                            onConfirm: () => document.getElementById('delete-transaction-form').submit()
                        })"
                        class="btn btn-link link-danger p-0 text-decoration-none small">
                        <i class="fa-solid fa-trash-can me-1"></i> Delete Transaction
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body p-4">
            @include('transactions.partials.form', ['transaction' => $transaction])
        </div>
    </div>
@endsection
