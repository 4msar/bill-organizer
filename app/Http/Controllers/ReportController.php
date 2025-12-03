<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

final class ReportController extends Controller
{
    /**
     * Display the reports page.
     */
    public function index(Request $request)
    {
        // Get date range from request, default to current month
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfMonth();

        // Bills statistics for selected date range
        $billsInRange = Bill::whereBetween('due_date', [$startDate, $endDate]);
        $transactionsInRange = Transaction::whereBetween('payment_date', [$startDate, $endDate]);

        // Date range statistics
        $dateRangeStats = [
            'total_bills_count' => $billsInRange->count(),
            'paid_bills_count' => $billsInRange->clone()->paid()->count(),
            'unpaid_bills_count' => $billsInRange->clone()->unpaid()->count(),
            'total_bills_amount' => $billsInRange->clone()->sum('amount'),
            'paid_amount' => $transactionsInRange->clone()->sum('amount'),
            'unpaid_amount' => $billsInRange->clone()->unpaid()->sum('amount'),
            'total_expenses' => $transactionsInRange->clone()->sum('amount'),
        ];

        // Lifetime statistics
        $lifetimeStats = [
            'total_bills_count' => Bill::count(),
            'paid_bills_count' => Bill::paid()->count(),
            'unpaid_bills_count' => Bill::unpaid()->count(),
            'total_bills_amount' => Bill::sum('amount'),
            'total_paid_amount' => Transaction::sum('amount'),
            'total_unpaid_amount' => Bill::unpaid()->sum('amount'),
            'total_expenses' => Transaction::sum('amount'),
        ];

        // Category breakdown for date range
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
            ->values();

        // Payment method breakdown for date range
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
            });

        // Monthly trend for the past 12 months
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

        // Yearly comparison
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

        return Inertia::render('Reports/Index', [
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'dateRangeStats' => $dateRangeStats,
            'lifetimeStats' => $lifetimeStats,
            'categoryBreakdown' => $categoryBreakdown,
            'paymentMethodBreakdown' => $paymentMethodBreakdown,
            'monthlyTrend' => $monthlyTrend,
            'yearlyComparison' => $yearlyComparison,
        ]);
    }
}
