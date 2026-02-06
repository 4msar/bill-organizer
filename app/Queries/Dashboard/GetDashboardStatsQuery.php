<?php

namespace App\Queries\Dashboard;

use App\Contracts\Query;
use App\Models\Bill;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class GetDashboardStatsQuery implements Query
{
    /**
     * Get dashboard statistics
     */
    public function get(): Collection
    {
        $now = Carbon::now();
        $currentYear = date('Y');

        // Bills statistics
        $totalBillsCount = Bill::whereYear('due_date', $currentYear)->count();
        $paidBillsCount = Bill::whereYear('due_date', $currentYear)->paid()->count();
        $unpaidBillsCount = Bill::whereYear('due_date', $currentYear)->unpaid()->count();
        $upcomingBillsCount = Bill::whereYear('due_date', $currentYear)->upcoming(30)->count();

        // Financial statistics
        $totalPaidAmount = Bill::whereYear('due_date', $currentYear)->paid()->sum('amount');
        $totalUnpaidAmount = Bill::whereYear('due_date', $currentYear)->unpaid()->sum('amount');

        // Due this month
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $dueBillsThisMonth = Bill::whereBetween('due_date', [$startOfMonth, $endOfMonth])
            ->unpaid()
            ->get();

        $amountDueThisMonth = $dueBillsThisMonth->sum('amount');

        return collect([
            'totalBills' => $totalBillsCount,
            'paidBills' => $paidBillsCount,
            'unpaidBills' => $unpaidBillsCount,
            'upcomingBills' => $upcomingBillsCount,
            'totalPaidAmount' => $totalPaidAmount,
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'amountDueThisMonth' => $amountDueThisMonth,
        ]);
    }
}
