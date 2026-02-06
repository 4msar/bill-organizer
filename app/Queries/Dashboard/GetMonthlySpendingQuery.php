<?php

namespace App\Queries\Dashboard;

use App\Contracts\Query;
use App\Models\Bill;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class GetMonthlySpendingQuery implements Query
{
    /**
     * Get 6-month spending data by category for charts
     */
    public function get(): Collection
    {
        $now = Carbon::now();
        $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth();
        $months = [];

        // Build months array
        for ($i = 0; $i < 6; $i++) {
            $monthDate = $sixMonthsAgo->copy()->addMonths($i);
            $months[] = [
                'month' => $monthDate->format('M Y'),
                'month_number' => $monthDate->month,
                'year' => $monthDate->year,
            ];
        }

        $categories = Category::all();
        $monthlySpendingByCategory = [];

        foreach ($categories as $category) {
            $monthlyCounts = [];

            foreach ($months as $month) {
                $startDate = Carbon::createFromDate($month['year'], $month['month_number'], 1)->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();

                // Get transaction amounts for this category and month
                $amount = Transaction::whereHas('bill', function ($query) use ($category) {
                    $query->where('category_id', $category->id);
                })
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount');

                // Get bills with no transactions but marked as paid
                $billsWithNoTnx = Bill::where('category_id', $category->id)
                    ->whereBetween('due_date', [$startDate, $endDate])
                    ->where('status', 'paid')
                    ->whereDoesntHave('transactions')
                    ->sum('amount');

                $monthlyCounts[] = round($amount + $billsWithNoTnx, 1);
            }

            $monthlySpendingByCategory[] = [
                'category' => $category->name,
                'data' => $monthlyCounts,
            ];
        }

        return collect([
            'months' => array_column($months, 'month'),
            'series' => $monthlySpendingByCategory,
        ]);
    }
}
