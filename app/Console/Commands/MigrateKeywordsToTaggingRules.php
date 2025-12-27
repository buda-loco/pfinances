<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\TaggingRule;
use Illuminate\Console\Command;

class MigrateKeywordsToTaggingRules extends Command
{
    protected $signature = 'migrate:tagging-rules';
    protected $description = 'Migrate keyword map to tagging rules table';

    // Keyword mapping from AutoCategorizeTransactions
    protected $keywordMap = [
        'UBER' => 'UBERS',
        'TAXI' => 'UBERS',
        'RIDESHARE' => 'UBERS',
        'CAFE' => 'CAFE',
        'COFFEE' => 'CAFE',
        'STARBUCKS' => 'CAFE',
        'RESTAURANT' => 'RESTAU',
        'DINING' => 'RESTAU',
        'HOTEL' => 'HOTELS',
        'ACCOMMODATION' => 'HOTELS',
        'AIRBNB' => 'HOTELS',
        'FLIGHT' => 'FLY',
        'AIRLINE' => 'FLY',
        'QANTAS' => 'FLY',
        'VIRGIN' => 'FLY',
        'JETSTAR' => 'FLY',
        'GROCERY' => 'GROCERY',
        'SUPERMARKET' => 'GROCERY',
        'COLES' => 'GROCERY',
        'WOOLWORTHS' => 'GROCERY',
        'PETROL' => 'PETROL',
        'FUEL' => 'PETROL',
        'GAS STATION' => 'PETROL',
        'PARKING' => 'PARKING',
        'MCDONALDS' => 'FFOOD',
        'KFC' => 'FFOOD',
        'BURGER' => 'FFOOD',
        'PIZZA' => 'FFOOD',
        'FAST FOOD' => 'FFOOD',
        'PUB' => 'PUBS',
        'BAR' => 'PUBS',
        'BREWERY' => 'PUBS',
        'LIQUOR' => 'PUBS',
        'PHARMACY' => 'GROOM',
        'CHEMIST' => 'GROOM',
        'TRANSFER' => 'XXINTER',
        'INTERNAL' => 'XXINTER',
        'WISE' => 'CASHW',
        'PAYPAL' => 'CASHW',
        'ATM' => 'CASHW',
        'MOBILE' => 'MOBILE',
        'PHONE' => 'MOBILE',
        'VODAFONE' => 'MOBILE',
        'OPTUS' => 'MOBILE',
        'TELSTRA' => 'MOBILE',
    ];

    public function handle()
    {
        $this->info('Migrating keywords to tagging rules...');

        // Group keywords by category code
        $grouped = [];
        foreach ($this->keywordMap as $keyword => $categoryCode) {
            if (!isset($grouped[$categoryCode])) {
                $grouped[$categoryCode] = [];
            }
            $grouped[$categoryCode][] = $keyword;
        }

        $count = 0;
        $progressBar = $this->output->createProgressBar(count($grouped));

        foreach ($grouped as $categoryCode => $keywords) {
            $category = Category::where('code', $categoryCode)->first();

            if (!$category) {
                $this->warn("Category not found for code: {$categoryCode}");
                continue;
            }

            // Create regex pattern from keywords (case-insensitive)
            $pattern = '/(' . implode('|', array_map('preg_quote', $keywords)) . ')/i';

            TaggingRule::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'field' => 'description',
                ],
                [
                    'name' => $category->name . ' - Auto',
                    'pattern' => $pattern,
                    'priority' => 10, // Default priority
                    'is_active' => true,
                ]
            );

            $count++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✅ Created/updated {$count} tagging rules!");

        return 0;
    }
}
