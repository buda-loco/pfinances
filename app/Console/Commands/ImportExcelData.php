<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ExcelImportService;
use Illuminate\Console\Command;

class ImportExcelData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:excel
                            {file : Path to Excel file}
                            {--user=1 : User ID to assign imported data to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import financial data from Excel file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $userId = $this->option('user');

        // Validate file exists
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        // Get user
        $user = User::find($userId);
        if (!$user) {
            $this->error("User not found with ID: {$userId}");
            return 1;
        }

        $this->info("Starting import from: {$filePath}");
        $this->info("Importing data for user: {$user->name} ({$user->email})");

        // Create progress bar
        $this->newLine();

        // Run import
        $importer = new ExcelImportService($filePath, $user);
        $stats = $importer->import();

        // Display results
        $this->newLine();
        $this->info('Import completed!');
        $this->newLine();

        $this->table(
            ['Type', 'Count'],
            [
                ['Entity Types', $stats['entity_types']],
                ['Categories', $stats['categories']],
                ['Accounts', $stats['accounts']],
                ['Transactions', $stats['transactions']],
            ]
        );

        if (!empty($stats['errors'])) {
            $this->newLine();
            $this->error('Errors occurred during import:');
            foreach ($stats['errors'] as $error) {
                $this->error('  - ' . $error);
            }
            return 1;
        }

        $this->newLine();
        $this->info('✅ All data imported successfully!');

        return 0;
    }
}
