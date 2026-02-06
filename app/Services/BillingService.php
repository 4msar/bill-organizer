<?php

namespace App\Services;

use App\Actions\Bills\CreateBillAction;
use App\Actions\Bills\DeleteBillAction;
use App\Actions\Bills\MarkBillAsPaidAction;
use App\Actions\Bills\UpdateBillAction;
use App\Models\Bill;
use App\Queries\Bills\GetBillsQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class BillingService
{
    public function __construct(
        private readonly CreateBillAction $createBillAction,
        private readonly UpdateBillAction $updateBillAction,
        private readonly DeleteBillAction $deleteBillAction,
        private readonly MarkBillAsPaidAction $markAsPaidAction,
    ) {}

    /**
     * Create a new bill
     */
    public function createBill(array $data): Bill
    {
        return $this->createBillAction->execute($data);
    }

    /**
     * Update an existing bill
     */
    public function updateBill(Bill $bill, array $data): Bill
    {
        return $this->updateBillAction->execute($bill, $data);
    }

    /**
     * Delete a bill
     */
    public function deleteBill(Bill $bill): void
    {
        $this->deleteBillAction->execute($bill);
    }

    /**
     * Mark a bill as paid
     */
    public function markBillAsPaid(Bill $bill): Bill
    {
        return $this->markAsPaidAction->execute($bill);
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
