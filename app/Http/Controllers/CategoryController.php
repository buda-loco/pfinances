<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Escape special LIKE wildcards to prevent SQL injection
     */
    private function escapeLike($value)
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    public function index(Request $request)
    {
        $query = Category::with('group')
            ->withSum('transactions as total_spent', 'amount');

        // Search filter - sanitize to prevent SQL injection
        if ($request->filled('search')) {
            $searchTerm = $this->escapeLike($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('code', 'like', "%{$searchTerm}%")
                    ->orWhere('keywords', 'like', "%{$searchTerm}%");
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('category_type', $request->type);
        }

        // Ordering
        $sortBy = $request->get('sort_by', 'is_active'); // Default sort
        $sortOrder = $request->get('sort_order', 'desc');

        // Allow sorting by specific allowed columns
        if (in_array($sortBy, ['name', 'category_type', 'is_active', 'order', 'monthly_budget', 'parent_id', 'total_spent'])) {
            if ($sortBy === 'parent_id') {
                $query->select('categories.*')
                    ->leftJoin('categories as parents', 'categories.parent_id', '=', 'parents.id')
                    ->orderBy('parents.name', $sortOrder)
                    ->orderBy('categories.name', 'asc');

            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            // Default Sort: Tree-ish structure? Or just alphabetical?
            // User requested "organise by parent".
            // Let's stick to the previous default, but maybe "parent_id, name" is better?
            // Existing default:
            $query->orderBy('is_active', 'desc')
                ->orderBy('order')
                ->orderBy('name');
        }

        // Clone query for stats to avoid executing it twice
        $statsQuery = clone $query;
        $allCategories = $statsQuery->get();

        // Stats
        $totalCategories = $allCategories->count();
        $activeCategories = $allCategories->where('is_active', true)->count();
        $expenseCount = $allCategories->where('category_type', 'expense')->count();

        // Get paginated results from original query
        $categories = $query->paginate(20)->withQueryString();

        $parentCategories = Category::whereNull('parent_id')
            ->with([
                'children' => function ($q) {
                    $q->withSum('transactions as total_spent', 'amount');
                }
            ])
            ->orderBy('name')
            ->get();

        return view('categories.index', compact(
            'categories',
            'totalCategories',
            'activeCategories',
            'expenseCount',
            'parentCategories'
        ))->with('page', 'categories');
    }

    public function create()
    {
        $categoryGroups = CategoryGroup::where('is_active', true)->get();
        $parentCategories = Category::whereNull('parent_id')->where('is_active', true)->get();

        return view('categories.create', compact('categoryGroups', 'parentCategories'))
            ->with('page', 'categories');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:categories,code',
            'category_type' => 'required|in:income,expense,transfer',
            'group_id' => 'nullable|exists:category_groups,id',
            'parent_id' => 'nullable|exists:categories,id',
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => 'nullable|string|max:10',
            'daily_budget' => 'nullable|numeric|min:0|max:999999999.99',
            'weekly_budget' => 'nullable|numeric|min:0|max:999999999.99',
            'monthly_budget' => 'nullable|numeric|min:0|max:999999999.99',
            'keywords' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0|max:9999',
        ]);

        $validated['is_active'] = true;

        $category = Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.')
            ->with('created_category_id', $category->id)
            ->with('created_category_name', $category->name);
    }

    public function edit(Category $category)
    {
        $categoryGroups = CategoryGroup::where('is_active', true)->get();
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->where('id', '!=', $category->id)
            ->get();

        return view('categories.edit', compact('category', 'categoryGroups', 'parentCategories'))
            ->with('page', 'categories');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:categories,code,' . $category->id,
            'category_type' => 'required|in:income,expense,transfer',
            'group_id' => 'nullable|exists:category_groups,id',
            'parent_id' => 'nullable|exists:categories,id',
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => 'nullable|string|max:10',
            'daily_budget' => 'nullable|numeric|min:0|max:999999999.99',
            'weekly_budget' => 'nullable|numeric|min:0|max:999999999.99',
            'monthly_budget' => 'nullable|numeric|min:0|max:999999999.99',
            'keywords' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:0|max:9999',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => ['category' => $category->load('parent')]
            ]);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        // Check if category has transactions
        if ($category->transactions()->count() > 0) {
            return back()->with('error', 'Cannot delete category with transactions. Deactivate it instead.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
