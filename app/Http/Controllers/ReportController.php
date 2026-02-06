<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

final class ReportController extends Controller
{
    /**
     * Display the reports page.
     */
    public function index(Request $request, ReportingService $reportingService)
    {
        // Get date range from request, default to current month
        $startDate = $request->input('start_date')
            ? Carbon::parse($request->input('start_date'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = $request->input('end_date')
            ? Carbon::parse($request->input('end_date'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $reportData = $reportingService->getReportData($startDate, $endDate);

        return Inertia::render('Reports/Index', [
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            ...$reportData,
        ]);
    }
}
