<?php

namespace App\Http\Controllers;

use App\Models\AccountBalance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PortfolioController extends Controller
{
    public function index()
    {
        $group = request('group', 'monthly');
        $isSqlite = config('database.default') === 'sqlite';

        // 1. Handle Year Filtering - database agnostic
        $yearSql = $isSqlite ? "strftime('%Y', date)" : "YEAR(date)";
        $availableYears = AccountBalance::selectRaw("$yearSql as year")
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Get selected years from request or default to all
        $selectedYears = request('years', $availableYears);

        // Ensure selectedYears is an array if it comes as a string
        if (is_string($selectedYears)) {
            $selectedYears = explode(',', $selectedYears);
        }

        // Define SQL select for grouping - database agnostic
        if ($group === 'annually') {
            $periodSql = $isSqlite
                ? "strftime('%Y', date)"
                : "YEAR(date)";
        } elseif ($group === 'quarterly') {
            $periodSql = $isSqlite
                ? "strftime('%Y', date) || '-Q' || ((strftime('%m', date) - 1) / 3 + 1)"
                : "CONCAT(YEAR(date), '-Q', QUARTER(date))";
        } else {
            // Default Monthly
            $periodSql = $isSqlite
                ? "strftime('%Y-%m', date)"
                : "DATE_FORMAT(date, '%Y-%m')";
        }

        $balances = AccountBalance::select(
            'currency',
            DB::raw("$periodSql as period"),
            DB::raw('SUM(balance) as total_balance')
        )
            ->whereIn(DB::raw($yearSql), $selectedYears) // Apply Year Filter - database agnostic
            ->groupBy('currency', 'period')
            ->orderBy('period', 'asc')
            ->get();

        // 2. Prepare Data Structure
        $periodData = [];
        $currencies = $balances->pluck('currency')->unique()->values()->toArray();

        foreach ($balances as $b) {
            $periodData[$b->period][$b->currency] = (float) $b->total_balance;
        }

        $labels = array_keys($periodData);
        sort($labels);

        $chartSeries = [];
        foreach ($currencies as $currency) {
            $data = [];
            foreach ($labels as $period) {
                $data[] = $periodData[$period][$currency] ?? 0;
            }
            $chartSeries[] = [
                'name' => $currency,
                'data' => $data
            ];
        }

        // 3. Prepare Table Data & Format Labels
        $tableRows = [];
        $formattedLabels = [];

        foreach ($labels as $period) {
            // Format label for Chart and Table
            if ($group === 'quarterly') {
                // Period is like 2024-Q1 or 2024-Q1.0 
                $parts = explode('-Q', $period);
                if (count($parts) === 2) {
                    $year = $parts[0];
                    $q = (int) $parts[1];
                    $display = "Q{$q} {$year}";
                } else {
                    $display = $period;
                }
            } elseif ($group === 'annually') {
                $display = $period;
            } else {
                try {
                    $display = Carbon::createFromFormat('Y-m', $period)->format('M Y');
                } catch (\Exception $e) {
                    $display = $period;
                }
            }
            $formattedLabels[$period] = $display;
        }

        foreach (array_reverse($labels) as $period) {
            $row = [
                'month' => $formattedLabels[$period], // We keep key 'month' for view compatibility
                'raw_month' => $period,
            ];
            foreach ($currencies as $currency) {
                $row[$currency] = $periodData[$period][$currency] ?? 0;
            }
            $tableRows[] = $row;
        }

        // Send formatted labels to chart
        $chartLabels = array_values(array_map(function ($l) use ($formattedLabels) {
            return $formattedLabels[$l];
        }, $labels));

        // 4. Prepare Matrix Data (Account x Date view)
        // Apply Year Filter to matrix dates too - database agnostic
        $matrixDates = AccountBalance::select('date')
            ->whereIn(DB::raw($yearSql), $selectedYears)
            ->distinct()
            ->orderBy('date')
            ->pluck('date');

        $matrixAccounts = \App\Models\Account::whereHas('balances')
            ->with(['balances'])
            ->get()
            ->map(function ($account) {
                // Map balances to date => amount
                $account->balance_map = $account->balances->pluck('balance', 'date');
                return $account;
            });

        return view('portfolio.index', compact(
            'chartSeries',
            'chartLabels',
            'tableRows',
            'currencies',
            'group',
            'matrixDates',
            'matrixAccounts'
        ));
    }
}
