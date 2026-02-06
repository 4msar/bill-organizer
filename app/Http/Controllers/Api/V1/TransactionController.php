<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\Api\V1\TransactionResource;
use App\Models\Bill;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

final class TransactionController extends Controller
{
    /**
     * Display a listing of the transactions.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['bill', 'bill.category', 'user', 'team'])
            ->when($request->search, function ($q, $search) {
                if (str_contains($search, ':')) {
                    [$column, $value] = explode(':', $search);
                    if ($column && $value && in_fillable($column, Transaction::class)) {
                        return $q->where($column, 'like', '%'.$value.'%');
                    }
                }

                $q->where('notes', 'like', '%'.$search.'%')
                    ->orWhere('payment_method', 'like', '%'.$search.'%');
            })
            ->when($request->bill_id, function ($q, $billId) {
                $q->where('bill_id', $billId);
            })
            ->when($request->payment_method, function ($q, $method) {
                $q->where('payment_method', $method);
            })
            ->when($request->date_from, function ($q, $dateFrom) {
                $q->whereDate('payment_date', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($q, $dateTo) {
                $q->whereDate('payment_date', '<=', $dateTo);
            })
            ->when($request->min_amount, function ($q, $minAmount) {
                $q->where('amount', '>=', $minAmount);
            })
            ->when($request->max_amount, function ($q, $maxAmount) {
                $q->where('amount', '<=', $maxAmount);
            });

        // Sorting
        $sortBy = $request->input('sort_by', 'payment_date');
        $sortDirection = $request->input('sort_direction', 'desc');

        if (in_fillable($sortBy, Transaction::class)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100);
        $transactions = $query->paginate($perPage);

        return TransactionResource::collection($transactions);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        $bill = Bill::findOrFail($validated['bill_id']);

        // Handle file upload if present
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->storePublicly('attachments');
        }

        $validated['user_id'] = $request->user()->id;
        $validated['team_id'] = $request->user()->active_team_id;
        $validated['attachment'] = $attachmentPath;

        $transaction = Transaction::create($validated);

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
                $bill->status = 'unpaid';
                $bill->due_date = $nextDueDate;
            }
        }

        $bill->save();

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
            // Delete old attachment if exists
            if ($transaction->attachment && Storage::exists($transaction->attachment)) {
                Storage::delete($transaction->attachment);
            }

            $validated['attachment'] = $request->file('attachment')->storePublicly('attachments');
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
