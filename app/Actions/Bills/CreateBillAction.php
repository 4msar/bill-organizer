<?php

namespace App\Actions\Bills;

use App\Contracts\Action;
use App\Models\Bill;

final class CreateBillAction implements Action
{
    /**
     * Create a new bill
     *
     * @param  array  $data
     */
    public function execute(mixed ...$params): Bill
    {
        $data = $params[0];

        return Bill::create($data + [
            'team_id' => $data['team_id'] ?? active_team_id(),
        ]);
    }
}
