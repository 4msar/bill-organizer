<?php

namespace App\Actions\Payments;

use App\Contracts\Action;
use App\Models\Bill;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

final class DeletePaymentAction implements Action
{
    /**
     * Delete a transaction and handle related cleanup
     *
     * Deletes the transaction, its attachment, and updates bill status if needed
     *
     * @param  Transaction  $transaction
     */
    public function execute(mixed ...$params): void
    {
        $transaction = $params[0];

        // Delete attachment if exists
        if ($transaction->attachment && Storage::exists($transaction->attachment)) {
            Storage::delete($transaction->attachment);
        }

        $billId = $transaction->bill_id;
        $transaction->delete();

        // If this was the only transaction for this bill, set bill back to unpaid
        $bill = Bill::find($billId);
        if ($bill && $bill->transactions()->count() === 0) {
            $bill->update(['status' => 'unpaid']);
        }
    }
}
