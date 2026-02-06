<?php

namespace App\Actions\Bills;

use App\Contracts\Action;
use App\Models\Bill;

final class DeleteBillAction implements Action
{
    /**
     * Delete a bill
     *
     * @param  Bill  $bill
     */
    public function execute(mixed ...$params): void
    {
        $bill = $params[0];

        $bill->delete();
    }
}
