<?php

namespace App\Queries\Transactions;

use App\Contracts\Query;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class GetTransactionsQuery implements Query
{
    public function __construct(
        private readonly ?string $search = null,
        private readonly ?int $billId = null,
        private readonly ?string $paymentMethod = null,
        private readonly ?string $dateFrom = null,
        private readonly ?string $dateTo = null,
        private readonly ?float $minAmount = null,
        private readonly ?float $maxAmount = null,
        private readonly string $sortBy = 'payment_date',
        private readonly string $sortDirection = 'desc',
        private readonly int $perPage = 15,
    ) {}

    public function get(): Collection|LengthAwarePaginator
    {
        $query = Transaction::with(['bill', 'bill.category', 'user', 'team']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                // Check for colon-based targeted search (e.g., "payment_method:card")
                if (str_contains($this->search, ':')) {
                    [$column, $value] = explode(':', $this->search, 2);

                    if ($column && $value && in_fillable($column, Transaction::class)) {
                        $q->where($column, 'like', '%'.$value.'%');

                        return;
                    }
                }

                // General search across notes and payment_method
                $q->where('notes', 'like', '%'.$this->search.'%')
                    ->orWhere('payment_method', 'like', '%'.$this->search.'%');
            });
        }

        // Apply bill filter
        if ($this->billId) {
            $query->where('bill_id', $this->billId);
        }

        // Apply payment method filter
        if ($this->paymentMethod) {
            $query->where('payment_method', $this->paymentMethod);
        }

        // Apply date range filters
        if ($this->dateFrom) {
            $query->whereDate('payment_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('payment_date', '<=', $this->dateTo);
        }

        // Apply amount range filters
        if ($this->minAmount !== null) {
            $query->where('amount', '>=', $this->minAmount);
        }

        if ($this->maxAmount !== null) {
            $query->where('amount', '<=', $this->maxAmount);
        }

        // Apply sorting
        $sortBy = in_fillable($this->sortBy, Transaction::class) ? $this->sortBy : 'payment_date';
        $sortDirection = in_array($this->sortDirection, ['asc', 'desc']) ? $this->sortDirection : 'desc';

        $query->orderBy($sortBy, $sortDirection);

        // Apply pagination
        $perPage = min($this->perPage, 100);

        return $query->paginate($perPage)->withQueryString();
    }
}
