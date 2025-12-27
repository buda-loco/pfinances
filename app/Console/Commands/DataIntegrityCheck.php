<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Account;

class DataIntegrityCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:data-integrity-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Performs a data integrity check on the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data integrity check...');

        $this->checkTransactionsWithoutAccount();
        $this->checkAccountBalances();

        $this->info('Data integrity check completed.');
    }

    protected function checkTransactionsWithoutAccount()
    {
        $this->info('Checking for transactions without an account...');
        $transactions = Transaction::whereDoesntHave('account')->get();

        if ($transactions->isEmpty()) {
            $this->info('No transactions without an account found.');
            return;
        }

        $this->warn(sprintf('%d transactions found without an account.', $transactions->count()));
        $this->table(['ID', 'Description', 'Amount'], $transactions->map(function ($transaction) {
            return [
                $transaction->id,
                $transaction->description,
                $transaction->amount,
            ];
        }));
    }

    protected function checkAccountBalances()
    {
        $this->info('Checking account balances...');
        $accounts = Account::with('transactions')->get();
        $mismatchedAccounts = [];

        foreach ($accounts as $account) {
            $calculatedBalance = round($account->transactions->sum('amount'), 2);
            $currentBalance = round($account->current_balance, 2);

            if ($currentBalance !== $calculatedBalance) {
                $mismatchedAccounts[] = [
                    'id' => $account->id,
                    'name' => $account->name,
                    'balance' => $account->current_balance,
                    'calculated_balance' => $calculatedBalance,
                ];
            }
        }

        if (empty($mismatchedAccounts)) {
            $this->info('All account balances are correct.');
            return;
        }

        $this->warn(sprintf('%d accounts found with mismatched balances.', count($mismatchedAccounts)));
        $this->table(['ID', 'Name', 'Balance', 'Calculated Balance'], $mismatchedAccounts);
    }
}
