<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Account;

class FixAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-account-balances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes the balances of all accounts by recalculating them based on their transactions.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix account balances...');

        $accounts = Account::with('transactions')->get();

        foreach ($accounts as $account) {
            $calculatedBalance = round($account->transactions->sum('amount'), 2);
            $currentBalance = round($account->current_balance, 2);

            if ($currentBalance !== $calculatedBalance) {
                $this->line(sprintf('Account "%s" (ID: %d) has a mismatched balance. Stored: %s, Calculated: %s. Updating...', $account->name, $account->id, $account->current_balance, $calculatedBalance));
                $account->current_balance = $calculatedBalance;
                $account->save();
            }
        }

        $this->info('Account balances fixed successfully.');
    }
}
