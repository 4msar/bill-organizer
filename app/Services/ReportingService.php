<?php

namespace App\Services;

use App\Actions\Reporting\GenerateCategoryBreakdownAction;
use App\Actions\Reporting\GenerateDateRangeStatsAction;
use App\Actions\Reporting\GenerateLifetimeStatsAction;
use App\Actions\Reporting\GenerateMonthlyTrendAction;
use App\Actions\Reporting\GeneratePaymentMethodBreakdownAction;
use App\Actions\Reporting\GenerateYearlyComparisonAction;
use App\Models\Bill;
use App\Models\Category;
use App\Queries\Dashboard\GetDashboardStatsQuery;
use App\Queries\Dashboard\GetMonthlySpendingQuery;
use Carbon\Carbon;

final class ReportingService
{
    public function __construct(
        private GetDashboardStatsQuery $dashboardStatsQuery,
        private GetMonthlySpendingQuery $monthlySpendingQuery,
        private GenerateDateRangeStatsAction $dateRangeStatsAction,
        private GenerateCategoryBreakdownAction $categoryBreakdownAction,
        private GeneratePaymentMethodBreakdownAction $paymentMethodBreakdownAction,
        private GenerateMonthlyTrendAction $monthlyTrendAction,
        private GenerateYearlyComparisonAction $yearlyComparisonAction,
        private GenerateLifetimeStatsAction $lifetimeStatsAction
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
            'dateRangeStats' => $this->dateRangeStatsAction->execute($startDate, $endDate),
            'lifetimeStats' => $this->lifetimeStatsAction->execute(),
            'categoryBreakdown' => $this->categoryBreakdownAction->execute($startDate, $endDate),
            'paymentMethodBreakdown' => $this->paymentMethodBreakdownAction->execute($startDate, $endDate),
            'monthlyTrend' => $this->monthlyTrendAction->execute(),
            'yearlyComparison' => $this->yearlyComparisonAction->execute(),
        ];
    }

    /**
     * Get category breakdown for a date range
     */
    public function getCategoryBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        return $this->categoryBreakdownAction->execute($startDate, $endDate);
    }
}
