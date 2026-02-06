<?php

namespace App\Actions\Payments;

use App\Contracts\Action;
use App\Models\Transaction;

final class RecordPaymentAction implements Action
{
    /**
     * Create a new transaction record
     *
     * @param  array  $data  Transaction data
     */
    public function execute(mixed ...$params): Transaction
    {
        $data = $params[0] ?? [];

        return Transaction::create([
            'team_id' => $data['team_id'] ?? active_team_id(),
            'user_id' => $data['user_id'] ?? auth()->id(),
            'bill_id' => $data['bill_id'],
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'] ?? null,
            'notes' => $data['notes'] ?? null,
            'attachment' => $data['attachment'] ?? null,
        ]);
    }
}
