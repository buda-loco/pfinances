<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'results' => [],
                'count' => 0
            ]);
        }

        $userId = Auth::id();
        $results = [];

        // Get user's account IDs once for reuse
        $userAccountIds = Account::where('user_id', $userId)->pluck('id');

        // Search Accounts
        $accounts = Account::where('user_id', $userId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('account_number', 'like', "%{$query}%")
                    ->orWhere('institution', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($accounts as $account) {
            $results[] = [
                'id' => $account->id,
                'type' => 'account',
                'icon' => 'fa-wallet',
                'color' => 'primary',
                'title' => $account->name,
                'subtitle' => $account->institution ?? 'No institution',
                'meta' => $account->currency . ' ' . number_format($account->current_balance, 2),
                'url' => route('accounts.index', ['account_id' => $account->id]),
                'badge' => ucfirst(str_replace('_', ' ', $account->account_type)),
            ];
        }

        // Search Transactions (filter by user's accounts)
        $transactions = $userAccountIds->isNotEmpty()
            ? Transaction::whereIn('account_id', $userAccountIds)
            ->where(function ($q) use ($query) {
                $q->where('description', 'like', "%{$query}%")
                    ->orWhere('user_description', 'like', "%{$query}%")
                    ->orWhere('notes_and_codes', 'like', "%{$query}%")
                    ->orWhere('merchant_name', 'like', "%{$query}%");
            })
            ->with(['account', 'category'])
            ->orderBy('transaction_date', 'desc')
            ->limit(10)
            ->get()
            : collect();

        foreach ($transactions as $transaction) {
            $results[] = [
                'id' => $transaction->id,
                'type' => 'transaction',
                'icon' => 'fa-receipt',
                'color' => $transaction->amount >= 0 ? 'success' : 'danger',
                'title' => \Illuminate\Support\Str::limit($transaction->description, 50),
                'subtitle' => $transaction->account->name ?? 'Unknown Account',
                'meta' => $transaction->currency . ' ' . number_format($transaction->amount, 2),
                'url' => route('transactions.index', ['transaction_id' => $transaction->id]),
                'badge' => $transaction->category->name ?? 'Uncategorized',
                'date' => $transaction->transaction_date->format('M d, Y'),
            ];
        }

        // Search Projects
        $projects = Project::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($projects as $project) {
            $results[] = [
                'id' => $project->id,
                'type' => 'project',
                'icon' => 'fa-rocket',
                'color' => 'info',
                'title' => $project->name,
                'subtitle' => $project->description ?? 'No description',
                'meta' => 'Budget: ' . number_format($project->budget ?? 0, 2),
                'url' => route('projects.index', ['project_id' => $project->id]),
                'badge' => ucfirst($project->status ?? 'active'),
            ];
        }

        // Search Categories
        $categories = Category::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($categories as $category) {
            $results[] = [
                'id' => $category->id,
                'type' => 'category',
                'icon' => 'fa-tag',
                'color' => 'warning',
                'title' => $category->name,
                'subtitle' => $category->parent ? 'Parent: ' . $category->parent->name : 'Top Level',
                'meta' => ucfirst($category->category_type ?? 'general'),
                'url' => route('categories.index', ['category_id' => $category->id]),
                'badge' => $category->code ?? 'No code',
            ];
        }

        // Search Budgets
        $budgets = Budget::where('user_id', $userId)
            ->where(function ($q) use ($query) {
                $q->whereHas('category', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                })
                ->orWhereHas('account', function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%");
                });
            })
            ->with(['category', 'account'])
            ->limit(5)
            ->get();

        foreach ($budgets as $budget) {
            $results[] = [
                'id' => $budget->id,
                'type' => 'budget',
                'icon' => 'fa-bullseye',
                'color' => 'secondary',
                'title' => ($budget->category->name ?? 'General') . ' Budget',
                'subtitle' => $budget->account->name ?? 'All Accounts',
                'meta' => $budget->currency . ' ' . number_format($budget->amount, 2),
                'url' => route('budgets.index', ['budget_id' => $budget->id]),
                'badge' => ucfirst($budget->period_type ?? 'monthly'),
            ];
        }

        return response()->json([
            'results' => $results,
            'count' => count($results)
        ]);
    }

    public function details(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');
        $userId = Auth::id();

        $details = [
            'title' => 'Not Found',
            'type_label' => 'Unknown',
            'icon' => 'fa-question',
            'color' => 'secondary',
            'info' => [],
            'related' => [],
            'url' => '#'
        ];

        switch ($type) {
            case 'account':
                $account = Account::where('user_id', $userId)->find($id);
                if ($account) {
                    $recentTransactions = Transaction::where('account_id', $account->id)
                        ->orderBy('transaction_date', 'desc')
                        ->limit(5)
                        ->get();

                    $details = [
                        'title' => $account->name,
                        'type_label' => 'Account',
                        'icon' => 'fa-wallet',
                        'color' => 'primary',
                        'info' => [
                            'Type' => ucfirst(str_replace('_', ' ', $account->account_type)),
                            'Institution' => $account->institution ?? 'N/A',
                            'Account Number' => $account->account_number ?? 'N/A',
                            'Currency' => $account->currency,
                            'Current Balance' => '<span class="fw-bold fs-5">' . number_format($account->current_balance, 2) . '</span>',
                            'Status' => $account->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>',
                            'Ownership' => ucfirst($account->ownership ?? 'N/A'),
                        ],
                        'related' => $recentTransactions->map(function ($t) {
                            return [
                                'id' => $t->id,
                                'title' => \Illuminate\Support\Str::limit($t->description, 40),
                                'subtitle' => $t->category->name ?? 'Uncategorized',
                                'amount' => $t->currency . ' ' . number_format($t->amount, 2),
                                'date' => $t->transaction_date->format('M d, Y'),
                            ];
                        })->toArray(),
                        'related_label' => 'Recent Transactions',
                        'url' => route('accounts.edit', $account),
                    ];
                }
                break;

            case 'transaction':
                $userAccountIds = Account::where('user_id', $userId)->pluck('id');
                $transaction = Transaction::whereIn('account_id', $userAccountIds)->with(['account', 'category', 'project'])->find($id);

                if ($transaction) {
                    $categories = Category::where('is_active', true)->orderBy('name')->get();
                    $accounts = Account::where('user_id', $userId)->where('is_active', true)->get();

                    $details = [
                        'title' => $transaction->description,
                        'type_label' => 'Transaction',
                        'icon' => 'fa-receipt',
                        'color' => $transaction->amount >= 0 ? 'success' : 'danger',
                        'editable' => true,
                        'info' => [
                            'Date' => $transaction->transaction_date->format('F d, Y'),
                            'Amount' => '<span class="fw-bold fs-5 ' . ($transaction->amount >= 0 ? 'text-success' : 'text-danger') . '">' .
                                        $transaction->currency . ' ' . number_format($transaction->amount, 2) . '</span>',
                            'Account' => $transaction->account->name ?? 'Unknown',
                            'Category' => $transaction->category ?
                                         '<span class="badge bg-light text-dark border">' . $transaction->category->name . '</span>' :
                                         '<span class="badge bg-warning">Uncategorized</span>',
                            'Merchant' => $transaction->merchant_name ?? 'N/A',
                            'Notes' => $transaction->notes_and_codes ?? 'N/A',
                            'Type' => ucfirst($transaction->transaction_type ?? 'standard'),
                        ],
                        'edit_fields' => [
                            ['name' => 'transaction_date', 'label' => 'Date', 'type' => 'date', 'value' => $transaction->transaction_date->format('Y-m-d'), 'required' => true],
                            ['name' => 'description', 'label' => 'Description', 'type' => 'text', 'value' => $transaction->description, 'required' => true, 'full_width' => true],
                            ['name' => 'account_id', 'label' => 'Account', 'type' => 'select', 'value' => $transaction->account_id, 'required' => true,
                             'options' => $accounts->map(fn($a) => ['value' => $a->id, 'label' => $a->name])->toArray()],
                            ['name' => 'category_id', 'label' => 'Category', 'type' => 'select', 'value' => $transaction->category_id, 'required' => false,
                             'options' => $categories->map(fn($c) => ['value' => $c->id, 'label' => $c->name])->toArray()],
                            ['name' => 'merchant_name', 'label' => 'Merchant', 'type' => 'text', 'value' => $transaction->merchant_name, 'required' => false],
                            ['name' => 'notes_and_codes', 'label' => 'Notes', 'type' => 'textarea', 'value' => $transaction->notes_and_codes, 'required' => false, 'full_width' => true],
                        ],
                        'related' => [],
                        'url' => route('transactions.index', ['transaction_id' => $transaction->id]),
                    ];

                    if ($transaction->project) {
                        $details['info']['Project'] = '<span class="badge bg-info">' . $transaction->project->name . '</span>';
                    }
                }
                break;

            case 'project':
                $project = Project::find($id);
                if ($project) {
                    $transactions = $project->transactions()->orderBy('transaction_date', 'desc')->limit(5)->get();
                    $totalSpent = $project->totalSpent();
                    $totalIncome = $project->totalIncome();

                    $details = [
                        'title' => $project->name,
                        'type_label' => 'Project',
                        'icon' => 'fa-rocket',
                        'color' => 'info',
                        'info' => [
                            'Code' => $project->code ?? 'N/A',
                            'Status' => '<span class="badge bg-' . ($project->status === 'active' ? 'success' : 'secondary') . '">' . ucfirst($project->status ?? 'active') . '</span>',
                            'Budget' => number_format($project->budget ?? 0, 2),
                            'Total Spent' => '<span class="text-danger fw-bold">' . number_format(abs($totalSpent), 2) . '</span>',
                            'Total Income' => '<span class="text-success fw-bold">' . number_format($totalIncome, 2) . '</span>',
                            'Start Date' => $project->start_date ? $project->start_date->format('M d, Y') : 'N/A',
                            'End Date' => $project->end_date ? $project->end_date->format('M d, Y') : 'N/A',
                            'Description' => $project->description ?? 'No description',
                        ],
                        'related' => $transactions->map(function ($t) {
                            return [
                                'id' => $t->id,
                                'title' => \Illuminate\Support\Str::limit($t->description, 40),
                                'subtitle' => $t->category->name ?? 'Uncategorized',
                                'amount' => $t->currency . ' ' . number_format($t->amount, 2),
                                'date' => $t->transaction_date->format('M d, Y'),
                            ];
                        })->toArray(),
                        'related_label' => 'Recent Transactions',
                        'url' => route('projects.show', $project),
                    ];
                }
                break;

            case 'category':
                $category = Category::with('parent')->find($id);
                if ($category) {
                    $transactionCount = Transaction::where('category_id', $category->id)->count();

                    $details = [
                        'title' => $category->name,
                        'type_label' => 'Category',
                        'icon' => 'fa-tag',
                        'color' => 'warning',
                        'info' => [
                            'Code' => $category->code ?? 'N/A',
                            'Type' => '<span class="badge bg-light text-dark border">' . ucfirst($category->category_type ?? 'general') . '</span>',
                            'Parent Category' => $category->parent ? $category->parent->name : 'None (Top Level)',
                            'Transaction Count' => $transactionCount . ' transactions',
                            'Status' => $category->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>',
                        ],
                        'related' => [],
                        'url' => route('categories.index', ['category_id' => $category->id]),
                    ];
                }
                break;

            case 'budget':
                $budget = Budget::where('user_id', $userId)->with(['category', 'account'])->find($id);
                if ($budget) {
                    $details = [
                        'title' => ($budget->category->name ?? 'General') . ' Budget',
                        'type_label' => 'Budget',
                        'icon' => 'fa-bullseye',
                        'color' => 'secondary',
                        'info' => [
                            'Amount' => '<span class="fw-bold fs-5">' . $budget->currency . ' ' . number_format($budget->amount, 2) . '</span>',
                            'Category' => $budget->category ? $budget->category->name : 'All Categories',
                            'Account' => $budget->account ? $budget->account->name : 'All Accounts',
                            'Period Type' => ucfirst($budget->period_type ?? 'monthly'),
                            'Period Start' => $budget->period_start ? $budget->period_start->format('M d, Y') : 'N/A',
                            'Period End' => $budget->period_end ? $budget->period_end->format('M d, Y') : 'N/A',
                            'Status' => $budget->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>',
                            'Notes' => $budget->notes ?? 'No notes',
                        ],
                        'related' => [],
                        'url' => route('budgets.index', ['budget_id' => $budget->id]),
                    ];
                }
                break;
        }

        return response()->json($details);
    }

    public function update(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');
        $data = $request->input('data');
        $userId = Auth::id();

        try {
            switch ($type) {
                case 'transaction':
                    $userAccountIds = Account::where('user_id', $userId)->pluck('id');
                    $transaction = Transaction::whereIn('account_id', $userAccountIds)->find($id);

                    if (!$transaction) {
                        return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
                    }

                    $transaction->update([
                        'transaction_date' => $data['transaction_date'],
                        'description' => $data['description'],
                        'account_id' => $data['account_id'],
                        'category_id' => $data['category_id'] ?: null,
                        'merchant_name' => $data['merchant_name'],
                        'notes_and_codes' => $data['notes_and_codes'],
                    ]);

                    return response()->json(['success' => true, 'message' => 'Transaction updated successfully']);

                case 'account':
                    $account = Account::where('user_id', $userId)->find($id);

                    if (!$account) {
                        return response()->json(['success' => false, 'message' => 'Account not found'], 404);
                    }

                    $account->update($data);

                    return response()->json(['success' => true, 'message' => 'Account updated successfully']);

                case 'project':
                    $project = Project::find($id);

                    if (!$project) {
                        return response()->json(['success' => false, 'message' => 'Project not found'], 404);
                    }

                    $project->update($data);

                    return response()->json(['success' => true, 'message' => 'Project updated successfully']);

                case 'category':
                    $category = Category::find($id);

                    if (!$category) {
                        return response()->json(['success' => false, 'message' => 'Category not found'], 404);
                    }

                    $category->update($data);

                    return response()->json(['success' => true, 'message' => 'Category updated successfully']);

                case 'budget':
                    $budget = Budget::where('user_id', $userId)->find($id);

                    if (!$budget) {
                        return response()->json(['success' => false, 'message' => 'Budget not found'], 404);
                    }

                    $budget->update($data);

                    return response()->json(['success' => true, 'message' => 'Budget updated successfully']);

                default:
                    return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update: ' . $e->getMessage()], 500);
        }
    }
}
