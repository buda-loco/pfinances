<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryGroup;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with('group')
            ->withSum('transactions as total_spent', 'amount');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%");
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

        // Get all filtered records for Stats (active/expense/total counts depend on filters? User desire: likely yes)
        // Check if we need a separate query for stats without pagination
        $allCategories = $query->get();

        // Stats
        $totalCategories = $allCategories->count();
        $activeCategories = $allCategories->where('is_active', true)->count();
        $expenseCount = $allCategories->where('category_type', 'expense')->count();

        // Get paginated results
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
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'daily_budget' => 'nullable|numeric|min:0',
            'weekly_budget' => 'nullable|numeric|min:0',
            'monthly_budget' => 'nullable|numeric|min:0',
            'keywords' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $validated['is_active'] = true;

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully!');
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
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:255',
            'daily_budget' => 'nullable|numeric|min:0',
            'weekly_budget' => 'nullable|numeric|min:0',
            'monthly_budget' => 'nullable|numeric|min:0',
            'keywords' => 'nullable|string',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'category' => $category->load('parent')
            ]);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        // Check if category has transactions
        if ($category->transactions()->count() > 0) {
            return back()->with('error', 'Cannot delete category with transactions. Deactivate it instead.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}
