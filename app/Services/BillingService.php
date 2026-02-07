<?php

namespace App\Services;

use App\Actions\Bills\CalculateBillStatusAction;
use App\Actions\Bills\CalculateNextDueDateAction;
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
        return app(CalculateBillStatusAction::class)->execute($bill);
    }

    /**
     * Calculate next due date based on recurrence period
     */
    public function calculateNextDueDate(
        Bill $bill,
        ?string $dueDate = null
    ): ?string {
        return app(CalculateNextDueDateAction::class)->execute($bill, $dueDate);
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
