<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FrolloImportService
{
    protected $userId;
    protected $stats = [
        'accounts_created' => 0,
        'accounts_updated' => 0,
        'transactions_created' => 0,
        'transactions_skipped' => 0,
        'errors' => []
    ];

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function importCsv($filePath)
    {
        DB::beginTransaction();

        try {
            $handle = fopen($filePath, 'r');
            if ($handle === false) {
                throw new \Exception('Could not open CSV file');
            }

            // Read header
            $header = fgetcsv($handle);
            if (!$header) {
                throw new \Exception('CSV file is empty or invalid');
            }

            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                try {
                    $data = array_combine($header, $row);
                    $this->processRow($data, $rowNumber);
                } catch (\Exception $e) {
                    $this->stats['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                    Log::error("Frollo import error on row {$rowNumber}: " . $e->getMessage());
                }
            }

            fclose($handle);

            DB::commit();
            return $this->stats;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function processRow($data, $rowNumber)
    {
        // Frollo CSV typical columns (adjust based on actual format):
        // Date, Description, Amount, Category, Account, Type, etc.

        if (empty($data['Date']) || empty($data['Amount'])) {
            $this->stats['transactions_skipped']++;
            return;
        }

        // Parse account
        $accountName = $data['Account'] ?? 'Unknown Account';
        $account = $this->getOrCreateAccount($accountName, $data);

        // Parse transaction date
        $transactionDate = $this->parseDate($data['Date']);

        // Parse amount (handle negatives)
        $amount = $this->parseAmount($data['Amount']);

        // Find category
        $categoryId = null;
        if (!empty($data['Category'])) {
            $category = Category::where('name', 'LIKE', '%' . $data['Category'] . '%')->first();
            $categoryId = $category->id ?? null;
        }

        // Create unique external ID
        $externalId = md5($accountName . $data['Date'] . $data['Description'] . $amount);

        // Check for duplicates
        $exists = Transaction::where('external_id', $externalId)->exists();
        if ($exists) {
            $this->stats['transactions_skipped']++;
            return;
        }

        // Create transaction
        Transaction::create([
            'account_id' => $account->id,
            'category_id' => $categoryId,
            'external_id' => $externalId,
            'transaction_date' => $transactionDate,
            'description' => $data['Description'] ?? 'Unknown',
            'amount' => $amount,
            'currency' => $data['Currency'] ?? $account->currency ?? 'AUD',
            'merchant_name' => $data['Merchant'] ?? null,
            'transaction_type' => $data['Type'] ?? null,
            'budget_category' => $data['Category'] ?? null,
            'is_manual' => false,
            'is_included' => true,
        ]);

        $this->stats['transactions_created']++;
    }

    protected function getOrCreateAccount($accountName, $data)
    {
        $account = Account::where('user_id', $this->userId)
            ->where('name', $accountName)
            ->first();

        if ($account) {
            $this->stats['accounts_updated']++;
            return $account;
        }

        // Create new account
        $account = Account::create([
            'user_id' => $this->userId,
            'name' => $accountName,
            'account_type' => 'bank', // Default, user can change later
            'ownership' => 'buda', // Default
            'institution' => $data['Institution'] ?? null,
            'currency' => $data['Currency'] ?? 'AUD',
            'opening_balance' => 0,
            'current_balance' => 0,
            'is_active' => true,
        ]);

        $this->stats['accounts_created']++;
        return $account;
    }

    protected function parseDate($dateString)
    {
        // Try multiple date formats
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        // Default to today if parsing fails
        return now()->format('Y-m-d');
    }

    protected function parseAmount($amountString)
    {
        // Remove currency symbols, spaces, commas
        $cleaned = preg_replace('/[^\d.-]/', '', $amountString);
        return (float) $cleaned;
    }

    public function getStats()
    {
        return $this->stats;
    }
}
