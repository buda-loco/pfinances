<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Build query - CRITICAL: Filter by user_id to prevent data leakage
        $query = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('is_active', true);

        // Filter by period type
        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $budgets = $query->get();

        // Calculate spent for each budget
        $budgetsWithSpent = $budgets->map(function ($budget) {
            $spent = Transaction::where('category_id', $budget->category_id)
                ->whereBetween('transaction_date', [$budget->period_start, $budget->period_end])
                ->where('amount', '<', 0) // Only expenses
                ->sum('amount');

            $budget->spent = abs($spent);
            $budget->remaining = $budget->amount - $budget->spent;
            $budget->percentage = $budget->amount > 0 ? min(100, ($budget->spent / $budget->amount) * 100) : 0;

            return $budget;
        });

        // Stats
        $totalBudgets = $budgets->count();
        $onTrack = $budgetsWithSpent->filter(fn($b) => $b->percentage < 80)->count();
        $overBudget = $budgetsWithSpent->filter(fn($b) => $b->percentage >= 100)->count();
        $totalBudgeted = $budgets->sum('amount');
        $totalSpent = $budgetsWithSpent->sum('spent');

        return view('budgets.index', [
            'budgets' => $budgetsWithSpent,
            'totalBudgets' => $totalBudgets,
            'onTrack' => $onTrack,
            'overBudget' => $overBudget,
            'totalBudgeted' => $totalBudgeted,
            'totalSpent' => $totalSpent,
        ])->with('page', 'budgets');
    }

    public function create()
    {
        $categories = Category::where('is_active', true)
            ->where('category_type', 'expense')
            ->orderBy('name')
            ->get();

        return view('budgets.create', compact('categories'))
            ->with('page', 'budgets');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'period_type' => 'required|in:daily,weekly,monthly,yearly',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_active'] = true;

        $budget = Budget::create($validated);
        $budget->load('category');

        return redirect()->route('budgets.index')
            ->with('success', 'Budget created successfully.')
            ->with('created_budget_id', $budget->id)
            ->with('created_budget_name', $budget->category->name);
    }

    public function edit(Budget $budget)
    {
        // Authorization: Verify budget belongs to authenticated user
        if ($budget->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to budget');
        }

        $categories = Category::where('is_active', true)
            ->where('category_type', 'expense')
            ->orderBy('name')
            ->get();

        // Calculate spent
        $spent = Transaction::where('category_id', $budget->category_id)
            ->whereBetween('transaction_date', [$budget->period_start, $budget->period_end])
            ->where('amount', '<', 0)
            ->sum('amount');
        $budget->spent = abs($spent);

        return view('budgets.edit', compact('budget', 'categories'))
            ->with('page', 'budgets');
    }

    public function update(Request $request, Budget $budget)
    {
        // Authorization: Verify budget belongs to authenticated user
        if ($budget->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to budget');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'period_type' => 'required|in:daily,weekly,monthly,yearly',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Fix: Use boolean() helper instead of has()
        $validated['is_active'] = $request->boolean('is_active');

        $budget->update($validated);

        return redirect()->route('budgets.index')
            ->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget)
    {
        // Authorization: Verify budget belongs to authenticated user
        if ($budget->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to budget');
        }

        $budget->delete();

        return redirect()->route('budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }
}
