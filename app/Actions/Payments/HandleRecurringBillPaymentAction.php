<?php

namespace App\Actions\Payments;

use App\Contracts\Action;
use App\Models\Bill;
use Carbon\Carbon;

final class HandleRecurringBillPaymentAction implements Action
{
    /**
     * Handle recurring bill payment logic
     *
     * Updates bill status and due date for recurring bills
     *
     * @param  Bill  $bill  The bill to update
     * @param  Carbon|string|null  $nextDueDate  Optional next due date
     */
    public function execute(mixed ...$params): Bill
    {
        $bill = $params[0];
        $nextDueDate = $params[1] ?? null;

        if (! $bill->is_recurring) {
            $bill->status = 'paid';
            $bill->save();

            return $bill;
        }

        $calculatedNextDueDate = $bill->calculateNextDueDate($nextDueDate);

        if ($calculatedNextDueDate) {
            $bill->status = 'unpaid';
            $bill->due_date = $calculatedNextDueDate;
        } else {
            $bill->status = 'paid';
        }

        $bill->save();

        return $bill;
    }
}
