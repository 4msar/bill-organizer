<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Models\Bill;
use App\Models\Transaction;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

final class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     */
    public function index(Request $request, PaymentService $paymentService)
    {
        $transactions = $paymentService->getTransactionsWithFilters([
            ...$request->only(['bill_id', 'payment_method', 'date_from', 'date_to']),
            'per_page' => 10,
        ]);

        // Get all user's bills for the filter dropdown
        $bills = Bill::select('id', 'title')->orderBy('title')->get();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['bill_id', 'payment_method', 'date_from', 'date_to']),
            'bills' => $bills,
        ]);
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(StoreTransactionRequest $request, PaymentService $paymentService)
    {
        $bill = Bill::findOrFail($request->validated('bill_id'));

        $paymentService->recordPayment(
            bill: $bill,
            paymentData: $request->validated(),
            updateDueDate: $request->boolean('update_due_date')
        );

        return Redirect::back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction)
    {
        return redirect()->route('bills.show', [
            'bill' => $transaction->bill_id,
        ]);
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(Transaction $transaction, PaymentService $paymentService)
    {
        $paymentService->deletePayment($transaction);

        return back()->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Show receipt page for a transaction
     */
    public function showReceipt(Transaction $transaction, PaymentService $paymentService)
    {
        return inertia('Transactions/Receipt', $paymentService->generateReceipt($transaction));
    }
}
