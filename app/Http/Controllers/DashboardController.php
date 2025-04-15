<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bill;
use App\Models\Category;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $now = Carbon::now();

        // Bills statistics
        $totalBillsCount = Bill::count();
        $paidBillsCount = Bill::paid()->count();
        $unpaidBillsCount = Bill::unpaid()->count();
        $upcomingBillsCount = Bill::upcoming(7)->count();

        // Financial statistics
        $totalPaidAmount = Bill::paid()->sum('amount');
        $totalUnpaidAmount = Bill::unpaid()->sum('amount');

        // Due this month
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $dueBillsThisMonth = Bill::unpaid()
            ->whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->get();
        $amountDueThisMonth = $dueBillsThisMonth->sum('amount');

        // Category statistics
        $categories = Category::withCount(['bills as paid_bills_count' => function ($query) {
            $query->where('status', 'paid');
        }])
            ->withCount(['bills as unpaid_bills_count' => function ($query) {
                $query->where('status', 'unpaid');
            }])
            ->withSum(['bills as total_amount' => function ($query) {
                $query->where('status', 'unpaid');
            }], 'amount')
            ->get();

        // Recent bills
        $recentBills = Bill::with('category')
            ->latest()
            ->take(5)
            ->get();

        // Upcoming bills
        $upcomingBills = Bill::with('category')
            ->unpaid()
            ->where('due_date', '>=', $now)
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Monthly spending by category (for chart)
        $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth();
        $months = [];

        for ($i = 0; $i < 6; $i++) {
            $monthDate = $sixMonthsAgo->copy()->addMonths($i);
            $months[] = [
                'month' => $monthDate->format('M Y'),
                'month_number' => $monthDate->month,
                'year' => $monthDate->year,
            ];
        }

        $monthlySpendingByCategory = [];

        foreach ($categories as $category) {
            $monthlyCounts = [];

            foreach ($months as $month) {
                $startDate = Carbon::createFromDate($month['year'], $month['month_number'], 1)->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();

                $amount = Bill::where('category_id', $category->id)
                    ->where('status', 'paid')
                    ->whereBetween('updated_at', [$startDate, $endDate])
                    ->sum('amount');

                $monthlyCounts[] = $amount;
            }

            $monthlySpendingByCategory[] = [
                'category' => $category->name,
                'data' => $monthlyCounts,
            ];
        }

        // Pass all data to the dashboard view
        return Inertia::render('Dashboard', [
            'stats' => [
                'totalBills' => $totalBillsCount,
                'paidBills' => $paidBillsCount,
                'unpaidBills' => $unpaidBillsCount,
                'upcomingBills' => $upcomingBillsCount,
                'totalPaidAmount' => $totalPaidAmount,
                'totalUnpaidAmount' => $totalUnpaidAmount,
                'amountDueThisMonth' => $amountDueThisMonth,
            ],
            'categories' => $categories,
            'recentBills' => $recentBills,
            'upcomingBills' => $upcomingBills,
            'chartData' => [
                'months' => array_column($months, 'month'),
                'series' => $monthlySpendingByCategory,
            ],
        ]);
    }
}
