<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportTransactions extends Command
{
    protected $signature = 'import:transactions';
    protected $description = 'Import transactions from transactions CSV';

    public function handle()
    {
        // Try to find the transaction file
        $files = File::glob(base_path('../Source data/transactions*.csv'));
        if (empty($files)) {
            $this->error("No transaction CSV found in Source data.");
            return;
        }

        $csvPath = $files[0];
        $this->info("Reading transactions from: $csvPath");

        $file = fopen($csvPath, 'r');
        // Read header and trim whitespace/BOM
        $header = array_map(function ($h) {
            return trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF");
        }, fgetcsv($file));

        $this->info("Headers found: " . implode(', ', $header));

        // Map header columns to indices
        $colMap = array_flip($header);

        $user = User::first();
        if (!$user) {
            $this->error('No user found.');
            return;
        }

        $count = 0;
        $skipped = 0;

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < count($header))
                continue;

            $data = [];
            foreach ($colMap as $name => $index) {
                $data[$name] = $row[$index] ?? null;
            }

            // Find Account
            $accountName = trim($data['account_name'] ?? '');
            $accountNumber = trim($data['account_number'] ?? '');
            $providerName = trim($data['provider_name'] ?? '');
            $currency = $data['currency'] ?? 'AUD';

            // 1. Try exact name match
            $account = Account::where('name', $accountName)->first();

            // 2. Try matching by account number (fuzzy)
            if (!$account && $accountNumber) {
                // Remove spaces, 'x', 'X', masked chars
                $searchNum = str_replace(['x', 'X', ' '], '', $accountNumber);
                if (strlen($searchNum) > 3) {
                    $account = Account::where('account_number', 'like', "%$searchNum")->first();
                }
            }

            // 3. Try aliases/provider mapping
            if (!$account) {
                if (stripos($accountName, 'Bankwest') !== false) {
                    $account = Account::where('institution', 'Bankwest')->first();
                } elseif (stripos($accountName, 'Everyday Global') !== false) {
                    $account = Account::where('institution', 'HSBC')->first();
                } elseif (stripos($accountName, 'Travel Money Card') !== false) {
                    // CommBank Travel Money Card - needs currency match
                    $account = Account::where('institution', 'Commonwealth Bank')
                        ->where('currency', $currency)
                        ->where('name', 'like', '%Travel%')
                        ->first();
                } elseif (stripos($providerName, 'CommBank') !== false || stripos($accountName, 'Smart Access') !== false || stripos($accountName, 'GoalSaver') !== false) {
                    // Generic CommBank match
                    $account = Account::where('institution', 'Commonwealth Bank')
                        ->where('currency', $currency)
                        ->first();
                } elseif (stripos($accountName, 'Mercado pago') !== false) {
                    $account = Account::where('name', 'like', '%Mercado pago%')->first();
                } elseif (stripos($accountName, 'Splitwise') !== false) {
                    $account = Account::where('name', 'like', '%Splitwise%')
                        ->where('currency', $currency)
                        ->first();
                } elseif (stripos($accountName, 'Orange Everyday') !== false) {
                    $account = Account::where('institution', 'ING')->where('name', 'like', '%Daily%')->first();
                } elseif (stripos($accountName, 'Savings Maximiser') !== false) {
                    $account = Account::where('institution', 'ING')->where('name', 'like', '%Sav%')->first();
                } elseif (stripos($accountName, 'COP') !== false) {
                    $account = Account::where('currency', 'COP')->where('name', 'like', '%Cash%')->first();
                } elseif (stripos($accountName, 'EUR') !== false) {
                    $account = Account::where('currency', 'EUR')->where('name', 'like', '%Cash%')->first();
                } elseif (stripos($accountName, 'ARS') !== false) {
                    $account = Account::where('currency', 'ARS')->where('name', 'like', '%Cash%')->first();
                } elseif (stripos($accountName, 'USD') !== false) {
                    $account = Account::where('currency', 'USD')->where('name', 'like', '%Cash%')->first();
                }
            }

            if (!$account) {
                // $this->warn("Account not found for: $accountName ($accountNumber) [$providerName] $currency. Skipping transaction {$data['transaction_id']}");
                $skipped++;
                continue;
            }

            // Parse Dates
            try {
                $transactionDate = Carbon::parse($data['transaction_date']);
                $postedDate = $data['posted_date'] ? Carbon::parse($data['posted_date']) : null;
            } catch (\Exception $e) {
                // $this->warn("Invalid date for tx {$data['transaction_id']}");
                continue;
            }

            // Find Project
            $projectId = null;
            // Prioritize separate "Project" column, fallback to "lugar tag"/"user_tags"
            $projectNameFromCsv = $data['Project'] ?? null;
            $userTags = $data['lugar tag'] ?? $data['user_tags'] ?? '';

            $searchString = $projectNameFromCsv ?: $userTags;

            if (!empty($searchString)) {
                // Get all projects if not already cached
                static $allProjects = null;
                if ($allProjects === null) {
                    $allProjects = \App\Models\Project::all();
                }

                foreach ($allProjects as $project) {
                    // Check direct match first (fastest)
                    if ($project->name === $searchString) {
                        $projectId = $project->id;
                        break;
                    }

                    // Fuzzy match (ignore emojis/spaces)
                    $normSearch = strtolower(preg_replace('/[^\p{L}\p{N}]/u', '', $searchString));
                    $normProj = strtolower(preg_replace('/[^\p{L}\p{N}]/u', '', $project->name));

                    if (str_contains($normSearch, $normProj) || str_contains($normProj, $normSearch)) {
                        $projectId = $project->id;
                        break;
                    }
                }
            }

            Transaction::updateOrCreate(
                ['external_id' => $data['transaction_id']],
                [
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'project_id' => $projectId,
                    'description' => $data['description'],
                    'user_description' => $data['user_description'],
                    'amount' => (float) $data['amount'],
                    'currency' => $data['currency'],
                    'transaction_date' => $transactionDate,
                    'posted_date' => $postedDate,
                    'credit_debit' => strtolower($data['credit_debit']),
                    'transaction_type' => $data['transaction_type'],
                    'merchant_name' => $data['merchant_name'] ?? null,
                    'budget_category' => $data['budget_category'] ?? null,
                    'notes_and_codes' => $data['notes y codigos'] ?? $data['notes'] ?? null,
                    'is_included' => strtolower($data['included'] ?? 'false') === 'true',
                ]
            );
            $count++;
        }

        fclose($file);
        $this->info("Imported $count transactions. Skipped $skipped.");
    }
}
