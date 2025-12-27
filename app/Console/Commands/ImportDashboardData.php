<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ImportDashboardData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:dashboard-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports income data from dashboard.csv';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path('../Source data/dashboard.csv');

        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        $this->info("Reading file: $filePath");

        $csvData = array_map('str_getcsv', file($filePath));
        $rowCount = count($csvData);

        // Fixed period for this specific import
        $startDate = Carbon::create(2025, 9, 1);
        $endDate = Carbon::create(2025, 9, 30);
        $transactionDate = $endDate; // Set transactions to end of month

        $activeAccount = \App\Models\Account::first(); // Fallback to first account if needed
        $accountId = $activeAccount ? $activeAccount->id : 1;

        $importedCount = 0;
        $budgetCount = 0;

        // Loop through rows starting from 24 (index 23)
        // Income data is in columns J (9), K (10), L (11), M (12), N (13), O (14)
        for ($i = 23; $i < $rowCount; $i++) {
            $row = $csvData[$i];

            // Check if we have enough columns
            if (count($row) < 15) {
                continue;
            }

            $rawName = isset($row[10]) ? trim($row[10]) : '';
            if (empty($rawName)) {
                continue;
            }

            // Stop if we hit the Projects section or other delimiters
            if (Str::startsWith($rawName, 'Projects') || $rawName === 'faltan') {
                $this->info("Hit delimiter '$rawName', stopping import.");
                break;
            }

            // Skip numeric looking names (Project counts usually in this column later)
            if (is_numeric($rawName)) {
                continue;
            }

            // Extract Icon and Name
            // Assumption: First char/grapheme is icon if it's an emoji-like pattern or just text
            // We'll try to split by space first
            $parts = explode(' ', $rawName, 2);
            $icon = null;
            $name = $rawName;

            if (count($parts) == 2) {
                // Check if first part looks like an emoji (short length)
                if (mb_strlen($parts[0]) <= 2) {
                    $icon = $parts[0];
                    $name = trim($parts[1]);
                }
            }

            // Find or Create Category
            $category = Category::where('name', $name)->orWhere('name', $rawName)->first();

            if (!$category) {
                $this->info("Creating category: $name");
                $category = Category::create([
                    'name' => $name,
                    'code' => Str::slug($name), // Generate code from name
                    'icon' => $icon,
                    'category_type' => 'income',
                    'is_active' => true,
                    // defaults
                    'color' => '#10B981', // green for income
                ]);
            }

            // Clean amounts
            $actualStr = isset($row[11]) ? $row[11] : '0';
            $budgetStr = isset($row[12]) ? $row[12] : '0';

            $actual = $this->parseAmount($actualStr);
            $budgetAmount = $this->parseAmount($budgetStr);

            $this->line("Processing $name: Actual=$actual, Budget=$budgetAmount");

            // Create Transaction if Actual > 0 or < 0 (non-zero)
            if ($actual != 0) {
                // Check for existing transaction to avoid duplicates?
                // For now, assuming new data or we overwrite/append. 
                // Let's check if one exists for this category/month to be safe?
                // The user said "missing data", so we likely just add it.
                // But a simple check is good:
                $exists = Transaction::where('category_id', $category->id)
                    ->whereBetween('transaction_date', [$startDate, $endDate])
                    ->where('amount', $actual)
                    ->exists();

                if (!$exists) {
                    Transaction::create([
                        'user_id' => 1, // Assumption: User ID 1
                        'account_id' => $accountId,
                        'category_id' => $category->id,
                        'transaction_date' => $transactionDate,
                        'amount' => $actual,
                        'description' => 'Imported Income from Dashboard',
                        'income_outcome' => 'income',
                        'currency' => 'AUD', // Assumption
                    ]);
                    $importedCount++;
                }
            }

            // Create/Update Budget
            // Budget table: category_id, amount, period_start, period_end, etc.
            if ($budgetAmount != 0) {
                $budget = Budget::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'period_start' => $startDate->format('Y-m-d'),
                        'period_end' => $endDate->format('Y-m-d'),
                    ],
                    [
                        'amount' => $budgetAmount,
                        'period_type' => 'monthly',
                        'is_active' => true,
                    ]
                );
                $budgetCount++;
            }
        }

        $this->info("Import completed.");
        $this->info("Transactions imported: $importedCount");
        $this->info("Budgets updated: $budgetCount");

        return 0;
    }

    private function parseAmount($str)
    {
        if (empty($str))
            return 0.0;

        // Remove $ and ,
        $clean = str_replace(['$', ','], '', $str);

        // Handle '(1000)' as negative? CSV usually uses - sign but just in case
        if (Str::startsWith($clean, '(') && Str::endsWith($clean, ')')) {
            $clean = '-' . substr($clean, 1, -1);
        }

        return (float) $clean;
    }
}
