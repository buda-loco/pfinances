<?php

namespace App\Console\Commands;

use App\Models\TaggingRule;
use App\Models\Transaction;
use Illuminate\Console\Command;

class AutoCategorizeTransactions extends Command
{
    protected $signature = 'transactions:auto-categorize {--all : Categorize all transactions, not just uncategorized ones}';
    protected $description = 'Automatically categorize transactions based on tagging rules';

    public function handle()
    {
        $categorizeAll = $this->option('all');

        // Get transactions to categorize
        $query = Transaction::query();
        if (!$categorizeAll) {
            $query->whereNull('category_id');
        }

        $transactions = $query->get();
        $this->info("Processing {$transactions->count()} transactions...");

        // Get active tagging rules ordered by priority
        $taggingRules = TaggingRule::with(['category', 'project'])
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        if ($taggingRules->isEmpty()) {
            $this->warn('No active tagging rules found. Run "php artisan migrate:tagging-rules" first.');
            return 1;
        }

        $categorized = 0;
        $progressBar = $this->output->createProgressBar($transactions->count());

        foreach ($transactions as $transaction) {
            $matched = false;

            foreach ($taggingRules as $rule) {
                // Get the field value to match against
                $fieldValue = match($rule->field) {
                    'merchant_name' => $transaction->merchant_name ?? '',
                    'notes_and_codes' => $transaction->notes_and_codes ?? '',
                    default => $transaction->description ?? '',
                };

                // Try to match the pattern
                if ($rule->matches($fieldValue)) {
                    $updates = [];

                    if ($rule->category_id) {
                        $updates['category_id'] = $rule->category_id;
                        $updates['code'] = $rule->category->code;
                    }

                    if ($rule->project_id) {
                        $updates['project_id'] = $rule->project_id;
                    }

                    if (!empty($updates)) {
                        $transaction->update($updates);
                        $categorized++;
                        $matched = true;
                        break; // Stop after first match (highest priority wins)
                    }
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✅ Categorized {$categorized} out of {$transactions->count()} transactions!");

        return 0;
    }
}
