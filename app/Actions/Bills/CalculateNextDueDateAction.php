<?php

namespace App\Actions\Bills;

use App\Enums\RecurrencePeriod;
use App\Models\Bill;
use Carbon\Carbon;

final class CalculateNextDueDateAction
{
    /**
     * Calculate next due date based on recurrence period
     *
     * @param  Bill  $bill
     * @param  string|null  $dueDate
     */
    public function execute(Bill $bill, ?string $dueDate = null): ?string
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
}
