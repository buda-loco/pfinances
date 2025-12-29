@extends('layouts.bootstrap')

@section('title', 'Add Transaction')

@section('content')
    <x-breadcrumb :items="[
        ['label' => 'Transactions', 'url' => route('transactions.index')],
        ['label' => 'Add Transaction']
    ]" />

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-0 outfit text-dark">Add New Transaction</h5>
                    <p class="text-muted extra-small mb-0">Record a new income or expense</p>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            @include('transactions.partials.form', ['transaction' => null])
        </div>
    </div>
@endsection
