<?php

namespace App\Actions\Reporting;

use App\Contracts\Action;
use App\Models\Bill;
use App\Models\Transaction;
use Carbon\Carbon;

final class GenerateYearlyComparisonAction implements Action
{
    /**
     * Generate 3-year comparison
     */
    public function execute(mixed ...$params): array
    {
        $currentYear = Carbon::now()->year;
        $yearlyComparison = [];

        for ($i = 0; $i < 3; $i++) {
            $year = $currentYear - $i;
            $yearStart = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfYear();

            $yearlyComparison[] = [
                'year' => $year,
                'total_bills' => Bill::whereBetween('due_date', [$yearStart, $yearEnd])->count(),
                'paid_bills' => Bill::whereBetween('due_date', [$yearStart, $yearEnd])->paid()->count(),
                'bills_amount' => Bill::whereBetween('due_date', [$yearStart, $yearEnd])->sum('amount'),
                'paid_amount' => Transaction::whereBetween('payment_date', [$yearStart, $yearEnd])->sum('amount'),
            ];
        }

        return $yearlyComparison;
    }
}
