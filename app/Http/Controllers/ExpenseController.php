<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = Carbon::now();
        // Check if value is present in request (even empty), otherwise default
        $selectedMonth = $request->has('month') ? $request->input('month') : $currentDate->month;
        $selectedYear = $request->has('year') ? $request->input('year') : $currentDate->year;

        // Define ranges based on selection
        $prevMonthStart = null;
        $prevMonthEnd = null;
        $prevYearStart = null;
        $prevYearEnd = null;

        if ($selectedMonth && $selectedYear) {
            // Specific Month
            $currentStart = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
            $currentEnd = $currentStart->copy()->endOfMonth();

            $prevMonthStart = $currentStart->copy()->subMonth();
            $prevMonthEnd = $prevMonthStart->copy()->endOfMonth();

            $prevYearStart = $currentStart->copy()->subYear();
            $prevYearEnd = $prevYearStart->copy()->endOfMonth();
        } elseif (!$selectedMonth && $selectedYear) {
            // Full Year
            $currentStart = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $currentEnd = $currentStart->copy()->endOfYear();

            // "Prev Month" column not relevant for full year, maybe reuse for Previous Year? 
            // Or set to null. Let's set to null/empty for now, user can just check Prev Year col.
            $prevMonthStart = null;

            $prevYearStart = $currentStart->copy()->subYear();
            $prevYearEnd = $prevYearStart->copy()->endOfYear();
        } else {
            // All Time (No Year Selected)
            $currentStart = Carbon::createFromDate(2000, 1, 1);
            $currentEnd = Carbon::now()->endOfYear();
        }

        // Fetch categories (Expense type only or all?) - Assuming all for now, filter in view or here
        // Usually expenses are specific types, but let's grab all active ones
        $allCategories = Category::where('is_active', true)->orderBy('name')->get();

        $selectedCategory = $request->input('category_id');

        $categoriesQuery = Category::where('is_active', true);
        if ($selectedCategory) {
            $categoriesQuery->where('id', $selectedCategory);
        }
        $categories = $categoriesQuery->get();

        // 1. Current Period Actuals
        $currentActuals = Transaction::whereBetween('transaction_date', [$currentStart, $currentEnd])
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        // 2. Previous Month Actuals
        $prevMonthActuals = collect();
        if ($prevMonthStart) {
            $prevMonthActuals = Transaction::whereBetween('transaction_date', [$prevMonthStart, $prevMonthEnd])
                ->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->pluck('total', 'category_id');
        }

        // 3. Previous Year Actuals
        $prevYearActuals = collect();
        if ($prevYearStart) {
            $prevYearActuals = Transaction::whereBetween('transaction_date', [$prevYearStart, $prevYearEnd])
                ->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->pluck('total', 'category_id');
        }

        // Transform data for view
        $summaryData = $categories->map(function ($category) use ($currentActuals, $prevMonthActuals, $prevYearActuals) {
            $actual = $currentActuals[$category->id] ?? 0;

            // Clean name
            $category->name = preg_replace('/^\s*[\[\(]*\s*tag\s*[\]\)]*[-_: ]*\s*/i', '', $category->name);

            $dataset = [
                'category' => $category,
                'actual' => $actual,
                'budget' => $category->monthly_budget ?? 0,
                'diff' => $actual - ($category->monthly_budget ?? 0),
                'prev_month' => $prevMonthActuals[$category->id] ?? 0,
                'prev_year' => $prevYearActuals[$category->id] ?? 0,
            ];
            return (object) $dataset;
        });

        // Calculate Totals
        $totals = [
            'actual' => $summaryData->sum('actual'),
            'budget' => $summaryData->sum('budget'),
            'diff' => $summaryData->sum('diff'),
            'prev_month' => $summaryData->sum('prev_month'),
            'prev_year' => $summaryData->sum('prev_year'),
        ];

        // Determine Period Text for View/Export
        $periodText = 'All Time';
        if ($selectedMonth && $selectedYear) {
            $periodText = $currentStart->format('F Y');
        } elseif (!$selectedMonth && $selectedYear) {
            $periodText = "Year " . $selectedYear;
        }

        return view('expenses.index', compact('summaryData', 'totals', 'selectedMonth', 'selectedYear', 'allCategories', 'selectedCategory', 'periodText'));
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'xlsx'); // xlsx or csv
        $currentDate = Carbon::now();
        $selectedMonth = $request->has('month') ? $request->input('month') : $currentDate->month;
        $selectedYear = $request->has('year') ? $request->input('year') : $currentDate->year;
        $selectedCategory = $request->input('category_id');

        // --- DUPLICATED AGGREGATION LOGIC (Refactor to service in future) ---
        $prevMonthStart = null;
        $prevMonthEnd = null;
        $prevYearStart = null;
        $prevYearEnd = null;

        $periodText = 'All Time';

        if ($selectedMonth && $selectedYear) {
            // Specific Month
            $currentStart = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->startOfMonth();
            $currentEnd = $currentStart->copy()->endOfMonth();
            $periodText = $currentStart->format('F Y');

            $prevMonthStart = $currentStart->copy()->subMonth();
            $prevMonthEnd = $prevMonthStart->copy()->endOfMonth();

            $prevYearStart = $currentStart->copy()->subYear();
            $prevYearEnd = $prevYearStart->copy()->endOfMonth();
        } elseif (!$selectedMonth && $selectedYear) {
            // Full Year
            $currentStart = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear();
            $currentEnd = $currentStart->copy()->endOfYear();
            $periodText = "Year " . $selectedYear;

            $prevMonthStart = null;

            $prevYearStart = $currentStart->copy()->subYear();
            $prevYearEnd = $prevYearStart->copy()->endOfYear();
        } else {
            // All Time
            $currentStart = Carbon::createFromDate(2000, 1, 1);
            $currentEnd = Carbon::now()->endOfYear();
            // periodText default is All Time
        }

        $categoriesQuery = Category::where('is_active', true);
        if ($selectedCategory) {
            $categoriesQuery->where('id', $selectedCategory);
        }
        $categories = $categoriesQuery->get();

        $currentActuals = Transaction::whereBetween('transaction_date', [$currentStart, $currentEnd])
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $prevMonthActuals = collect();
        if ($prevMonthStart) {
            $prevMonthActuals = Transaction::whereBetween('transaction_date', [$prevMonthStart, $prevMonthEnd])
                ->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->pluck('total', 'category_id');
        }

        $prevYearActuals = collect();
        if ($prevYearStart) {
            $prevYearActuals = Transaction::whereBetween('transaction_date', [$prevYearStart, $prevYearEnd])
                ->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->pluck('total', 'category_id');
        }

        $summaryData = $categories->map(function ($category) use ($currentActuals, $prevMonthActuals, $prevYearActuals) {
            $actual = $currentActuals[$category->id] ?? 0;

            // Clean name
            $cleanName = preg_replace('/^\s*[\[\(]*\s*tag\s*[\]\)]*[-_: ]*\s*/i', '', $category->name);

            return [
                'name' => ($category->icon ? $category->icon . ' ' : '') . $cleanName,
                'actual' => $actual,
                'budget' => $category->monthly_budget ?? 0,
                'diff' => $actual - ($category->monthly_budget ?? 0),
                'prev_month' => $prevMonthActuals[$category->id] ?? 0,
                'prev_year' => $prevYearActuals[$category->id] ?? 0,
            ];
        });
        // ------------------------------------------------------------------

        // CREATE SPREADSHEET
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // HEADING
        $sheet->setCellValue('A1', 'Expense Summary');
        $sheet->setCellValue('A2', 'Period: ' . $periodText);
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // TABLE HEADER
        $headers = ['Category', 'Actual', 'Budget', 'Difference', 'Prev Month', 'Prev Year'];
        $sheet->fromArray($headers, NULL, 'A4');
        $sheet->getStyle('A4:F4')->getFont()->setBold(true);

        // DATA
        $row = 5;
        foreach ($summaryData as $data) {
            $sheet->setCellValue('A' . $row, $data['name']);
            $sheet->setCellValue('B' . $row, $data['actual']);
            $sheet->setCellValue('C' . $row, $data['budget']);
            $sheet->setCellValue('D' . $row, $data['diff']);
            $sheet->setCellValue('E' . $row, $data['prev_month']);
            $sheet->setCellValue('F' . $row, $data['prev_year']);

            // Format Numbers
            $sheet->getStyle('B' . $row . ':F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // Color Difference Column
            if ($data['diff'] < 0) {
                $sheet->getStyle('D' . $row)->getFont()->getColor()->setARGB('FFFF0000'); // Red
            } else {
                $sheet->getStyle('D' . $row)->getFont()->getColor()->setARGB('FF008000'); // Green
            }

            $row++;
        }

        // AUTO SIZE COLUMNS
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // GENERATE FILE
        $filename = 'expenses-' . $selectedMonth . '-' . $selectedYear;

        if ($type === 'csv') {
            $writer = new Csv($spreadsheet);
            $filename .= '.csv';

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $writer->save('php://output');
        } else {
            $writer = new Xlsx($spreadsheet);
            $filename .= '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $writer->save('php://output');
        }
        exit;
    }
}
