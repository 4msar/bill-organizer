<?php

namespace App\Actions\Reporting;

use App\Contracts\Action;
use App\Models\Transaction;
use Carbon\Carbon;

final class GeneratePaymentMethodBreakdownAction implements Action
{
    /**
     * Generate payment method breakdown for a date range
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     */
    public function execute(mixed ...$params): array
    {
        $startDate = $params[0];
        $endDate = $params[1];

        $paymentMethodBreakdown = Transaction::whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->payment_method ?? 'Unknown',
                    'count' => $item->count,
                    'total_amount' => $item->total_amount,
                ];
            })
            ->toArray();

        return $paymentMethodBreakdown;
    }
}
