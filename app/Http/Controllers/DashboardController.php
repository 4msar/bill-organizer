<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Inertia\Inertia;

final class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $now = Carbon::now();

        // Bills statistics
        $totalBillsCount = Bill::whereYear('due_date', date('Y'))->count();
        $paidBillsCount = Bill::whereYear('due_date', date('Y'))->paid()->count();
        $unpaidBillsCount = Bill::whereYear('due_date', date('Y'))->unpaid()->count();
        $upcomingBillsCount = Bill::whereYear('due_date', date('Y'))->upcoming(30)->count();

        // Financial statistics
        $totalPaidAmount = Bill::whereYear('due_date', date('Y'))->paid()->sum('amount');
        $totalUnpaidAmount = Bill::whereYear('due_date', date('Y'))->unpaid()->sum('amount');

        // Due this month
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $dueBillsThisMonth = Bill::whereBetween(
            'due_date',
            [$startOfMonth, $endOfMonth]
        )
            ->unpaid()
            ->get();

        $paidBillsThisMonth = Bill::query()
            ->whereHas(
                'transactions',
                function ($query) {
                    $query->whereYear('payment_date', date('Y'))
                        ->whereMonth('payment_date', date('m'));
                }
            )
            ->orWhereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->paid()
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
        $upcomingBills = Bill::whereYear('due_date', date('Y'))
            ->whereMonth('due_date', date('m'))
            ->with('category')
            ->unpaid()
            ->where('due_date', '>=', $now)
            ->whereDate('due_date', '<=', $now->copy()->addDays(30))
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

                $amount = Transaction::whereHas(
                    'bill',
                    function ($query) use ($category) {
                        $query->where('category_id', $category->id);
                    }
                )
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount');

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
            'currentMonthStats' => [
                'dueBills' => $dueBillsThisMonth,
                'paidBills' => $paidBillsThisMonth,
            ]
        ]);
    }

    /**
     * Show the calendar.
     */
    public function calendar()
    {
        return Inertia::render('Calendar', [
            'bills' => Bill::with('category')->get(),
            'categories' => Category::all(),
            'tags' => Bill::getAllTags(),
        ]);
    }
}
