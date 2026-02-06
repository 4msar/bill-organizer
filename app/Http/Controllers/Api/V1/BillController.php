<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\StoreBillRequest;
use App\Http\Requests\Bill\UpdateBillRequest;
use App\Http\Resources\Api\V1\BillResource;
use App\Models\Bill;
use App\Services\BillingService;
use Illuminate\Http\Request;

final class BillController extends Controller
{
    /**
     * Display a listing of the bills.
     */
    public function index(Request $request, BillingService $billingService)
    {
        $bills = $billingService->getBillsWithFilters($request->all());

        return BillResource::collection($bills);
    }

    /**
     * Store a newly created bill.
     */
    public function store(StoreBillRequest $request, BillingService $billingService)
    {
        $bill = $billingService->createBill($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Bill created successfully',
            'data' => new BillResource($bill->load(['category', 'user', 'team'])),
        ], 201);
    }

    /**
     * Display the specified bill.
     */
    public function show(Bill $bill)
    {
        return response()->json([
            'success' => true,
            'data' => new BillResource($bill->load(['category', 'user', 'team', 'transactions', 'notes'])),
        ]);
    }

    /**
     * Update the specified bill.
     */
    public function update(UpdateBillRequest $request, Bill $bill, BillingService $billingService)
    {
        $billingService->updateBill($bill, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Bill updated successfully',
            'data' => new BillResource($bill->fresh()->load(['category', 'user', 'team'])),
        ]);
    }

    /**
     * Remove the specified bill.
     */
    public function destroy(Bill $bill, BillingService $billingService)
    {
        $billingService->deleteBill($bill);

        return response()->json([
            'success' => true,
            'message' => 'Bill deleted successfully',
        ]);
    }

    /**
     * Mark bill as paid.
     */
    public function markAsPaid(Request $request, Bill $bill, BillingService $billingService)
    {
        $billingService->markBillAsPaid($bill);

        return response()->json([
            'success' => true,
            'message' => 'Bill marked as paid',
            'data' => new BillResource($bill->fresh()->load(['category', 'user', 'team'])),
        ]);
    }

    /**
     * Get upcoming bills.
     */
    public function upcoming(Request $request)
    {
        $days = $request->input('days', 7);
        $bills = Bill::with(['category', 'user', 'team'])
            ->upcoming($days)
            ->get();

        return response()->json([
            'success' => true,
            'data' => BillResource::collection($bills),
        ]);
    }

    /**
     * Get bill details for payment.
     */
    public function getPaymentDetails(Bill $bill)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'bill' => new BillResource($bill->load('category')),
                'payment_methods' => config('system.payment_methods'),
                'next_due_date' => $bill->is_recurring ? $bill->calculateNextDueDate() : null,
            ],
        ]);
    }
}
