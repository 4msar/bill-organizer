<?php

namespace App\Console\Commands;

use App\Models\BillAutoTransaction;
use App\Models\Transaction;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class CreateAutoTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:create-auto-transactions
                            {--date= : Process date in Y-m-d format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create bill transactions from active auto transaction settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDate = $this->resolveTargetDate();
        $processedCount = 0;
        $skippedCount = 0;

        $this->info('Creating auto transactions for '.$targetDate->toDateString().'...');

        $autoTransactions = BillAutoTransaction::query()
            ->withoutGlobalScopes()
            ->where('is_active', true)
            ->whereHas('bill', function ($query) use ($targetDate) {
                $query->withoutGlobalScopes()->whereDate('due_date', $targetDate->toDateString());
            })
            ->with(['bill' => function ($query) {
                $query->withoutGlobalScopes();
            }])
            ->get();

        foreach ($autoTransactions as $autoTransaction) {
            $bill = $autoTransaction->bill;

            if (! $bill) {
                $skippedCount++;
                continue;
            }

            if (! $bill->is_recurring && $bill->getRawOriginal('status') === 'paid') {
                $skippedCount++;
                continue;
            }

            $alreadyCreated = Transaction::query()
                ->withoutGlobalScopes()
                ->where('bill_id', $bill->id)
                ->whereDate('payment_date', $targetDate->toDateString())
                ->exists();

            if ($alreadyCreated) {
                $skippedCount++;
                continue;
            }

            DB::transaction(function () use ($autoTransaction, $bill, $targetDate) {
                Transaction::create([
                    'team_id' => $bill->team_id,
                    'user_id' => $bill->user_id,
                    'bill_id' => $bill->id,
                    'amount' => $autoTransaction->amount,
                    'payment_date' => $targetDate->toDateString(),
                    'payment_method' => $autoTransaction->payment_method,
                    'notes' => $autoTransaction->notes,
                ]);

                if ($bill->is_recurring) {
                    app(PaymentService::class)->handleRecurringBillPayment($bill);
                } else {
                    $bill->update([
                        'status' => 'paid',
                    ]);
                }

                $autoTransaction->update([
                    'last_processed_date' => $targetDate->toDateString(),
                ]);
            });

            $processedCount++;
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Auto Transactions Processed', $processedCount],
                ['Auto Transactions Skipped', $skippedCount],
            ]
        );

        return Command::SUCCESS;
    }

    private function resolveTargetDate(): Carbon
    {
        $optionDate = $this->option('date');

        if (! is_string($optionDate) || trim($optionDate) === '') {
            return now()->startOfDay();
        }

        return Carbon::parse($optionDate)->startOfDay();
    }
}
