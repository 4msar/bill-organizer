<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Category;
use App\Models\Transaction;
use App\Queries\Dashboard\GetDashboardStatsQuery;
use App\Queries\Dashboard\GetMonthlySpendingQuery;
use Carbon\Carbon;

final class ReportingService
{
    public function __construct(
        private GetDashboardStatsQuery $dashboardStatsQuery,
        private GetMonthlySpendingQuery $monthlySpendingQuery,
    ) {}

    /**
     * Get dashboard data with stats, categories, and charts
     */
    public function getDashboardData(): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Get stats from query
        $stats = $this->dashboardStatsQuery->get();

        // Get categories with counts
        $categories = Category::withCount([
            'bills as paid_bills_count' => function ($query) {
                $query->where('status', 'paid');
            },
        ])
            ->withCount([
                'bills as unpaid_bills_count' => function ($query) {
                    $query->where('status', 'unpaid');
                },
            ])
            ->withSum([
                'bills as total_amount' => function ($query) {
                    $query->where('status', 'unpaid');
                },
            ], 'amount')
            ->get();

        // Get recent bills
        $recentBills = Bill::with('category')
            ->latest()
            ->take(5)
            ->get();

        // Get upcoming bills
        $upcomingBills = Bill::whereYear('due_date', date('Y'))
            ->whereMonth('due_date', date('m'))
            ->with('category')
            ->unpaid()
            ->where('due_date', '>=', $now)
            ->whereDate('due_date', '<=', $now->copy()->addDays(30))
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Get monthly spending data for chart
        $chartData = $this->monthlySpendingQuery->get();

        // Get current month bills
        $dueBillsThisMonth = Bill::whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->unpaid()
            ->get();

        $paidBillsThisMonth = Bill::query()
            ->whereHas('transactions', function ($query) {
                $query->whereYear('payment_date', date('Y'))
                    ->whereMonth('payment_date', date('m'));
            })
            ->orWhereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->paid()
            ->get();

        return [
            'stats' => $stats->toArray(),
            'categories' => $categories,
            'recentBills' => $recentBills,
            'upcomingBills' => $upcomingBills,
            'chartData' => $chartData->toArray(),
            'currentMonthStats' => [
                'dueBills' => $dueBillsThisMonth,
                'paidBills' => $paidBillsThisMonth,
            ],
        ];
    }

    /**
     * Get report data for a specific date range
     */
    public function getReportData(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'dateRangeStats' => $this->generateDateRangeStats($startDate, $endDate),
            'lifetimeStats' => $this->generateLifetimeStats(),
            'categoryBreakdown' => $this->generateCategoryBreakdown($startDate, $endDate),
            'paymentMethodBreakdown' => $this->generatePaymentMethodBreakdown($startDate, $endDate),
            'monthlyTrend' => $this->generateMonthlyTrend(),
            'yearlyComparison' => $this->generateYearlyComparison(),
        ];
    }

    /**
     * Get category breakdown for a date range
     */
    public function getCategoryBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        return $this->generateCategoryBreakdown($startDate, $endDate);
    }

    /**
     * Generate statistics for a specific date range
     */
    private function generateDateRangeStats(Carbon $startDate, Carbon $endDate): array
    {
        $billsInRange = Bill::whereBetween('due_date', [$startDate, $endDate]);
        $transactionsInRange = Transaction::whereBetween('payment_date', [$startDate, $endDate]);

        return [
            'total_bills_count' => $billsInRange->count(),
            'paid_bills_count' => $billsInRange->clone()->paid()->count(),
            'unpaid_bills_count' => $billsInRange->clone()->unpaid()->count(),
            'total_bills_amount' => $billsInRange->clone()->sum('amount'),
            'paid_amount' => $transactionsInRange->clone()->sum('amount'),
            'unpaid_amount' => $billsInRange->clone()->unpaid()->sum('amount'),
            'total_expenses' => $transactionsInRange->clone()->sum('amount'),
        ];
    }

    /**
     * Generate lifetime statistics
     */
    private function generateLifetimeStats(): array
    {
        return [
            'total_bills_count' => Bill::count(),
            'paid_bills_count' => Bill::paid()->count(),
            'unpaid_bills_count' => Bill::unpaid()->count(),
            'total_bills_amount' => Bill::sum('amount'),
            'total_paid_amount' => Transaction::sum('amount'),
            'total_unpaid_amount' => Bill::unpaid()->sum('amount'),
            'total_expenses' => Transaction::sum('amount'),
        ];
    }

    /**
     * Generate category breakdown for a date range
     */
    private function generateCategoryBreakdown(Carbon $startDate, Carbon $endDate): array
    {
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

    /**
     * Generate payment method breakdown for a date range
     */
    private function generatePaymentMethodBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $paymentMethodBreakdown = Transaction::whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->payment_method ?? 'Unknown',
                    'count' => $item->count,
                    'total_amount' => $item->total_amount,
                ];
            })
            ->toArray();

        return $paymentMethodBreakdown;
    }

    /**
     * Generate 12-month trend analysis
     */
    private function generateMonthlyTrend(): array
    {
        $monthlyTrend = [];
        $startOfTrend = Carbon::now()->subMonths(11)->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $monthDate = $startOfTrend->copy()->addMonths($i);
            $monthStart = $monthDate->copy()->startOfMonth();
            $monthEnd = $monthDate->copy()->endOfMonth();

            $monthlyTrend[] = [
                'month' => $monthDate->format('M Y'),
                'total_bills' => Bill::whereBetween('due_date', [$monthStart, $monthEnd])->count(),
                'paid_bills' => Bill::whereBetween('due_date', [$monthStart, $monthEnd])->paid()->count(),
                'bills_amount' => Bill::whereBetween('due_date', [$monthStart, $monthEnd])->sum('amount'),
                'paid_amount' => Transaction::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount'),
            ];
        }

        return $monthlyTrend;
    }

    /**
     * Generate 3-year comparison
     */
    private function generateYearlyComparison(): array
    {
        $currentYear = Carbon::now()->year;
        $yearlyComparison = [];

        for ($i = 0; $i < 3; $i++) {
            $year = $currentYear - $i;
            $yearStart = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfYear();

            $yearlyComparison[] = [
                'year' => $year,
                'total_bills' => Bill::whereBetween('due_date', [$yearStart, $yearEnd])->count(),
                'paid_bills' => Bill::whereBetween('due_date', [$yearStart, $yearEnd])->paid()->count(),
                'bills_amount' => Bill::whereBetween('due_date', [$yearStart, $yearEnd])->sum('amount'),
                'paid_amount' => Transaction::whereBetween('payment_date', [$yearStart, $yearEnd])->sum('amount'),
            ];
        }

        return $yearlyComparison;
    }
}
