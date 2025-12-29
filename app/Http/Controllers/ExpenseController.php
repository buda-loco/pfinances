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
        $data = $this->getExpenseData($request);

        return view('expenses.index', [
            'summaryData' => $data['summaryData'],
            'totals' => $data['totals'],
            'selectedMonth' => $data['selectedMonth'],
            'selectedYear' => $data['selectedYear'],
            'allCategories' => $data['allCategories'],
            'selectedCategory' => $data['selectedCategory'],
            'periodText' => $data['periodText']
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'xlsx'); // xlsx or csv
        $data = $this->getExpenseData($request);

        $summaryData = $data['summaryDataArray'];
        $periodText = $data['periodText'];

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
        $filename = 'expenses-' . $data['selectedMonth'] . '-' . $data['selectedYear'];

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

    /**
     * Extract expense data calculation logic to avoid duplication between index and export
     */
    private function getExpenseData(Request $request): array
    {
        $currentDate = Carbon::now();
        $selectedMonth = $request->has('month') ? $request->input('month') : $currentDate->month;
        $selectedYear = $request->has('year') ? $request->input('year') : $currentDate->year;
        $selectedCategory = $request->input('category_id');

        // Define ranges based on selection
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

            $prevYearStart = $currentStart->copy()->subYear();
            $prevYearEnd = $prevYearStart->copy()->endOfYear();
        } else {
            // All Time
            $currentStart = Carbon::createFromDate(2000, 1, 1);
            $currentEnd = Carbon::now()->endOfYear();
        }

        // Fetch categories
        $allCategories = Category::where('is_active', true)->orderBy('name')->get();

        $categoriesQuery = Category::where('is_active', true);
        if ($selectedCategory) {
            $categoriesQuery->where('id', $selectedCategory);
        }
        $categories = $categoriesQuery->get();

        // Aggregate current period actuals
        $currentActuals = Transaction::whereBetween('transaction_date', [$currentStart, $currentEnd])
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        // Aggregate previous month actuals
        $prevMonthActuals = collect();
        if ($prevMonthStart) {
            $prevMonthActuals = Transaction::whereBetween('transaction_date', [$prevMonthStart, $prevMonthEnd])
                ->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->pluck('total', 'category_id');
        }

        // Aggregate previous year actuals
        $prevYearActuals = collect();
        if ($prevYearStart) {
            $prevYearActuals = Transaction::whereBetween('transaction_date', [$prevYearStart, $prevYearEnd])
                ->select('category_id', DB::raw('SUM(amount) as total'))
                ->groupBy('category_id')
                ->pluck('total', 'category_id');
        }

        // Transform data for view (returns objects for view compatibility)
        $summaryData = $categories->map(function ($category) use ($currentActuals, $prevMonthActuals, $prevYearActuals) {
            $actual = $currentActuals[$category->id] ?? 0;

            // Clean name for display
            $category->name = preg_replace('/^\s*[\[\(]*\s*tag\s*[\]\)]*[-_: ]*\s*/i', '', $category->name);

            return (object) [
                'category' => $category,
                'actual' => $actual,
                'budget' => $category->monthly_budget ?? 0,
                'diff' => $actual - ($category->monthly_budget ?? 0),
                'prev_month' => $prevMonthActuals[$category->id] ?? 0,
                'prev_year' => $prevYearActuals[$category->id] ?? 0,
            ];
        });

        // Transform data for export (returns arrays)
        $summaryDataArray = $categories->map(function ($category) use ($currentActuals, $prevMonthActuals, $prevYearActuals) {
            $actual = $currentActuals[$category->id] ?? 0;
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

        // Calculate totals
        $totals = [
            'actual' => $summaryData->sum('actual'),
            'budget' => $summaryData->sum('budget'),
            'diff' => $summaryData->sum('diff'),
            'prev_month' => $summaryData->sum('prev_month'),
            'prev_year' => $summaryData->sum('prev_year'),
        ];

        return [
            'summaryData' => $summaryData,
            'summaryDataArray' => $summaryDataArray,
            'totals' => $totals,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'allCategories' => $allCategories,
            'selectedCategory' => $selectedCategory,
            'periodText' => $periodText,
        ];
    }
}
