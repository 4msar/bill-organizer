<?php

namespace App\Actions\Reporting;

use App\Contracts\Action;
use App\Models\Bill;
use App\Models\Transaction;
use Carbon\Carbon;

final class GenerateMonthlyTrendAction implements Action
{
    /**
     * Generate 12-month trend analysis
     */
    public function execute(mixed ...$params): array
    {
        $monthlyTrend = [];
        $startOfTrend = Carbon::now()->subMonths(11)->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $monthDate = $startOfTrend->copy()->addMonths($i);
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();

            $monthlyTrend[] = [
                'month' => $monthDate->format('M Y'),
                'total_bills' => Bill::whereBetween('due_date', [$monthStart, $monthEnd])->count(),
                'paid_bills' => Bill::whereBetween('due_date', [$monthStart, $monthEnd])->paid()->count(),
                'bills_amount' => Bill::whereBetween('due_date', [$monthStart, $monthEnd])->sum('amount'),
                'paid_amount' => Transaction::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount'),
            ];
        }

        return $monthlyTrend;
    }
}
