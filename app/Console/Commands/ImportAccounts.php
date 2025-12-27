<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class ImportAccounts extends Command
{
    protected $signature = 'import:accounts {--with-transactions : Import transactions from the Movimientos block}';
    protected $description = 'Import accounts and transactions from the source CSV file';

    public function handle()
    {
        $csvPath = base_path('../Source data/V2 ULTIMATE BUDGET AND CONTROL SHEET - ACCOUNTS.csv');

        if (!File::exists($csvPath)) {
            $this->error("CSV file not found at: $csvPath");
            return;
        }

        $this->info("Reading CSV from: $csvPath");

        $file = fopen($csvPath, 'r');

        // --- PHASE 1: ACCOUNTS ---
        $this->info("Phase 1: Importing Accounts...");

        $line1 = fgetcsv($file); // Skip Line 1
        $header = fgetcsv($file); // Line 2: Headers

        // Identify date columns
        $dateIndices = [];
        $dateMap = []; // Index => Carbon date
        foreach ($header as $index => $colName) {
            if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', trim($colName), $matches)) {
                $dateIndices[] = $index;
                try {
                    $dateMap[$index] = Carbon::createFromDate($matches[3], $matches[2], $matches[1]);
                } catch (\Exception $e) {
                    // ignore invalid dates
                }
            }
        }

        if (empty($dateIndices)) {
            $this->error("No date columns found in CSV header.");
            return;
        }

        $user = User::first();
        if (!$user) {
            $this->error('No users found in database.');
            return;
        }

        $accountCount = 0;
        $transactionCount = 0;

        // We need to keep reading until we finish the file or hit "Movements" block logic
        // But fgetcsv is sequential.
        // We will process accounts until we detect the gap/Movimientos block.

        $processedAccounts = []; // Name -> Model

        while (($row = fgetcsv($file)) !== false) {
            // Detect end of Accounts block
            // Row 1 of Movimientos block has "Movimientos" in it
            if (isset($row[1]) && str_contains($row[1], 'Movimientos')) {
                $this->info("Found Movimientos block start.");
                break; // Exit Account loop
            }

            // Allow skipping empty lines but break if we see "Movimientos" in any column roughly
            if (empty($row[1]) && empty($row[2])) {
                // Peek next? No peeking. Just continue.
                continue;
            }

            // Headers again?
            if ($row[1] == 'ref' && $row[2] == 'cuenta')
                continue;

            if (count($row) < 5)
                continue;

            $ref = $row[1];
            $name = $row[2];
            $currency = $row[3];
            $description = $row[4];

            // If name is header-like
            if ($name === 'cuenta')
                continue;

            // Extract Opening and Current Balance
            $openingBalance = 0;
            $currentBalance = 0;
            $foundFirst = false;

            foreach ($dateIndices as $idx) {
                if (!isset($row[$idx]))
                    continue;
                $val = trim($row[$idx]);
                if ($val === '')
                    continue;
                $cleanVal = preg_replace('/[^\d.-]/', '', $val);

                if (is_numeric($cleanVal)) {
                    $amount = (float) $cleanVal;
                    if (!$foundFirst) {
                        $openingBalance = $amount;
                        $foundFirst = true;
                    }
                    $currentBalance = $amount;
                }
            }

            $accountNumber = null;
            if (preg_match('/(\d{4})/', $description, $matches)) {
                $accountNumber = $matches[1];
            } elseif (preg_match('/\d{3,}/', $description, $matches)) {
                $accountNumber = $matches[0];
            }

            $institution = $this->guessInstitution($description);
            $ownership = $this->guessOwnership($description, $name);
            $accountType = $this->guessAccountType($description, $name);

            $name = trim($name);
            if (empty($name))
                continue;

            $account = Account::updateOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                [
                    'currency' => $currency,
                    'institution' => $institution,
                    'account_number' => $accountNumber,
                    'current_balance' => $currentBalance,
                    'opening_balance' => $openingBalance,
                    'notes' => "Imported from CSV. Original Description: $description",
                    'is_active' => true,
                    'ownership' => $ownership,
                    'account_type' => $accountType,
                ]
            );
            $processedAccounts[$name] = $account;
            $accountCount++;
        }

        $this->info("Imported $accountCount accounts.");

        if (!$this->option('with-transactions')) {
            fclose($file);
            return;
        }

        // --- PHASE 2: TRANSACTIONS ---
        $this->info("Phase 2: Importing Transactions...");

        // We broke out of the loop at "Movimientos". 
        // The next line should contain headers for the transaction block?
        // Let's verify structure. The file has "Movimientos" line, then Header line.
        // If we broke on "Movimientos", we need to consume the next "Header" line.
        // Actually, let's just loop and skip until we find 'ref', 'cuenta' again.

        $inTransactionBlock = false;

        while (($row = fgetcsv($file)) !== false) {
            // Find header for transactions
            if ($row[1] == 'ref' && $row[2] == 'cuenta') {
                $inTransactionBlock = true;
                $this->info("Found Transaction Headers.");
                continue;
            }

            if (!$inTransactionBlock)
                continue;

            // Process Transaction Rows
            if (count($row) < 5)
                continue;

            $name = trim($row[2]);
            if (empty($name))
                continue;

            if (!isset($processedAccounts[$name])) {
                // Maybe warn?
                continue;
            }

            $account = $processedAccounts[$name];

            // Iterate date columns to find non-zero transactions
            foreach ($dateMap as $idx => $date) {
                if (!isset($row[$idx]))
                    continue;

                $val = trim($row[$idx]);
                if ($val === '')
                    continue;

                $cleanVal = preg_replace('/[^\d.-]/', '', $val);
                if (!is_numeric($cleanVal))
                    continue;

                $amount = (float) $cleanVal;

                // Only import non-zero transactions
                if (abs($amount) < 0.01)
                    continue;

                // Check if duplicate?
                // Simple check: Account + Date + Amount
                $exists = Transaction::where('account_id', $account->id)
                    ->whereDate('transaction_date', $date)
                    ->where('amount', $amount)
                    ->exists();

                if (!$exists) {
                    Transaction::create([
                        'user_id' => $user->id,
                        'account_id' => $account->id,
                        'category_id' => null, // Uncategorized
                        'currency' => $account->currency, // Inherit from account
                        'transaction_date' => $date,
                        'amount' => $amount,
                        'description' => 'Imported Transaction', // Generic
                        'type' => $amount < 0 ? 'expense' : 'income',
                        'status' => 'completed',
                    ]);
                    $transactionCount++;
                }
            }
        }

        fclose($file);
        $this->info("Imported $transactionCount transactions.");
    }

    private function guessInstitution($description)
    {
        if (stripos($description, 'ING') !== false)
            return 'ING';
        if (stripos($description, 'Bankwest') !== false)
            return 'Bankwest';
        if (stripos($description, 'CommBank') !== false || stripos($description, 'CBA') !== false)
            return 'Commonwealth Bank';
        if (stripos($description, 'HSBC') !== false)
            return 'HSBC';
        if (stripos($description, 'PayPal') !== false)
            return 'PayPal';
        if (stripos($description, 'N26') !== false)
            return 'N26';
        if (stripos($description, 'Wise') !== false)
            return 'Wise';
        if (stripos($description, 'Revolut') !== false)
            return 'Revolut';
        if (stripos($description, 'Cash') !== false)
            return 'Cash';
        return $description;
    }

    private function guessOwnership($description, $name)
    {
        $text = $description . ' ' . $name;
        if (stripos($text, 'N26') !== false)
            return 'gupi';
        if (stripos($text, 'Gupi') !== false)
            return 'gupi';
        if (stripos($text, 'Shared') !== false)
            return 'shared';
        if (stripos($text, 'Joint') !== false)
            return 'shared';

        return 'buda'; // Default
    }

    private function guessAccountType($description, $name)
    {
        $text = $description . ' ' . $name;

        if (stripos($text, 'Cash') !== false)
            return 'cash';
        if (stripos($text, 'Travel') !== false)
            return 'travel_money';
        if (stripos($text, 'Wise') !== false)
            return 'travel_money';
        if (stripos($text, 'Revolut') !== false)
            return 'travel_money';
        if (stripos($text, 'Savings') !== false)
            return 'savings';
        if (stripos($text, 'Pocket') !== false)
            return 'savings';
        if (stripos($text, 'Credit') !== false)
            return 'credit_card';
        if (stripos($text, 'Investment') !== false)
            return 'investment';
        if (stripos($text, 'Super') !== false)
            return 'investment';

        return 'bank';
    }
}
