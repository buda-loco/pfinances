<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get account balances
        $accounts = Account::where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        // Calculate total balance by currency
        $totalsByCurrency = $accounts->groupBy('currency')->map(function ($accts) {
            return $accts->sum('current_balance');
        });

        // Get recent transactions
        $recentTransactions = Transaction::with('account')
            ->whereIn('account_id', $accounts->pluck('id'))
            ->orderBy('transaction_date', 'desc')
            ->take(10)
            ->get();

        // Monthly spending (last 6 months)
        $monthlySpending = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('transaction_date', '>=', now()->subMonths(6))
            ->where('amount', '<', 0)
            ->select(
                DB::raw("strftime('%Y-%m', transaction_date) as month"),
                DB::raw('SUM(ABS(amount)) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // Transaction count
        $transactionCount = Transaction::whereIn('account_id', $accounts->pluck('id'))->count();

        // Spending by category (last 30 days)
        $categorySpending = Transaction::with('category')
            ->whereIn('account_id', $accounts->pluck('id'))
            ->where('transaction_date', '>=', now()->subDays(90))
            ->where('amount', '<', 0)
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('SUM(ABS(amount)) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Prepare chart data
        $chartData = [
            'labels' => $monthlySpending->pluck('month')->map(function ($m) {
                return \Carbon\Carbon::parse($m)->format('M');
            })->values()->toArray(),
            'data' => $monthlySpending->pluck('total')->map(function ($t) {
                return (float) $t;
            })->values()->toArray()
        ];

        return view('dashboard', compact(
            'accounts',
            'totalsByCurrency',
            'recentTransactions',
            'monthlySpending',
            'transactionCount',
            'categorySpending',
            'chartData'
        ))->with('page', 'dashboard');
    }
}
