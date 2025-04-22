<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

final class BillController extends Controller
{
    public function index()
    {
        $bills = Bill::with('category')->where('user_id', auth()->id())->get();

        $unpaidBills = $bills->where('status', 'unpaid')->filter(
            fn($item) => $item->due_date->isCurrentMonth()
        );

        return inertia('Bills/Index', [
            'bills' => $bills,
            'total_unpaid' => $unpaidBills->sum('amount'),
            'unpaid_count' => $unpaidBills->count(),
            'upcoming_count' => $bills
                ->filter(fn($item) => $item->isUpcoming())
                ->count(),
            'paid_count' => $bills
                ->where('status', 'paid')
                ->filter(fn($item) => $item->due_date->isCurrentMonth())
                ->count(),
        ]);
    }

    public function create()
    {
        return inertia('Bills/Create', [
            'categories' => Category::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
        ]);

        Bill::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'is_recurring' => $request->is_recurring,
            'recurrence_period' => $request->recurrence_period,
        ]);

        return redirect()->route('bills.index')->with('success', 'Bill created successfully.');
    }

    public function show(Bill $bill)
    {
        // Load the bill with its category and transactions
        $bill->load([
            'category',
            'transactions' => function ($query) {
                $query->latest('payment_date');
            }
        ]);

        return inertia('Bills/Show', ['bill' => $bill]);
    }

    public function edit(Bill $bill)
    {
        return inertia('Bills/Edit', ['bill' => $bill]);
    }

    public function update(Request $request, Bill $bill)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
        ]);

        $bill->update($request->all());

        return redirect()->route('bills.index')->with('success', 'Bill updated successfully.');
    }

    public function destroy(Bill $bill)
    {
        $bill->delete();

        return redirect()->route('bills.index')->with('success', 'Bill deleted successfully.');
    }

    public function markAsPaid(Bill $bill)
    {
        $bill->markAsPaid();

        return redirect()->route('bills.index')->with('success', 'Bill marked as paid successfully.');
    }

    /**
     * Display bill payment form.
     */
    public function showPaymentForm(Bill $bill)
    {
        if ($bill->status === 'paid') {
            return Redirect::route('bills.show', $bill)->with('error', 'This bill is already paid.');
        }

        $paymentMethods = [
            'cash' => 'Cash',
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'paypal' => 'PayPal',
            'crypto' => 'Cryptocurrency',
            'check' => 'Check',
            'other' => 'Other',
        ];

        return inertia('Bills/Pay', [
            'bill' => $bill->load('category'),
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Get bill details for payment dialog.
     */
    public function getPaymentDetails(Bill $bill)
    {
        $paymentMethods = [
            'cash' => 'Cash',
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'paypal' => 'PayPal',
            'crypto' => 'Cryptocurrency',
            'check' => 'Check',
            'other' => 'Other',
        ];

        return response()->json([
            'bill' => $bill->load('category'),
            'paymentMethods' => $paymentMethods,
            'nextDueDate' => $bill->is_recurring ? $bill->calculateNextDueDate() : null,
        ]);
    }
}
