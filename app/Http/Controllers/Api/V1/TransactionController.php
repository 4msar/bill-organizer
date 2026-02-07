<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\Api\V1\TransactionResource;
use App\Models\Bill;
use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class TransactionController extends Controller
{
    function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Display a listing of the transactions.
     */
    public function index(Request $request)
    {
        $transactions = $this->paymentService->getTransactionsWithFilters($request->all());

        return TransactionResource::collection($transactions);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(StoreTransactionRequest $request)
    {
        $bill = Bill::findOrFail($request->validated('bill_id'));

        $transaction = $this->paymentService->recordPayment(
            bill: $bill,
            paymentData: $request->validated(),
            updateDueDate: $request->boolean('update_due_date')
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => new TransactionResource($transaction->load(['bill', 'bill.category', 'user', 'team'])),
        ], 201);
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction)
    {
        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction->load(['bill', 'bill.category', 'user', 'team'])),
        ]);
    }

    /**
     * Update the specified transaction.
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $validated = $request->validated();

        // Handle file upload if present
        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $this->paymentService->uploadPaymentAttachment(
                $request->file('attachment'),
                $transaction->attachment
            );
        }

        $transaction->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'data' => new TransactionResource($transaction->fresh()->load(['bill', 'bill.category', 'user', 'team'])),
        ]);
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Transaction $transaction)
    {
        $this->paymentService->deletePayment($transaction);

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully',
        ]);
    }

    /**
     * Get transaction receipt details.
     */
    public function receipt(Transaction $transaction)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'transaction' => new TransactionResource($transaction->load(['bill', 'bill.category', 'user', 'team'])),
                'receipt_details' => [
                    'transaction_id' => $transaction->tnx_id,
                    'bill_title' => $transaction->bill->title,
                    'amount' => (float) $transaction->amount,
                    'payment_date' => $transaction->payment_date->format('Y-m-d'),
                    'payment_method' => $transaction->payment_method_name,
                    'category' => $transaction->bill->category?->name,
                    'team' => $transaction->team->name,
                    'paid_by' => $transaction->user->name,
                ],
            ],
        ]);
    }
}
