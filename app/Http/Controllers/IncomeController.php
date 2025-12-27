<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        // ... (Same data fetching logic as before, refactor into private method for reuse)
        $data = $this->getIncomeData($request);
        return view('income.index', $data);
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'xlsx');
        $data = $this->getIncomeData($request);
        $incomeData = $data['allData'];
        $summary = $data['summary'];
        $year = $data['year'];
        $monthName = \DateTime::createFromFormat('!m', $data['month'])->format('F');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->setCellValue('A1', "Income Report - $monthName $year");
        $sheet->setCellValue('A2', "Generated on " . now()->toDateTimeString());

        // Summary
        $sheet->setCellValue('A4', 'Summary');
        $sheet->setCellValue('A5', 'Planned');
        $sheet->setCellValue('B5', $summary['planned']);
        $sheet->setCellValue('A6', 'Actual');
        $sheet->setCellValue('B6', $summary['actual']);
        $sheet->setCellValue('A7', 'Previous Month');
        $sheet->setCellValue('B7', $summary['prev_month']);
        $sheet->setCellValue('A8', 'Previous Year');
        $sheet->setCellValue('B8', $summary['prev_year']);

        // Table Headers
        $row = 10;
        $sheet->setCellValue("A$row", 'Category');
        $sheet->setCellValue("B$row", 'Actual');
        $sheet->setCellValue("C$row", 'Budget');
        $sheet->setCellValue("D$row", 'Difference');
        $sheet->setCellValue("E$row", 'Previous Month');
        $sheet->setCellValue("F$row", 'Previous Year');

        // Data
        $row++;
        $sheet->setCellValue("A$row", 'TOTALS');
        $sheet->setCellValue("B$row", $summary['actual']);
        $sheet->setCellValue("C$row", $summary['planned']);
        $sheet->setCellValue("D$row", $summary['actual'] - $summary['planned']);
        $sheet->setCellValue("E$row", $summary['prev_month']);
        $sheet->setCellValue("F$row", $summary['prev_year']);
        $sheet->getStyle("A$row:F$row")->getFont()->setBold(true);

        $row++;
        foreach ($incomeData as $item) {
            $sheet->setCellValue("A$row", $item['name']); // Name (removing emoji/icon if simple string, but here likely okay)
            $sheet->setCellValue("B$row", $item['actual']);
            $sheet->setCellValue("C$row", $item['budget']);
            $sheet->setCellValue("D$row", $item['difference']);
            $sheet->setCellValue("E$row", $item['previous_month']);
            $sheet->setCellValue("F$row", $item['previous_year']);
            $row++;
        }

        $filename = "Income-Report-$year-$data[month]";

        if ($format === 'csv') {
            $writer = new Csv($spreadsheet);
            $filename .= '.csv';

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $writer->save('php://output');
            exit;
        } else {
            // Default to XLSX
            $writer = new Xlsx($spreadsheet);
            $filename .= '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $writer->save('php://output');
            exit;
        }
    }

    private function getIncomeData(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        // Filters
        $accountId = $request->input('account_id');
        $projectId = $request->input('project_id');
        $categoryId = $request->input('category_id');
        $uncategorized = $request->boolean('uncategorized');

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $prevMonthStart = $startDate->copy()->subMonth();
        $prevMonthEnd = $prevMonthStart->copy()->endOfMonth();

        $prevYearStart = $startDate->copy()->subYear();
        $prevYearEnd = $prevYearStart->copy()->endOfMonth();

        // Fetch Dropdown Data
        $accounts = Account::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $allCategories = Category::where('is_active', true)->orderBy('name')->get();

        // Helper to apply filters to transaction queries
        $applyFilters = function ($query) use ($accountId, $projectId, $categoryId, $uncategorized) {
            if ($accountId) {
                $query->where('account_id', $accountId);
            }
            if ($projectId) {
                $query->where('project_id', $projectId);
            }
            if ($uncategorized) {
                $query->whereNull('category_id');
            } elseif ($categoryId) {
                $query->where('category_id', $categoryId);
            }
        };

        // Aggregate Actuals (Current Period)
        $actualsQuery = Transaction::where('income_outcome', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate]);
        $applyFilters($actualsQuery);
        $actuals = $actualsQuery->select('category_id', DB::raw('sum(amount) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        // Fetch Budgets (Current Period)
        // Note: Budgets are not filtered by Account/Project as they are typically global per category.
        // However, if filtering by Category, we technically naturally filter budgets by mapping.
        $budgets = Budget::where('is_active', true)
            ->where('period_start', '<=', $endDate)
            ->where('period_end', '>=', $startDate)
            ->get();

        $budgetMap = [];
        foreach ($budgets as $budget) {
            if (!isset($budgetMap[$budget->category_id])) {
                $budgetMap[$budget->category_id] = 0;
            }
            $budgetMap[$budget->category_id] += $budget->amount;
        }

        // Aggregate Previous Month & Year
        $prevMonthQuery = Transaction::where('income_outcome', 'income')
            ->whereBetween('transaction_date', [$prevMonthStart, $prevMonthEnd]);
        $applyFilters($prevMonthQuery);
        $prevMonthActuals = $prevMonthQuery->select('category_id', DB::raw('sum(amount) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $prevYearQuery = Transaction::where('income_outcome', 'income')
            ->whereBetween('transaction_date', [$prevYearStart, $prevYearEnd]);
        $applyFilters($prevYearQuery);
        $prevYearActuals = $prevYearQuery->select('category_id', DB::raw('sum(amount) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        // Build View Data
        $data = [];
        $summary = [
            'planned' => 0,
            'actual' => 0,
            'prev_month' => 0,
            'prev_year' => 0,
            'growth_percentage' => 0,
        ];

        // Process Standard Categories
        foreach ($allCategories as $category) {
            // If "Uncategorized" filter is ON, skip standard categories unless we want to show 0s? 
            // Typically we just show what matches.
            if ($uncategorized) {
                // If searching for uncategorized, skip regular categories
                continue;
            }

            // If Category Filter is ON and this isn't the one, skip
            if ($categoryId && $category->id != $categoryId) {
                continue;
            }

            $catId = $category->id;
            $act = $actuals[$catId] ?? 0;
            $bud = $budgetMap[$catId] ?? ($category->monthly_budget ?? 0);
            $pm = $prevMonthActuals[$catId] ?? 0;
            $py = $prevYearActuals[$catId] ?? 0;

            if ($act == 0 && $bud == 0 && $pm == 0 && $py == 0)
                continue;

            $data[] = [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon ?? '💰',
                'actual' => $act,
                'budget' => $bud,
                'difference' => $act - $bud,
                'previous_month' => $pm,
                'previous_year' => $py,
            ];

            $summary['planned'] += $bud;
            $summary['actual'] += $act;
            $summary['prev_month'] += $pm;
            $summary['prev_year'] += $py;
        }

        // Process Uncategorized (category_id = null)
        // Only if Uncategorized filter is ON OR (No CategFilter is ON)
        if ($uncategorized || !$categoryId) {
            // pluck(val, key). If key is null in DB, Laravel pluck might use empty string ""?
            // Let's check for empty string key in $actuals collection
            $uncatAct = $actuals[''] ?? 0;
            $uncatPm = $prevMonthActuals[''] ?? 0;
            $uncatPy = $prevYearActuals[''] ?? 0;

            // If any data exists
            if ($uncatAct != 0 || $uncatPm != 0 || $uncatPy != 0) {
                $data[] = [
                    'id' => 'uncategorized',
                    'name' => 'Uncategorized',
                    'icon' => '❓',
                    'actual' => $uncatAct,
                    'budget' => 0,
                    'difference' => $uncatAct,
                    'previous_month' => $uncatPm,
                    'previous_year' => $uncatPy,
                ];

                $summary['actual'] += $uncatAct;
                $summary['prev_month'] += $uncatPm;
                $summary['prev_year'] += $uncatPy;
            }
        }

        // Calculate Growth Percentage (Actual vs Previous Month)
        if ($summary['prev_month'] > 0) {
            $summary['growth_percentage'] = (($summary['actual'] - $summary['prev_month']) / $summary['prev_month']) * 100;
        } elseif ($summary['actual'] > 0) {
            $summary['growth_percentage'] = 100; // 100% growth if prev was 0 and now we have income
        } else {
            $summary['growth_percentage'] = 0;
        }

        // Create Collection for Sorting and Pagination
        $collection = collect($data);

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortOrder = $request->input('sort_order', 'asc');

        if ($sortOrder === 'desc') {
            $collection = $collection->sortByDesc($sortBy);
        } else {
            $collection = $collection->sortBy($sortBy);
        }

        // Pagination
        $page = request()->input('page', 1);
        $perPage = 15;
        $items = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return [
            'data' => $paginator,
            'allData' => $collection,
            'summary' => $summary,
            'year' => $year,
            'month' => $month,
            'accounts' => $accounts,
            'projects' => $projects,
            'allCategories' => $allCategories,
            'accountId' => $accountId,
            'projectId' => $projectId,
            'categoryId' => $categoryId,
            'uncategorized' => $uncategorized
        ];
    }
}
