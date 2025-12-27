<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Account;
use App\Models\AccountBalance;
use Carbon\Carbon;
use App\Console\Commands\DataIntegrityCheck;
use App\Console\Commands\FixAccountBalances;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:data-integrity-check', function () {
    $command = new DataIntegrityCheck();
    $command->setOutput($this->getOutput());
    $command->handle();
})->purpose('Performs a data integrity check on the database.');

Artisan::command('app:fix-account-balances', function () {
    $command = new FixAccountBalances();
    $command->setOutput($this->getOutput());
    $command->handle();
})->purpose('Fixes the balances of all accounts by recalculating them based on their transactions.');


Artisan::command('import:balances-adhoc', function () {
    $this->info("Starting ad-hoc import...");

    // Truncate to avoid issues first
    \App\Models\AccountBalance::truncate();
    $this->info("Truncated account_balances table.");

    // Go up one level from 'app' folder to find 'Source data'
    $csvFile = base_path('../Source data/accounts.csv');

    if (!file_exists($csvFile)) {
        $this->error("File not found: $csvFile");
        return;
    }

    $handle = fopen($csvFile, 'r');
    if (!$handle) {
        $this->error("Could not open file.");
        return;
    }

    fgetcsv($handle); // Skip Row 1
    $headerLine = fgetcsv($handle); // Row 2

    if (!$headerLine) {
        $this->error("CSV is empty or malformed.");
        fclose($handle);
        return;
    }

    $dateColumns = [];
    foreach ($headerLine as $index => $col) {
        $col = trim($col);
        if (empty($col))
            continue;
        try {
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $col)) {
                $dateColumns[$index] = Carbon::createFromFormat('d/m/Y', $col)->startOfDay();
            }
        } catch (\Exception $e) {
        }
    }

    $this->info("Found " . count($dateColumns) . " date columns.");
    $accountsUpdated = 0;
    $balancesInserted = 0;

    while (($row = fgetcsv($handle)) !== false) {
        $accountName = $row[2] ?? null;
        $currency = $row[3] ?? null;

        if (empty($accountName) || empty($currency))
            continue;

        $account = Account::where('name', $accountName)->first();
        if (!$account) {
            // Try trimming or simple normalization
            $account = Account::where('name', trim($accountName))->first();
            if (!$account) {
                // Try removing emojis for matching? 
                // Actually, let's just log and skip for now for safety/speed
                // $this->warn("Skipping unknown account: $accountName");
                continue;
            }
        }

        $accountsUpdated++;
        $monthlySnapshots = [];

        foreach ($dateColumns as $index => $date) {
            $val = $row[index] ?? null;
            if ($val === null || $val === '')
                continue;

            $val = str_replace([',', ' ', 'AUD', 'USD', 'EUR', 'JPY', 'KRW', 'HKD', 'VND', 'FJD'], '', $val);
            if (!is_numeric($val))
                continue;

            $balance = (float) $val;
            $monthKey = $date->format('Y-m');
            $monthlySnapshots[$monthKey] = [
                'date' => $date,
                'balance' => $balance
            ];
        }

        foreach ($monthlySnapshots as $snapshot) {
            AccountBalance::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'date' => $snapshot['date']->format('Y-m-d'),
                ],
                [
                    'balance' => $snapshot['balance'],
                    'currency' => $currency
                ]
            );
            $balancesInserted++;
        }
    }

    fclose($handle);
    $this->info("Import complete. Processed $accountsUpdated accounts. Records: $balancesInserted.");
})->purpose('Ad-hoc import for account balances');
