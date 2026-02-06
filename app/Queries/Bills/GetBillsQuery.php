<?php

namespace App\Queries\Bills;

use App\Contracts\Query;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetBillsQuery implements Query
{
    public function __construct(
        private ?string $search = null,
        private ?string $status = null,
        private ?int $categoryId = null,
        private ?string $date = null,
        private ?string $sortBy = null,
        private ?string $sortDirection = null,
        private int $perPage = 15,
    ) {}

    /**
     * Execute the query and return paginated bills
     */
    public function get(): LengthAwarePaginator
    {
        $query = Bill::with('category')
            ->when($this->search, function ($query) {
                $search = $this->search;

                if (str_contains($search, ':')) {
                    [$column, $value] = explode(':', $search);
                    if ($column && $value && in_fillable($column, Bill::class)) {
                        return $query->where($column, 'like', '%'.$value.'%');
                    }
                }

                $query->where('title', 'like', '%'.$search.'%');
            })
            ->when($this->status, function ($query) {
                if ($this->status === 'upcoming') {
                    $query->upcoming(7);

                    return;
                }
                $query->where('status', $this->status);
            })
            ->when($this->categoryId, function ($query) {
                $query->where('category_id', $this->categoryId);
            })
            ->when($this->date, function ($query) {
                $date = Carbon::parse($this->date);

                $query->whereBetween('due_date', [
                    $date->copy()->startOfMonth(),
                    $date->copy()->endOfMonth(),
                ]);
            });

        if ($this->sortBy && in_fillable($this->sortBy, Bill::class)) {
            $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';
            $query->orderBy($this->sortBy, $sortDirection);
        } else {
            $query->orderBy('due_date', 'asc');
        }

        return $query
            ->paginate($this->perPage)
            ->onEachSide(1)
            ->withQueryString();
    }
}
