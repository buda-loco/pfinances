<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\TaggingRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportKeywords extends Command
{
    protected $signature = 'import:keywords';
    protected $description = 'Import categories and keywords from keywords CSV';

    public function handle()
    {
        $csvPath = base_path('../Source data/keywords.csv');
        if (!File::exists($csvPath)) {
            $this->error("Keywords CSV not found.");
            return;
        }

        $this->info("Reading keywords from: $csvPath");
        $file = fopen($csvPath, 'r');

        // Locate header row (it's around line 8 usually)
        $header = [];
        $colMap = [];

        while (($row = fgetcsv($file)) !== false) {
            // Check for known headers
            if (in_array('Category', $row) && in_array('Code', $row)) {
                $header = $row;
                $colMap = array_flip($header);
                break;
            }
        }

        if (empty($colMap)) {
            $this->error("Could not find header row in keywords.csv");
            fclose($file);
            return;
        }

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            // Get values using map
            $categoryName = trim($row[$colMap['Category']] ?? '');
            $code = trim($row[$colMap['Code']] ?? '');
            $groupName = trim($row[$colMap['Category Group']] ?? '');
            // $type = trim($row[$colMap['Type']] ?? ''); // Income/Expense/etc

            if (empty($categoryName) || empty($code))
                continue;

            // 1. Create/Find Category Group
            $group = null;
            if (!empty($groupName)) {
                // Generate a code for the group if needed
                $groupCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $groupName), 0, 10));

                $group = CategoryGroup::firstOrCreate(
                    ['name' => $groupName],
                    ['code' => $groupCode]
                );
            }

            // 2. Create/Find Category
            $category = Category::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $categoryName,
                    'group_id' => $group ? $group->id : null,
                    'color' => '#808080', // Default color
                    'icon' => 'tag', // Default icon
                ]
            );

            // 3. Create Tagging Rule (if logical)
            // The "Code" in keywords.csv seems to be the unique ID for the category, 
            // but also potentially a keyword to look for? 
            // Actually, usually "Code" is just the ID. 
            // The "Frollo" column might contain the pattern? Or user description?
            // In the example: Category="Internal Transfer", Code="XXINTER", Frollo="Transfer between accounts"
            // XXINTER looks like a tag.

            // Let's create a rule that matches the CODE itself in "notes_and_codes" or description
            // pattern: /\bCODE\b/i

            TaggingRule::updateOrCreate(
                ['name' => "Rule for $code"],
                [
                    'pattern' => "/\b" . preg_quote($code, '/') . "\b/i",
                    'category_id' => $category->id,
                    'field' => 'notes_and_codes', // It seems codes are added to notes/tags
                    'priority' => 10,
                ]
            );

            // Also maybe Frollo column has keywords? "Transfer between accounts"
            $frollo = trim($row[$colMap['Frollo']] ?? '');
            if (!empty($frollo) && $frollo !== $categoryName) {
                TaggingRule::updateOrCreate(
                    ['name' => "Frollo match for $code"],
                    [
                        'pattern' => "/" . preg_quote($frollo, '/') . "/i",
                        'category_id' => $category->id,
                        'field' => 'description',
                        'priority' => 5,
                    ]
                );
            }

            $count++;
        }

        fclose($file);
        $this->info("Imported $count categories/rules.");
    }
}
