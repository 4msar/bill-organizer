<?php

namespace App\Actions\Reporting;

use App\Contracts\Action;
use App\Models\Category;
use Carbon\Carbon;

final class GenerateCategoryBreakdownAction implements Action
{
    /**
     * Generate category breakdown for a date range
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     */
    public function execute(mixed ...$params): array
    {
        $startDate = $params[0];
        $endDate = $params[1];

        $categoryBreakdown = Category::withCount(['bills' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('due_date', [$startDate, $endDate]);
        }])
            ->withCount(['bills as paid_bills_count' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('due_date', [$startDate, $endDate])
                    ->where('status', 'paid');
            }])
            ->withSum(['bills as total_amount' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('due_date', [$startDate, $endDate]);
            }], 'amount')
            ->withSum(['bills as paid_amount' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('due_date', [$startDate, $endDate])
                    ->where('status', 'paid');
            }], 'amount')
            ->get()
            ->filter(function ($category) {
                return $category->bills_count > 0;
            })
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'color' => $category->color ?: '#6366f1',
                    'total_bills' => $category->bills_count,
                    'paid_bills' => $category->paid_bills_count,
                    'total_amount' => $category->total_amount ?? 0,
                    'paid_amount' => $category->paid_amount ?? 0,
                ];
            })
            ->values()
            ->toArray();

        return $categoryBreakdown;
    }
}
