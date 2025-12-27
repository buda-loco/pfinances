<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class SeedCategories extends Command
{
    protected $signature = 'seed:categories';
    protected $description = 'Seed expense categories';

    public function handle()
    {
        $categories = [
            ['name' => '🏦 International Fees', 'code' => 'INTFEE', 'type' => 'expense'],
            ['name' => '📷 Work Equipment', 'code' => 'GEAR', 'type' => 'expense'],
            ['name' => '⛽️ Petrol', 'code' => 'PETROL', 'type' => 'expense'],
            ['name' => '🅿️ Parking', 'code' => 'PARKING', 'type' => 'expense'],
            ['name' => '🚎 Public Transportation', 'code' => 'PTRANS', 'type' => 'expense'],
            ['name' => '🛴 Bikes and Scooters', 'code' => 'SCOOTS', 'type' => 'expense'],
            ['name' => '📱 Mobile Phone', 'code' => 'MOBILE', 'type' => 'expense'],
            ['name' => '🧼 Laundry & Home Services', 'code' => 'CLEAN', 'type' => 'expense'],
            ['name' => '💐 Corporate Gifts', 'code' => 'GIFTS', 'type' => 'expense'],
            ['name' => '💸 Cash Movements', 'code' => 'CASHW', 'type' => 'transfer'],
            ['name' => '🍔 Fast Food', 'code' => 'FFOOD', 'type' => 'expense'],
            ['name' => '🛍️ Shopping', 'code' => 'SHOPS', 'type' => 'expense'],
            ['name' => '👨🏻‍💻 Accounting costs', 'code' => 'ACCOUNTING', 'type' => 'expense'],
            ['name' => '📺 Subscriptions', 'code' => 'SUBSC', 'type' => 'expense'],
            ['name' => '💸 Loans & Repayments', 'code' => 'LOANS', 'type' => 'expense'],
            ['name' => '🥡 Deliveries', 'code' => 'DELIV', 'type' => 'expense'],
            ['name' => '💻 Work Software/Subs', 'code' => 'SOFTW', 'type' => 'expense'],
            ['name' => '🧖‍♀️ Spa & Grooming', 'code' => 'GROOM', 'type' => 'expense'],
            ['name' => '🍺 Alcohol & Pubs', 'code' => 'PUBS', 'type' => 'expense'],
            ['name' => '🎭 Theatre/shows', 'code' => 'SHOWS', 'type' => 'expense'],
            ['name' => '💱 Currency exchange', 'code' => 'CURRX', 'type' => 'expense'],
            ['name' => '😷 Travel Insurance', 'code' => 'TINSU', 'type' => 'expense'],
            ['name' => '🚕 Uber/taxis', 'code' => 'UBERS', 'type' => 'expense'],
            ['name' => '🛒 Groceries', 'code' => 'GROCERY', 'type' => 'expense'],
            ['name' => '🎯 Tourist Activities', 'code' => 'TOURIS', 'type' => 'expense'],
            ['name' => '🏪 Mini-mart', 'code' => 'MINIMART', 'type' => 'expense'],
            ['name' => '✈️ Flights', 'code' => 'FLY', 'type' => 'expense'],
            ['name' => '☕️ Cafe & Dessert', 'code' => 'CAFE', 'type' => 'expense'],
            ['name' => '🍽️ Restaurants', 'code' => 'RESTAU', 'type' => 'expense'],
            ['name' => '🏨 Accommodation', 'code' => 'HOTELS', 'type' => 'expense'],
            ['name' => '🛳️ Cruises', 'code' => 'CRUISE', 'type' => 'expense'],
            ['name' => '🧾 TAX2425', 'code' => 'TX2425', 'type' => 'expense'],
            ['name' => '🔄 Internal Transfer', 'code' => 'XXINTER', 'type' => 'transfer'],
        ];

        $count = 0;
        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['code' => $cat['code']],
                [
                    'name' => $cat['name'],
                    'category_type' => $cat['type'],
                    'is_active' => true,
                ]
            );
            $count++;
        }

        $this->info("✅ Created/updated {$count} categories!");
        return 0;
    }
}
