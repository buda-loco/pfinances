@extends('layouts.bootstrap')

@section('title', 'Import Data')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title h5">Import Data</h1>
                </div>
                <div class="card-body">
                    <div x-data="{
                        uploading: false,
                        dragOver: false,
                        stats: null,
                        error: null,
                        fileName: null,
                        importService: 'FrolloImportService', // Default selected service
                        handleFiles(files) {
                            if (files.length > 0) {
                                this.fileName = files[0].name;
                                this.uploadFile(files[0]);
                            }
                        },
                        uploadFile(file) {
                            this.uploading = true;
                            this.stats = null;
                            this.error = null;

                            const formData = new FormData();
                            formData.append('csv_file', file);
                            formData.append('import_service', this.importService); // Send selected service
                            formData.append('_token', '{{ csrf_token() }}');

                            fetch('{{ route('import.upload') }}', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                this.uploading = false;
                                if (data.success) {
                                    this.stats = data.stats;
                                } else {
                                    this.error = data.message;
                                }
                            })
                            .catch(error => {
                                this.uploading = false;
                                this.error = 'Upload failed: ' + error.message;
                            });
                        }
                    }">
                        <div class="mb-3">
                            <label for="importServiceSelect" class="form-label">Select Import Service:</label>
                            <select x-model="importService" id="importServiceSelect" class="form-select">
                                <option value="FrolloImportService">Frollo Import Service</option>
                                <option value="ExcelImportService">Excel Import Service</option>
                            </select>
                        </div>
                        <div @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                            @drop.prevent="dragOver = false; handleFiles($event.dataTransfer.files)"
                            class="text-center p-5 border rounded"
                            :class="{'border-primary': dragOver}">
                            <div x-show="!uploading">
                                <i class="bi bi-cloud-arrow-up-fill fs-1 text-primary"></i>
                                <p class="mt-3">Drag and drop your CSV file here or click to browse.</p>
                                <input id="file-upload" type="file" accept=".csv,.txt"
                                    @change="handleFiles($event.target.files)" class="d-none">
                                <label for="file-upload" class="btn btn-primary">
                                    Choose File
                                </label>
                            </div>
                            <div x-show="uploading" x-cloak>
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3">Processing import...</p>
                            </div>
                        </div>

                        <div x-show="error" x-cloak class="alert alert-danger mt-3" x-text="error"></div>

                        <div x-show="stats" x-cloak class="mt-3">
                            <div class="alert alert-success">
                                <p class="h5">Import Completed Successfully!</p>
                                <p>Your transactions have been processed and imported.</p>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Accounts Created
                                            <span class="badge bg-primary rounded-pill" x-text="stats?.accounts_created || 0"></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Accounts Updated
                                            <span class="badge bg-info rounded-pill" x-text="stats?.accounts_updated || 0"></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Transactions Created
                                            <span class="badge bg-success rounded-pill" x-text="stats?.transactions_created || 0"></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Duplicates Skipped
                                            <span class="badge bg-warning text-dark rounded-pill" x-text="stats?.transactions_skipped || 0"></span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6" x-show="stats?.errors && stats.errors.length > 0">
                                    <div class="alert alert-warning">
                                        <p class="h5">Import Warnings:</p>
                                        <ul class="mb-0">
                                            <template x-for="error in stats.errors" :key="error">
                                                <li x-text="error"></li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('transactions.index') }}" class="btn btn-primary">View Transactions</a>
                                <button @click="stats = null; fileName = null; error = null" class="btn btn-secondary">Import Another</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection