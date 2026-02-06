<?php

namespace App\Actions\Bills;

use App\Contracts\Action;
use App\Enums\RecurrencePeriod;
use App\Models\Bill;
use Carbon\Carbon;

final class CalculateBillStatusAction implements Action
{
    /**
     * Calculate the status based on recurrence period and transactions
     *
     * @param  Bill  $bill
     */
    public function execute(mixed ...$params): string
    {
        $bill = $params[0];
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
}
