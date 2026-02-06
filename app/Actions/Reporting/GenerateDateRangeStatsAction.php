<?php

namespace App\Actions\Reporting;

use App\Contracts\Action;
use App\Models\Bill;
use App\Models\Transaction;
use Carbon\Carbon;

final class GenerateDateRangeStatsAction implements Action
{
    /**
     * Generate statistics for a specific date range
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     */
    public function execute(mixed ...$params): array
    {
        $startDate = $params[0];
        $endDate = $params[1];

        $billsInRange = Bill::whereBetween('due_date', [$startDate, $endDate]);
        $transactionsInRange = Transaction::whereBetween('payment_date', [$startDate, $endDate]);

        return [
            'total_bills_count' => $billsInRange->count(),
            'paid_bills_count' => $billsInRange->clone()->paid()->count(),
            'unpaid_bills_count' => $billsInRange->clone()->unpaid()->count(),
            'total_bills_amount' => $billsInRange->clone()->sum('amount'),
            'paid_amount' => $transactionsInRange->clone()->sum('amount'),
            'unpaid_amount' => $billsInRange->clone()->unpaid()->sum('amount'),
            'total_expenses' => $transactionsInRange->clone()->sum('amount'),
        ];
    }
}
