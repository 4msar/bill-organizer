<?php

namespace App\Actions\Reporting;

use App\Contracts\Action;
use App\Models\Bill;
use App\Models\Transaction;

final class GenerateLifetimeStatsAction implements Action
{
    /**
     * Generate lifetime statistics
     */
    public function execute(mixed ...$params): array
    {
        return [
            'total_bills_count' => Bill::count(),
            'paid_bills_count' => Bill::paid()->count(),
            'unpaid_bills_count' => Bill::unpaid()->count(),
            'total_bills_amount' => Bill::sum('amount'),
            'total_paid_amount' => Transaction::sum('amount'),
            'total_unpaid_amount' => Bill::unpaid()->sum('amount'),
            'total_expenses' => Transaction::sum('amount'),
        ];
    }
}
