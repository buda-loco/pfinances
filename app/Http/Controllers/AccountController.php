<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\EntityType;
use Illuminate\Http\Request;

class AccountController extends Controller
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
        $user = auth()->user();

        // 1. Existing Logic for Cards (Grouped by Type) - fetch all active for summary
        $allAccounts = Account::where('user_id', $user->id)
            ->with('entityType')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        $bankAccounts = $allAccounts->where('account_type', 'bank');
        $travelMoney = $allAccounts->where('account_type', 'travel_money');
        $cash = $allAccounts->where('account_type', 'cash');
        $other = $allAccounts->whereNotIn('account_type', ['bank', 'travel_money', 'cash']);

        $totalsByCurrency = $allAccounts->where('is_active', true)
            ->groupBy('currency')
            ->map(fn($accts) => $accts->sum('current_balance'));

        // 2. New Logic for Detailed Table (Search, Sort, Paginate)
        $query = Account::where('user_id', $user->id)->with('entityType');

        // Search - sanitize to prevent SQL injection
        if ($request->filled('search')) {
            $searchTerm = $this->escapeLike($request->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('institution', 'like', "%{$searchTerm}%")
                    ->orWhere('currency', 'like', "%{$searchTerm}%")
                    ->orWhere('account_number', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('account_type', $request->type);
        }

        // Filter by Ownership
        if ($request->filled('ownership')) {
            $query->where('ownership', $request->ownership);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'is_active');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['name', 'account_type', 'institution', 'currency', 'current_balance', 'ownership', 'is_active'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('is_active', 'desc')->orderBy('name');
        }

        $tableAccounts = $query->paginate(20)->withQueryString();

        return view('accounts.index', compact(
            'allAccounts', // Renamed from 'accounts' to avoid confusion, but kept likely for view compatibility if needed? No, view uses specific groups.
            'bankAccounts',
            'travelMoney',
            'cash',
            'other',
            'totalsByCurrency',
            'tableAccounts' // New paginated list
        ))->with('page', 'accounts');
    }

    public function create()
    {
        $entityTypes = EntityType::all();

        return view('accounts.create', compact('entityTypes'))
            ->with('page', 'accounts');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_type' => 'required|in:bank,credit_card,savings,investment,travel_money,cash,other',
            'ownership' => 'required|in:buda,gupi,shared',
            'institution' => 'nullable|string|max:255',
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'opening_balance' => 'required|numeric|min:-999999999.99|max:999999999.99',
            'current_balance' => 'required|numeric|min:-999999999.99|max:999999999.99',
            'account_number' => 'nullable|string|max:255',
            'entity_type_id' => 'nullable|exists:entity_types,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_active'] = true;

        $account = Account::create($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Account created successfully.')
            ->with('created_account_id', $account->id)
            ->with('created_account_name', $account->name);
    }

    public function edit(Account $account)
    {
        // Ensure user owns this account
        if ($account->user_id !== auth()->id()) {
            abort(403);
        }

        $entityTypes = EntityType::all();

        return view('accounts.edit', compact('account', 'entityTypes'))
            ->with('page', 'accounts');
    }

    public function update(Request $request, Account $account)
    {
        // Ensure user owns this account
        if ($account->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_type' => 'required|in:bank,credit_card,savings,investment,travel_money,cash,other',
            'ownership' => 'required|in:buda,gupi,shared',
            'institution' => 'nullable|string|max:255',
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'current_balance' => 'required|numeric|min:-999999999.99|max:999999999.99',
            'account_number' => 'nullable|string|max:255',
            'entity_type_id' => 'nullable|exists:entity_types,id',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $account->update($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Account $account)
    {
        // Ensure user owns this account
        if ($account->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if account has transactions
        if ($account->transactions()->count() > 0) {
            return back()->with('error', 'Cannot delete account with transactions. Archive it instead.');
        }

        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Account deleted successfully.');
    }
}
