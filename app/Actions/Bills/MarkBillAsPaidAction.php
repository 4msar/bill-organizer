<?php

namespace App\Actions\Bills;

use App\Contracts\Action;
use App\Models\Bill;

final class MarkBillAsPaidAction implements Action
{
    /**
     * Mark a bill as paid
     *
     * @param  Bill  $bill
     */
    public function execute(mixed ...$params): Bill
    {
        $bill = $params[0];

        $bill->markAsPaid();

        return $bill;
    }
}
