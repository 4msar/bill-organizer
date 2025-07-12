<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;

class BillReportController extends Controller
{
    function teamReport(Request $request)
    {

        return inertia('Reports/TeamReport', [
            'team' => $request->user()->currentTeam,
            'bills' => $request->user()->currentTeam->bills()->with('category')->get(),
        ]);
    }

    function billReport(Request $request, Bill $bill)
    {
        return inertia('Reports/BillReport', [
            'bill' => $bill->load(['category', 'transactions.user']),
            'team' => $request->user()->currentTeam,
        ]);
    }
}
