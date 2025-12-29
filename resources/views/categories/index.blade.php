@extends('layouts.bootstrap')

@section('title', 'Categories')

@section('content')
    <div class="d-flex flex-column gap-4">

        <!-- Stats Summary -->
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm p-4 hover-lift h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted extra-small fw-bold text-uppercase mb-1 tracking-wider">Total Categories</p>
                            <h3 class="fw-bold mb-0 text-dark outfit">{{ $totalCategories }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                            <i class="fa-solid fa-tags fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm p-4 hover-lift h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted extra-small fw-bold text-uppercase mb-1 tracking-wider">Operational Status</p>
                            <h3 class="fw-bold mb-0 text-success outfit">{{ $activeCategories }}</h3>
                            <div class="extra-small text-muted mt-1 fw-bold text-uppercase tracking-wider opacity-75">{{ $totalCategories > 0 ? round(($activeCategories / $totalCategories) * 100, 1) : 0 }}% ACTIVE</div>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded-4 p-3">
                            <i class="fa-solid fa-circle-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm p-4 hover-lift h-100">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted extra-small fw-bold text-uppercase mb-1 tracking-wider">Expense Segments</p>
                            <h3 class="fw-bold mb-0 text-danger outfit">{{ $expenseCount }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 text-danger rounded-4 p-3">
                            <i class="fa-solid fa-cart-shopping fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header & Filters -->
        <div class="card border-0 shadow-sm overflow-visible">
            <div class="card-header bg-white border-0 p-4">
                <div class="row align-items-center g-3 mb-3">
                    <div class="col-12 col-lg-auto">
                        <h5 class="fw-bold mb-0 outfit text-dark">Categorization Engine</h5>
                    </div>
                    <div class="col-12 col-lg d-flex justify-content-lg-end">
                        <a href="{{ route('categories.create') }}" class="btn btn-primary d-flex align-items-center gap-2 px-4">
                            <i class="fa-solid fa-plus"></i> New Category
                        </a>
                    </div>
                </div>

                <!-- Always Visible Filter Bar -->
                <form method="GET" action="{{ route('categories.index') }}" class="p-3 bg-light rounded-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search categories...">
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <label class="form-label small fw-bold text-muted mb-1">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                                <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                                <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-auto d-flex gap-2">
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                                <i class="fa-solid fa-rotate-left"></i> Clear
                            </a>
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                                <i class="fa-solid fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Categories Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <x-table-sort-header sortField="name" label="Category Definition" route="categories.index" class="ps-4" />
                            <x-table-sort-header sortField="parent_id" label="Parent Association" route="categories.index" />
                            <x-table-sort-header sortField="is_active" label="Lifecycle" route="categories.index" class="text-center" />
                            <x-table-sort-header sortField="monthly_budget" label="Budget Cap" route="categories.index" class="text-end" />
                            <x-table-sort-header sortField="total_spent" label="Actual vs Target" route="categories.index" class="text-end" />
                            <th class="text-muted extra-small fw-bold text-uppercase tracking-wider text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                            <tr class="{{ !$category->parent_id ? 'table-light' : '' }}" x-data="{ 
                                editingParent: false, 
                                parentId: '{{ $category->parent_id }}',
                                async updateParent() {
                                    try {
                                        const response = await fetch('/categories/{{ $category->id }}', {
                                            method: 'PATCH',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                            },
                                            body: JSON.stringify({
                                                parent_id: this.parentId || null,
                                                is_active: {{ $category->is_active ? 'true' : 'false' }},
                                                name: '{{ $category->name }}',
                                                code: '{{ $category->code }}',
                                                category_type: '{{ $category->category_type }}'
                                            })
                                        });
                                        if (!response.ok) throw new Error('Failed to update');
                                        this.editingParent = false;
                                        window.location.reload();
                                    } catch (e) {
                                        console.error(e);
                                    }
                                }
                            }">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background-color: {{ $category->category_type === 'expense' ? 'rgba(220, 53, 69, 0.1)' : 'rgba(25, 135, 84, 0.1)' }}; color: {{ $category->category_type === 'expense' ? '#dc3545' : '#198754' }};">
                                            @if($category->icon && strtolower($category->icon) !== 'tag')
                                                <i class="{{ $category->icon }} small"></i>
                                            @else
                                                <span class="small fw-bold">{{ mb_substr(preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $category->name), 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $category->name) }}</div>
                                            <div class="small text-muted fw-bold extra-small uppercase tracking-wider">{{ $category->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="position-relative" @click="editingParent = true">
                                        <template x-if="!editingParent">
                                            <div class="d-flex align-items-center gap-2 cursor-pointer p-1 rounded hover-bg-light">
                                                <span class="small text-muted border-bottom border-dashed" x-text="parentId ? (typeof parentCategories !== 'undefined' ? (parentCategories.find(p => p.id == parentId)?.name.replace(/^\s*TAG\s*[-_: ]*\s*/i, '') || '{{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $category->parent->name ?? 'None') }}') : '{{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $category->parent->name ?? 'None') }}') : 'None'"></span>
                                                <i class="fa-solid fa-pen extra-small text-muted opacity-50"></i>
                                            </div>
                                        </template>
                                        <template x-if="editingParent">
                                            <select x-model="parentId" @change="updateParent()" @blur="editingParent = false" class="form-select form-select-sm">
                                                <option value="">None</option>
                                                @foreach($parentCategories as $parent)
                                                    @if($parent->id !== $category->id)
                                                        <option value="{{ $parent->id }}">{{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $parent->name) }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </template>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($category->is_active)
                                        <x-pill variant="success">Active</x-pill>
                                    @else
                                        <x-pill variant="secondary">Inactive</x-pill>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="small text-muted">{{ $category->monthly_budget > 0 ? '$' . number_format($category->monthly_budget, 2) : '-' }}</span>
                                </td>
                                <td class="text-end fw-bold {{ $category->total_spent < 0 ? 'text-dark' : 'text-success' }}">
                                    @if($category->total_spent)
                                        ${{ number_format(abs($category->total_spent), 2) }}
                                    @else
                                        <span class="text-muted opacity-25">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-link link-secondary p-0"
                                        aria-label="Edit {{ $category->name }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    <div class="d-flex justify-content-center">
                        {{ $categories->links() }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Categories by Group (Tabs) -->
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-4">Categories by Group</h5>
            <div x-data="{ activeTab: '{{ $parentCategories->first()->id ?? '' }}' }">
                <div class="row g-4">
                    <div class="col-12 col-md-3">
                        <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                            @foreach($parentCategories as $parent)
                                <button type="button" @click="activeTab = '{{ $parent->id }}'" class="list-group-item list-group-item-action border-0 d-flex justify-content-between align-items-center py-2.5" :class="activeTab === '{{ $parent->id }}' ? 'active bg-primary' : ''">
                                    <span class="small fw-medium">{{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $parent->name) }}</span>
                                    <span class="badge rounded-pill bg-light text-dark small">{{ $parent->children->count() }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-12 col-md-9">
                        @foreach($parentCategories as $parent)
                            <div x-show="activeTab === '{{ $parent->id }}'" x-transition>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold mb-0 text-primary">{{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $parent->name) }} Group</h6>
                                </div>
                                <div class="table-responsive border rounded-3">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3 border-0">Sub-Category</th>
                                                <th class="border-0">Code</th>
                                                <th class="border-0 text-center">Status</th>
                                                <th class="border-0 text-end">Budget</th>
                                                <th class="border-0 text-end">Spent</th>
                                                <th class="border-0 text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($parent->children as $child)
                                                <tr>
                                                    <td class="ps-3 fw-bold">{{ preg_replace('/^\s*TAG\s*[-_: ]*\s*/i', '', $child->name) }}</td>
                                                    <td><span class="badge bg-light text-dark border-0 fw-normal">{{ $child->code }}</span></td>
                                                    <td class="text-center">
                                                        @if($child->is_active)
                                                            <span class="text-success extra-small fw-bold">Active</span>
                                                        @else
                                                            <span class="text-muted extra-small fw-bold">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end text-muted">{{ $child->monthly_budget > 0 ? '$' . number_format($child->monthly_budget, 2) : '-' }}</td>
                                                    <td class="text-end fw-bold {{ $child->total_spent < 0 ? 'text-dark' : 'text-success' }}">
                                                        {{ $child->total_spent ? '$' . number_format(abs($child->total_spent), 2) : '-' }}
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('categories.edit', $child) }}" class="btn btn-link link-secondary p-0 btn-sm">
                                                            <i class="fa-solid fa-pen extra-small"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4 text-muted">No sub-categories in this group.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    @if($categories->isEmpty())
        <div class="card border-0 shadow-sm p-5 text-center my-4">
            <i class="fa-solid fa-tag fs-1 text-muted opacity-25 mb-3"></i>
            <h5 class="fw-bold">No categories yet</h5>
            <p class="text-muted small mb-4">Get started by creating your first category.</p>
            <div>
                <a href="{{ route('categories.create') }}" class="btn btn-primary d-flex align-items-center gap-2 px-4 fw-bold">
                    <i class="fa-solid fa-plus"></i> Create Category
                </a>
            </div>
        </div>
    @endif

    <script>
        const parentCategories = @json($parentCategories);

        // Show success toast for newly created category
        @if(session('created_category_id'))
            window.toast('Category "{{ session('created_category_name') }}" created successfully.', 'success', 5000);
        @endif
    </script>
@endsection