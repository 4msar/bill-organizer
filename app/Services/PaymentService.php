<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Transaction;
use App\Queries\Transactions\GetTransactionsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class PaymentService
{
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
                $attachmentPath = $this->uploadPaymentAttachment($paymentData['attachment']);
            }

            // Create transaction
            $transaction = Transaction::create([
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
                $this->handleRecurringBillPayment($bill, $paymentData['nextDueDate'] ?? null);
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

    /**
     * Handle recurring bill payment logic
     *
     * Updates bill status and due date for recurring bills
     */
    public function handleRecurringBillPayment(Bill $bill, mixed $nextDueDate = null): Bill
    {
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

    /**
     * Upload payment attachment to storage
     */
    public function uploadPaymentAttachment(mixed $file): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        return $file->storePublicly('attachments');
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
