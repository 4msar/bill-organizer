<?php

namespace App\Services;

use App\Enums\RecurrencePeriod;
use App\Models\Bill;
use App\Queries\Bills\GetBillsQuery;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class BillingService
{
    /**
     * Create a new bill
     */
    public function createBill(array $data): Bill
    {
        return Bill::create($data + [
            'team_id' => $data['team_id'] ?? active_team_id(),
        ]);
    }

    /**
     * Update an existing bill
     */
    public function updateBill(Bill $bill, array $data): Bill
    {
        $bill->update($data);

        return $bill;
    }

    /**
     * Delete a bill
     */
    public function deleteBill(Bill $bill): void
    {
        $bill->delete();
    }

    /**
     * Mark a bill as paid
     */
    public function markBillAsPaid(Bill $bill): Bill
    {
        $bill->markAsPaid();

        return $bill;
    }

    /**
     * Calculate the status based on recurrence period and transactions
     */
    public function calculateBillStatus(Bill $bill): string
    {
        $currentStatus = $bill->getAttributeValue('status') ?? 'unpaid';

        // For non-recurring bills, just return the current status
        if (! $bill->is_recurring || ! $bill->recurrence_period) {
            return $currentStatus;
        }

        $now = now();
        $dueDate = Carbon::parse($bill->due_date);

        // Check if we're in the current period for this bill
        $isCurrentPeriod = match ($bill->recurrence_period) {
            RecurrencePeriod::WEEKLY => $dueDate->isSameWeek($now),
            RecurrencePeriod::MONTHLY => $dueDate->isSameMonth($now),
            RecurrencePeriod::YEARLY => $dueDate->isSameYear($now),
            default => false,
        };

        // if the bill is in the current recurrence period
        // check for transactions to determine if it's paid or unpaid
        if ($isCurrentPeriod) {
            // In current period - check for recent transactions
            $hasTransaction = $bill->transactions()
                ->whereBetween('payment_date', [
                    $dueDate->copy()->startOfPeriod($bill->recurrence_period),
                    $dueDate->copy()->endOfPeriod($bill->recurrence_period),
                ])
                ->exists();

            if ($hasTransaction) {
                return 'paid';
            }

            /**
             * If it's yearly and not within 90 days of due date,
             * consider it paid to avoid unnecessary reminders.
             */
            if ($bill->recurrence_period === RecurrencePeriod::YEARLY && $dueDate->diffInDays($now) > 90) {
                return 'paid';
            }

            return 'unpaid';
        }

        // Not in current period - check if due date is in the past
        if ($dueDate->isBefore($now)) {
            return 'overdue';
        }

        // check if due date is in the future and not in current period
        // then it's considered paid for past periods
        if ($dueDate->isAfter($now)) {
            return 'paid';
        }

        return $currentStatus;
    }

    /**
     * Calculate next due date based on recurrence period
     */
    public function calculateNextDueDate(Bill $bill, ?string $dueDate = null): ?string
    {
        if (! $bill->is_recurring || ! $bill->recurrence_period) {
            return null;
        }

        if ($dueDate) {
            return Carbon::parse($dueDate)->format('Y-m-d');
        }

        $currentDueDate = Carbon::parse($bill->due_date);

        return match ($bill->recurrence_period) {
            RecurrencePeriod::WEEKLY => $currentDueDate->addWeek()->format('Y-m-d'),
            RecurrencePeriod::MONTHLY => $currentDueDate->addMonth()->format('Y-m-d'),
            RecurrencePeriod::YEARLY => $currentDueDate->addYear()->format('Y-m-d'),
            default => null,
        };
    }

    /**
     * Get bills with filters
     */
    public function getBillsWithFilters(array $filters): LengthAwarePaginator
    {
        $query = new GetBillsQuery(
            search: $filters['search'] ?? null,
            status: $filters['status'] ?? null,
            categoryId: $filters['category'] ?? null,
            date: $filters['date'] ?? null,
            sortBy: $filters['sort_by'] ?? null,
            sortDirection: $filters['sort_direction'] ?? null,
            perPage: $filters['per_page'] ?? 15,
        );

        return $query->get();
    }
}
