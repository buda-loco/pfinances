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

        // Monthly spending (last 6 months) - database agnostic
        $monthlySpending = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('transaction_date', '>=', now()->subMonths(6))
            ->where('amount', '<', 0)
            ->select(
                DB::raw(config('database.default') === 'sqlite'
                    ? "strftime('%Y-%m', transaction_date) as month"
                    : "DATE_FORMAT(transaction_date, '%Y-%m') as month"
                ),
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

        // ========== NEW FINANCIAL INSIGHTS ==========

        // Get date range for calculations
        $firstTransaction = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->orderBy('transaction_date', 'asc')
            ->first();

        $lastTransaction = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->orderBy('transaction_date', 'desc')
            ->first();

        $totalDays = $firstTransaction && $lastTransaction
            ? max(1, $firstTransaction->transaction_date->diffInDays($lastTransaction->transaction_date) + 1)
            : 1;

        // Calculate ALL-TIME totals
        $totalIncome = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('amount', '>', 0)
            ->sum('amount');

        $totalExpenses = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('amount', '<', 0)
            ->sum('amount');

        // Calculate CURRENT metrics (last 30 days) for accurate monthly view
        $monthlyIncome = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('amount', '>', 0)
            ->where('transaction_date', '>=', now()->subDays(30))
            ->sum('amount');

        $monthlyExpenses = abs(Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('amount', '<', 0)
            ->where('transaction_date', '>=', now()->subDays(30))
            ->sum('amount'));

        // Calculate AVERAGE daily income from all-time data for projections
        $dailyIncome = $totalDays > 0 ? $totalIncome / $totalDays : 0;

        // Yearly projection based on historical daily average
        $yearlyIncome = $dailyIncome * 365;

        // Daily expenses from all-time average
        $dailyExpenses = $totalDays > 0 ? abs($totalExpenses) / $totalDays : 0;

        // Savings calculations (based on last 30 days actual)
        $monthlySavings = $monthlyIncome - $monthlyExpenses;
        $savingsRate = $monthlyIncome > 0 ? ($monthlySavings / $monthlyIncome) * 100 : 0;

        // Daily balance (from averages)
        $dailyBalance = $dailyIncome - $dailyExpenses;

        // Hourly rate calculation (based on ACTUAL last 30 days income)
        $workingDaysPerMonth = 22;
        $hoursPerDay = 8;
        $monthlyWorkingHours = $workingDaysPerMonth * $hoursPerDay;
        $hourlyRate = $monthlyWorkingHours > 0 ? $monthlyIncome / $monthlyWorkingHours : 0;

        // Income to expenses ratio (all-time)
        $incomeExpenseRatio = abs($totalExpenses) > 0 ? $totalIncome / abs($totalExpenses) : 0;

        $financialMetrics = [
            'daily_income' => $dailyIncome,
            'monthly_income' => $monthlyIncome,
            'yearly_income' => $yearlyIncome,
            'daily_expenses' => $dailyExpenses,
            'monthly_expenses' => $monthlyExpenses,
            'monthly_savings' => $monthlySavings,
            'savings_rate' => $savingsRate,
            'daily_balance' => $dailyBalance,
            'hourly_rate' => $hourlyRate,
            'income_expense_ratio' => $incomeExpenseRatio,
            'total_income' => $totalIncome,
            'total_expenses' => abs($totalExpenses),
        ];

        // ========== ADVANCED FINANCIAL METRICS ==========

        // 1. CASH RUNWAY - How long until money runs out
        // Only count positive balances as liquid assets (exclude debts/negative balances)
        $liquidAssets = $accounts->filter(function($account) {
            return $account->current_balance > 0;
        })->sum('current_balance');

        $cashRunwayDays = $dailyExpenses > 0 && $liquidAssets > 0 ? $liquidAssets / $dailyExpenses : 0;
        $cashRunwayMonths = $cashRunwayDays / 30;

        // 2. EMERGENCY FUND RATIO - Months of expenses covered
        $emergencyFundMonths = $monthlyExpenses > 0 && $liquidAssets > 0 ? $liquidAssets / $monthlyExpenses : 0;
        $emergencyFundStatus = $emergencyFundMonths >= 6 ? 'excellent' : ($emergencyFundMonths >= 3 ? 'good' : 'warning');

        // 3. BUDGET ADHERENCE - Overall budget performance
        $activeBudgets = \App\Models\Budget::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->get();

        $budgetAdherence = 0;
        $budgetsOnTrack = 0;
        $totalBudgeted = 0;
        $totalActualSpent = 0;

        foreach ($activeBudgets as $budget) {
            $spent = abs(Transaction::where('category_id', $budget->category_id)
                ->whereBetween('transaction_date', [$budget->period_start, $budget->period_end])
                ->where('amount', '<', 0)
                ->sum('amount'));

            $totalBudgeted += $budget->amount;
            $totalActualSpent += $spent;

            if ($spent <= $budget->amount) {
                $budgetsOnTrack++;
            }
        }

        $budgetAdherence = $activeBudgets->count() > 0 ? ($budgetsOnTrack / $activeBudgets->count()) * 100 : 0;

        // 4. NET CASH FLOW TREND (last 6 months)
        $cashFlowTrend = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('transaction_date', '>=', now()->subMonths(6))
            ->select(
                DB::raw(config('database.default') === 'sqlite'
                    ? "strftime('%Y-%m', transaction_date) as month"
                    : "DATE_FORMAT(transaction_date, '%Y-%m') as month"
                ),
                DB::raw('SUM(amount) as net')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        // 5. EXPENSE CATEGORY BREAKDOWN
        $expenseBreakdown = Transaction::with('category')
            ->whereIn('account_id', $accounts->pluck('id'))
            ->where('transaction_date', '>=', now()->subDays(30))
            ->where('amount', '<', 0)
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('SUM(ABS(amount)) as total'))
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // 6. MONTH-OVER-MONTH COMPARISON
        $lastMonthIncome = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('amount', '>', 0)
            ->whereBetween('transaction_date', [now()->subDays(60), now()->subDays(30)])
            ->sum('amount');

        $lastMonthExpenses = abs(Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('amount', '<', 0)
            ->whereBetween('transaction_date', [now()->subDays(60), now()->subDays(30)])
            ->sum('amount'));

        $incomeChange = $lastMonthIncome > 0 ? (($monthlyIncome - $lastMonthIncome) / $lastMonthIncome) * 100 : 0;
        $expenseChange = $lastMonthExpenses > 0 ? (($monthlyExpenses - $lastMonthExpenses) / $lastMonthExpenses) * 100 : 0;

        // 7. TOP EXPENSES THIS MONTH
        $topExpenses = Transaction::with(['category', 'account'])
            ->whereIn('account_id', $accounts->pluck('id'))
            ->where('amount', '<', 0)
            ->where('transaction_date', '>=', now()->subDays(30))
            ->orderBy('amount', 'asc')
            ->limit(5)
            ->get();

        // 8. RECURRING EXPENSES DETECTION (transactions that appear monthly)
        $recurringExpenses = Transaction::whereIn('account_id', $accounts->pluck('id'))
            ->where('amount', '<', 0)
            ->where('transaction_date', '>=', now()->subMonths(3))
            ->select('description', DB::raw('COUNT(*) as frequency'), DB::raw('AVG(ABS(amount)) as avg_amount'))
            ->groupBy('description')
            ->having('frequency', '>=', 2)
            ->orderBy('avg_amount', 'desc')
            ->limit(5)
            ->get();

        $totalRecurringCost = $recurringExpenses->sum('avg_amount');

        // 9. INCOME SOURCES BREAKDOWN
        $incomeSources = Transaction::with('category')
            ->whereIn('account_id', $accounts->pluck('id'))
            ->where('amount', '>', 0)
            ->where('transaction_date', '>=', now()->subDays(30))
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->get();

        // 10. FINANCIAL HEALTH SCORE (0-100)
        $healthScore = 0;
        $healthScore += min(25, ($savingsRate > 0 ? $savingsRate : 0)); // Max 25 points for savings rate
        $healthScore += min(25, $budgetAdherence / 4); // Max 25 points for budget adherence
        $healthScore += min(25, $emergencyFundMonths * 4.16); // Max 25 points (6 months = 25)
        $healthScore += ($incomeExpenseRatio >= 1 ? 25 : ($incomeExpenseRatio * 25)); // Max 25 points
        $healthScore = min(100, max(0, $healthScore));

        $advancedMetrics = [
            'cash_runway_days' => $cashRunwayDays,
            'cash_runway_months' => $cashRunwayMonths,
            'emergency_fund_months' => $emergencyFundMonths,
            'emergency_fund_status' => $emergencyFundStatus,
            'budget_adherence' => $budgetAdherence,
            'budgets_on_track' => $budgetsOnTrack,
            'total_budgets' => $activeBudgets->count(),
            'total_budgeted' => $totalBudgeted,
            'total_actual_spent' => $totalActualSpent,
            'cash_flow_trend' => $cashFlowTrend,
            'expense_breakdown' => $expenseBreakdown,
            'last_month_income' => $lastMonthIncome,
            'last_month_expenses' => $lastMonthExpenses,
            'income_change' => $incomeChange,
            'expense_change' => $expenseChange,
            'top_expenses' => $topExpenses,
            'recurring_expenses' => $recurringExpenses,
            'total_recurring_cost' => $totalRecurringCost,
            'income_sources' => $incomeSources,
            'health_score' => $healthScore,
        ];

        return view('dashboard', compact(
            'accounts',
            'totalsByCurrency',
            'recentTransactions',
            'monthlySpending',
            'transactionCount',
            'categorySpending',
            'chartData',
            'financialMetrics',
            'advancedMetrics'
        ))->with('page', 'dashboard');
    }
}
