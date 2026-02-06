<?php

namespace App\Actions\Bills;

use App\Contracts\Action;
use App\Models\Bill;

final class UpdateBillAction implements Action
{
    /**
     * Update an existing bill
     *
     * @param  Bill  $bill
     * @param  array  $data
     */
    public function execute(mixed ...$params): Bill
    {
        $bill = $params[0];
        $data = $params[1];

        $bill->update($data);

        return $bill;
    }
}
