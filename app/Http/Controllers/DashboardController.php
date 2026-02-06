<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Category;
use App\Services\ReportingService;
use Inertia\Inertia;

final class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(ReportingService $reportingService)
    {
        $dashboardData = $reportingService->getDashboardData();

        return Inertia::render('Dashboard', $dashboardData);
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
