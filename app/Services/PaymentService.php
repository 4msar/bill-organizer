<?php

namespace App\Services;

use App\Actions\Payments\DeletePaymentAction;
use App\Actions\Payments\HandleRecurringBillPaymentAction;
use App\Actions\Payments\RecordPaymentAction;
use App\Actions\Payments\UploadPaymentAttachmentAction;
use App\Models\Bill;
use App\Models\Transaction;
use App\Queries\Transactions\GetTransactionsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class PaymentService
{
    public function __construct(
        private readonly UploadPaymentAttachmentAction $uploadAttachment,
        private readonly RecordPaymentAction $recordPayment,
        private readonly HandleRecurringBillPaymentAction $handleRecurring,
        private readonly DeletePaymentAction $deletePayment,
    ) {}

    /**
     * Record a new payment for a bill
     */
    public function recordPayment(
        Bill $bill,
        array $paymentData,
        bool $updateDueDate = false
    ): Transaction {
        return DB::transaction(function () use ($bill, $paymentData, $updateDueDate) {
            // Upload attachment if present
            $attachmentPath = null;
            if (isset($paymentData['attachment'])) {
                $attachmentPath = $this->uploadAttachment->execute($paymentData['attachment']);
            }

            // Create transaction
            $transaction = $this->recordPayment->execute([
                'team_id' => active_team_id(),
                'user_id' => Auth::id(),
                'bill_id' => $bill->id,
                'amount' => $paymentData['amount'],
                'payment_date' => $paymentData['payment_date'],
                'payment_method' => $paymentData['payment_method'] ?? null,
                'notes' => $paymentData['notes'] ?? null,
                'attachment' => $attachmentPath,
            ]);

            // Handle bill status and recurring logic
            if ($bill->is_recurring && $updateDueDate) {
                $this->handleRecurring->execute($bill, $paymentData['nextDueDate'] ?? null);
            } else {
                $bill->status = 'paid';
                $bill->save();
            }

            return $transaction;
        });
    }

    /**
     * Delete a payment transaction
     */
    public function deletePayment(Transaction $transaction): void
    {
        $this->deletePayment->execute($transaction);
    }

    /**
     * Generate receipt data for a transaction
     */
    public function generateReceipt(Transaction $transaction): array
    {
        $transaction->load(['bill', 'bill.category', 'user']);

        return [
            'transaction' => $transaction,
        ];
    }

    /**
     * Get transactions with filters
     */
    public function getTransactionsWithFilters(array $filters): LengthAwarePaginator
    {
        $query = new GetTransactionsQuery(
            search: $filters['search'] ?? null,
            billId: $filters['bill_id'] ?? null,
            paymentMethod: $filters['payment_method'] ?? null,
            dateFrom: $filters['date_from'] ?? null,
            dateTo: $filters['date_to'] ?? null,
            minAmount: isset($filters['min_amount']) ? (float) $filters['min_amount'] : null,
            maxAmount: isset($filters['max_amount']) ? (float) $filters['max_amount'] : null,
            sortBy: $filters['sort_by'] ?? 'payment_date',
            sortDirection: $filters['sort_direction'] ?? 'desc',
            perPage: isset($filters['per_page']) ? (int) $filters['per_page'] : 15,
        );

        return $query->get();
    }
}
