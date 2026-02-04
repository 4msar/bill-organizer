<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

final class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     */
    /**
     * Display a listing of the transactions.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['bill', 'bill.category']);

        // Apply filters
        if ($request->filled('bill_id')) {
            $query->where('bill_id', $request->bill_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $transactions = $query->latest('payment_date')
            ->paginate(10)
            ->withQueryString();

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|max:10240', // 10MB max
            'update_due_date' => 'boolean',
        ]);

        $bill = Bill::findOrFail($validated['bill_id']);

        // Handle file upload if present
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->storePublicly('attachments');
        }

        // Create transaction
        $transaction = new Transaction([
            'team_id' => active_team_id(),
            'bill_id' => $bill->id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'attachment' => $attachmentPath,
        ]);

        $transaction->save();

        // Update bill status
        $bill->status = 'paid';

        // If it's a recurring bill and update_due_date is true, calculate next due date
        if (
            $bill->is_recurring &&
            $request->boolean('update_due_date')
        ) {
            $nextDueDate = $bill->calculateNextDueDate(
                $request->input('nextDueDate')
            );
            if ($nextDueDate) {
                // Create a new bill for the next due date
                $bill->status = 'unpaid';
                $bill->due_date = $nextDueDate;
                $bill->save();
            }
        } else {
            // If it's not a recurring bill, set the due date to null
            $bill->status = 'paid';
            $bill->save();
        }

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
    public function destroy(Transaction $transaction)
    {
        // Delete attachment if exists
        if (
            $transaction->attachment &&
            Storage::exists($transaction->attachment)
        ) {
            Storage::delete($transaction->attachment);
        }

        $billId = $transaction->bill_id;
        $transaction->delete();

        // If this was the only transaction for this bill, set bill back to unpaid
        $bill = Bill::find($billId);
        if ($bill && $bill->transactions()->count() === 0) {
            $bill->update([
                'status' => 'unpaid',
            ]);
        }

        return back()->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Show receipt page for a transaction
     */
    public function showReceipt(Transaction $transaction)
    {
        $transaction->load(['bill', 'bill.category', 'user']);

        return inertia('Transactions/Receipt', [
            'transaction' => $transaction,
        ]);
    }
}
