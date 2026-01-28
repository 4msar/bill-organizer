<?php

namespace App\Console\Commands;

use App\Models\Bill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateBillStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:update-statuses
                            {--bill-id=* : Specific bill IDs to update}
                            {--dry-run : Preview changes without updating}
                            {--show-details : Show detailed information about each change}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update bill statuses based on recurrence period and transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $billIds = $this->option('bill-id');
        $showDetails = $this->option('show-details');

        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }

        $this->info('Starting bill status update...');

        // Build the query
        $query = Bill::query()->withoutGlobalScopes();

        if (! empty($billIds)) {
            $query->whereIn('id', $billIds);
            $this->info('Filtering by bill IDs: ' . implode(', ', $billIds));
        }

        $bills = $query->get();
        $totalBills = $bills->count();
        $updatedCount = 0;
        $unchangedCount = 0;

        $this->info("Processing {$totalBills} bills...");

        $progressBar = $this->output->createProgressBar($totalBills);
        $progressBar->start();

        foreach ($bills as $bill) {
            $currentDbStatus = $bill->getAttributes()['status'] ?? 'unpaid';
            $calculatedStatus = $bill->calculateStatus();

            if ($currentDbStatus !== $calculatedStatus) {
                if ($showDetails || $isDryRun) {
                    $this->newLine();
                    $this->line("Bill #{$bill->id} - {$bill->title}");
                    $this->line("  Current: {$currentDbStatus} → Calculated: {$calculatedStatus}");

                    if ($bill->is_recurring) {
                        $this->line("  Recurring: {$bill->recurrence_period}");
                        $this->line("  Due Date: {$bill->due_date->format('Y-m-d')}");
                    }
                }

                if (! $isDryRun) {
                    // Update without triggering model events or observers
                    DB::table('bills')
                        ->where('id', $bill->id)
                        ->update([
                            'status' => $calculatedStatus,
                        ]);
                }

                $updatedCount++;
            } else {
                $unchangedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✓ Status update completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Bills Processed', $totalBills],
                ['Bills Updated', $updatedCount],
                ['Bills Unchanged', $unchangedCount],
            ]
        );

        if ($isDryRun) {
            $this->warn('This was a DRY RUN - no changes were made to the database');
            $this->info('Run without --dry-run to apply changes');
        }

        return Command::SUCCESS;
    }
}
