<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Category;
use App\Models\EntityType;
use App\Models\Transaction;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExcelImportService
{
    protected $userId;
    protected $filePath;
    protected $user;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Import CSV file - matches FrolloImportService interface
     */
    public function importCsv($filePath): array
    {
        $this->user = User::find($this->userId);
        if (!$this->user) {
            throw new \Exception('User not found');
        }

        $this->filePath = $filePath;
        return $this->import();
    }

    /**
     * Internal import method
     */
    protected function import(): array
    {
        $stats = [
            'categories' => 0,
            'accounts' => 0,
            'transactions' => 0,
            'entity_types' => 0,
            'errors' => [],
        ];

        try {
            DB::beginTransaction();

            // 1. Import Entity Types
            $stats['entity_types'] = $this->importEntityTypes();

            // 2. Import Categories from Keywords sheet
            $stats['categories'] = $this->importCategoriesFromSheet();

            // 3. Import Accounts from ACCOUNTS sheet
            $stats['accounts'] = $this->importAccountsFromSheet();

            // 4. Import Transactions
            $stats['transactions'] = $this->importTransactionsFromSheets();

            DB::commit();

            Log::info('Excel import completed successfully', $stats);

        } catch (\Exception $e) {
            DB::rollBack();
            $stats['errors'][] = $e->getMessage();
            Log::error('Excel import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return $stats;
    }

    protected function importEntityTypes(): int
    {
        $types = [
            ['name' => 'Personal', 'user_ids' => [$this->user->id], 'color' => '#3B82F6'],
            ['name' => 'Work', 'user_ids' => [$this->user->id], 'color' => '#10B981'],
            ['name' => 'Shared', 'user_ids' => [$this->user->id], 'color' => '#8B5CF6'],
        ];

        foreach ($types as $type) {
            EntityType::firstOrCreate(['name' => $type['name']], $type);
        }

        return count($types);
    }

    protected function importCategoriesFromSheet(): int
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(false); // Need calculated values for formulas
        $reader->setLoadSheetsOnly(['Keywords']);

        $spreadsheet = $reader->load($this->filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $count = 0;
        $categoryGroups = [];
        $lastCategoryGroup = null;

        // Start from row 9 (where categories begin based on Excel analysis)
        // Column C = Category Name, D = Code, E = Frollo Category, F = Category Group
        for ($row = 9; $row <= min(200, $sheet->getHighestRow() ?? 200); $row++) {
            $name = $sheet->getCell('C' . $row)->getValue();
            $code = $sheet->getCell('D' . $row)->getValue();
            $frolloCategory = $sheet->getCell('E' . $row)->getValue();

            // Get calculated value for category group (handles formulas like =F10)
            $categoryGroup = $sheet->getCell('F' . $row)->getCalculatedValue();

            // Skip empty rows or header rows
            if (empty($code) || $name === 'Category') {
                continue;
            }

            // Track last valid category group for formula references
            if (!empty($categoryGroup) && !str_starts_with((string) $categoryGroup, '=')) {
                $lastCategoryGroup = $categoryGroup;
            } elseif (empty($categoryGroup) || str_starts_with((string) $categoryGroup, '=')) {
                $categoryGroup = $lastCategoryGroup;
            }

            // Determine category type
            $type = 'expense';
            if (
                stripos((string) $categoryGroup, 'income') !== false || stripos((string) $name, 'salary') !== false ||
                stripos((string) $name, 'interest') !== false || stripos((string) $name, 'invoices') !== false
            ) {
                $type = 'income';
            } elseif (stripos((string) $code, 'XXINTER') !== false || stripos((string) $name, 'transfer') !== false) {
                $type = 'transfer';
            }

            // Handle parent categories (category groups)
            $parentId = null;
            if (!empty($categoryGroup) && $categoryGroup !== $name) {
                // Create or get parent category
                if (!isset($categoryGroups[$categoryGroup])) {
                    $parent = Category::firstOrCreate(
                        ['code' => 'GROUP_' . substr(md5($categoryGroup), 0, 8)],
                        [
                            'name' => $categoryGroup,
                            'category_type' => $type,
                            'is_active' => true,
                            'order' => $count,
                        ]
                    );
                    $categoryGroups[$categoryGroup] = $parent->id;
                }
                $parentId = $categoryGroups[$categoryGroup];
            }

            // Create category
            Category::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'parent_id' => $parentId,
                    'category_type' => $type,
                    'frollo_category' => $frolloCategory,
                    'is_active' => true,
                    'order' => $count,
                ]
            );

            $count++;
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $count;
    }

    protected function importAccountsFromSheet(): int
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(['ACCOUNTS']);

        $spreadsheet = $reader->load($this->filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $count = 0;
        $personalEntity = EntityType::where('name', 'Personal')->first();

        // Row 3 starts the account data; Column C = name, Column D = currency
        for ($row = 3; $row <= min(100, $sheet->getHighestRow() ?? 100); $row++) {
            $name = $sheet->getCell('C' . $row)->getValue();
            $currency = $sheet->getCell('D' . $row)->getValue();

            if (empty($name) || empty($currency)) {
                continue;
            }

            // Skip if currency is not a valid 3-letter code
            if (strlen(trim($currency)) !== 3) {
                continue;
            }

            Account::updateOrCreate(
                ['name' => $name, 'currency' => strtoupper($currency)],
                [
                    'user_id' => $this->user->id,
                    'entity_type_id' => $personalEntity?->id,
                    'account_type' => 'personal',
                    'currency' => strtoupper($currency),
                    'opening_balance' => 0,
                    'current_balance' => 0,
                    'is_active' => true,
                ]
            );

            $count++;
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $count;
    }

    protected function importTransactionsFromSheets(): int
    {
        $count = 0;

        // Import from main Transacciones sheet
        $count += $this->importFromSingleSheet('Transacciones');

        // Import from Manual sheet
        $count += $this->importFromSingleSheet('Manual', true);

        return $count;
    }

    protected function importFromSingleSheet(string $sheetName, bool $isManual = false): int
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheetName]);

        try {
            $spreadsheet = $reader->load($this->filePath);
        } catch (\Exception $e) {
            Log::warning("Sheet $sheetName not found or could not be loaded");
            return 0;
        }

        $sheet = $spreadsheet->getActiveSheet();
        $count = 0;

        // Row 2 starts the transaction data, limit to 1000 rows for now
        $maxRow = min($sheet->getHighestRow() ?? 1000, 1000);

        for ($row = 2; $row <= $maxRow; $row++) {
            try {
                $description = $sheet->getCell('B' . $row)->getValue();
                $amount = $sheet->getCell('D' . $row)->getValue();
                $currency = $sheet->getCell('E' . $row)->getValue();
                $transactionDate = $sheet->getCell('F' . $row)->getValue();

                if (empty($description) || $amount === null) {
                    continue;
                }

                // Get default account
                $account = Account::where('currency', strtoupper($currency ?: 'AUD'))->first()
                    ?? Account::first();

                if (!$account) {
                    continue;
                }

                // Parse date
                $date = $this->parseDate($transactionDate);

                Transaction::create([
                    'account_id' => $account->id,
                    'transaction_date' => $date,
                    'description' => substr($description, 0, 255),
                    'user_description' => $sheet->getCell('C' . $row)->getValue(),
                    'amount' => $amount,
                    'currency' => strtoupper($currency ?: 'AUD'),
                    'is_manual' => $isManual,
                    'is_included' => true,
                ]);

                $count++;

            } catch (\Exception $e) {
                // Skip problematic rows
                continue;
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $count;
    }

    protected function parseDate($value)
    {
        if (empty($value)) {
            return now();
        }

        try {
            if (is_numeric($value)) {
                // Excel date format
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            }
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            return now();
        }
    }
}
