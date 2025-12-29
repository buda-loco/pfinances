<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
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
        $accounts = Account::where('user_id', $user->id)->where('is_active', true)->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        // Build query
        $query = Transaction::with(['account', 'category', 'project'])
            ->whereIn('account_id', $accounts->pluck('id'));

        // Filter by uncategorized (legacy parameter)
        if ($request->has('uncategorized') && $request->uncategorized == '1') {
            $query->whereNull('category_id');
        }

        // Filter by category
        if ($request->filled('category_id')) {
            if ($request->category_id === 'uncategorized') {
                // Show only uncategorized transactions
                $query->whereNull('category_id');
            } else {
                // Show transactions with specific category
                $query->where('category_id', $request->category_id);
            }
        }

        // Filter by account
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by Date Parts (Month/Year)
        if ($request->filled('month')) {
            $query->whereMonth('transaction_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('transaction_date', $request->year);
        }

        // Filter by date range (legacy but kept)
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        // Search by description - sanitize to prevent SQL injection
        if ($request->filled('search')) {
            $searchTerm = $this->escapeLike($request->search);
            $query->where('description', 'like', '%' . $searchTerm . '%');
        }

        // Sort
        $sortBy = $request->get('sort_by', 'transaction_date');
        $sortOrder = $request->get('sort_order', 'desc');

        if ($sortBy === 'account.name') {
            $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                ->orderBy('accounts.name', $sortOrder)
                ->select('transactions.*'); // Avoid column collision
        } elseif ($sortBy === 'category.name') {
            $query->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
                ->orderBy('categories.name', $sortOrder)
                ->select('transactions.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginate
        $transactions = $query->paginate(15)->withQueryString();

        // Get filter options - database agnostic
        $years = Transaction::selectRaw(
            config('database.default') === 'sqlite'
                ? "strftime('%Y', transaction_date) as year"
                : "YEAR(transaction_date) as year"
        )
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Get stats
        $totalTransactions = Transaction::whereIn('account_id', $accounts->pluck('id'))->count();
        $uncategorizedCount = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->whereNull('category_id')
            ->count();
        $categorizedCount = $totalTransactions - $uncategorizedCount;

        return view('transactions.index', compact(
            'transactions',
            'accounts',
            'categories',
            'projects',
            'years',
            'totalTransactions',
            'uncategorizedCount',
            'categorizedCount'
        ))->with('page', 'transactions');
    }

    public function update(Request $request, Transaction $transaction)
    {
        // Ensure user owns this transaction via account ownership
        if ($transaction->account->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'account_id' => 'nullable|exists:accounts,id',
            'amount' => 'nullable|numeric',
            'date' => 'nullable|date',
            'description' => 'nullable|string|max:255',
            'user_description' => 'nullable|string|max:500',
            'notes_and_codes' => 'nullable|string|max:1000',
        ]);

        $updateData = [];

        if ($request->has('category_id')) {
            $updateData['category_id'] = $request->category_id;
            $updateData['code'] = $request->category_id ? Category::find($request->category_id)->code : null;
        }

        if ($request->has('account_id')) {
            $updateData['account_id'] = $request->account_id;
        }

        if ($request->has('project_id')) {
            $updateData['project_id'] = $request->project_id ?: null;
        }

        if ($request->has('amount')) {
            $updateData['amount'] = $request->amount;
            // Update income_outcome based on amount
            $updateData['income_outcome'] = $request->amount >= 0 ? 'income' : 'expense';
        }

        if ($request->has('date')) {
            $updateData['transaction_date'] = $request->date;
        }

        if ($request->has('description')) {
            $updateData['description'] = $request->description;
        }

        if ($request->has('user_description')) {
            $updateData['user_description'] = $request->user_description;
        }

        if ($request->has('notes_and_codes')) {
            $updateData['notes_and_codes'] = $request->notes_and_codes;
        }

        $transaction->update($updateData);

        // Check if this is an AJAX request
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully.',
                'data' => ['transaction' => $transaction->load(['category', 'account'])]
            ]);
        }

        // Traditional form submission
        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'exists:transactions,id',
            'category_id' => 'nullable|exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        $updateData = [];

        if ($request->filled('category_id')) {
            $category = Category::find($request->category_id);
            $updateData['category_id'] = $category->id;
            $updateData['code'] = $category->code;
        }

        if ($request->filled('project_id')) {
            $updateData['project_id'] = $request->project_id;
        }

        if ($request->filled('account_id')) {
            $updateData['account_id'] = $request->account_id;
        }

        if (!empty($updateData)) {
            Transaction::whereIn('id', $request->transaction_ids)->update($updateData);
        }

        return response()->json([
            'success' => true,
            'message' => count($request->transaction_ids) . ' transactions updated successfully.',
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $accounts = Account::where('user_id', $user->id)->where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return view('transactions.create', compact('accounts', 'categories', 'projects'))
            ->with('page', 'transactions');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'account_id' => 'required|exists:accounts,id',
            'category_id' => 'nullable|exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'user_description' => 'nullable|string|max:500',
        ]);

        $transactionData = [
            'user_id' => auth()->id(),
            'transaction_date' => $validated['date'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'account_id' => $validated['account_id'],
            'category_id' => $validated['category_id'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
            'user_description' => $validated['user_description'] ?? null,
        ];

        if ($validated['category_id']) {
            $category = Category::find($validated['category_id']);
            $transactionData['code'] = $category->code;
        }

        // Determine income_outcome based on amount
        $transactionData['income_outcome'] = $validated['amount'] >= 0 ? 'income' : 'expense';

        // Set default currency from account
        $account = Account::find($validated['account_id']);
        $transactionData['currency'] = $account->currency;

        $transaction = Transaction::create($transactionData);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction created successfully.')
            ->with('created_transaction_id', $transaction->id);
    }

    public function edit(Transaction $transaction)
    {
        // Ensure user owns this transaction via account ownership
        if ($transaction->account->user_id !== auth()->id()) {
            abort(403);
        }

        $user = auth()->user();
        $accounts = Account::where('user_id', $user->id)->where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return view('transactions.edit', compact('transaction', 'accounts', 'categories', 'projects'))
            ->with('page', 'transactions');
    }

    public function destroy(Transaction $transaction)
    {
        // Ensure user owns this transaction via account ownership
        if ($transaction->account->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    public function suggestions(Transaction $transaction)
    {
        // Find similar transactions that are already categorized
        $suggestions = Transaction::where('description', 'like', '%' . substr($transaction->description, 0, 10) . '%')
            ->whereNotNull('category_id')
            ->with('category')
            ->select('category_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->get();

        return response()->json($suggestions);
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $accounts = Account::where('user_id', $user->id)->where('is_active', true)->get();

        // Build query - same logic as index method
        $query = Transaction::with(['account', 'category', 'project'])
            ->whereIn('account_id', $accounts->pluck('id'))
            ->whereNotNull('category_id'); // Only export categorized transactions

        // Apply same filters as index page
        if ($request->filled('category_id')) {
            if ($request->category_id !== 'uncategorized') {
                $query->where('category_id', $request->category_id);
            }
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('month')) {
            $query->whereMonth('transaction_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('transaction_date', $request->year);
        }

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $searchTerm = $this->escapeLike($request->search);
            $query->where('description', 'like', '%' . $searchTerm . '%');
        }

        // Get all matching transactions (no pagination for export)
        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        // Generate CSV
        $filename = 'transactions_categorized_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Date',
                'Description',
                'User Description',
                'Amount',
                'Account',
                'Category',
                'Category Code',
                'Project',
                'Notes and Codes'
            ]);

            // Add data rows
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_date,
                    $transaction->description,
                    $transaction->user_description,
                    $transaction->amount,
                    $transaction->account->name ?? '',
                    $transaction->category->name ?? '',
                    $transaction->code ?? '',
                    $transaction->project->name ?? '',
                    $transaction->notes_and_codes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
