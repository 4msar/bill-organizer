<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Category;
use Illuminate\Http\Request;

final class BillController extends Controller
{
    public function index()
    {
        $bills = Bill::with('category')->where('user_id', auth()->id())->get();

        return inertia('Bills/Index', [
            'bills' => $bills,
            'total_unpaid' => $bills->where('is_paid', false)->sum('amount'),
            'unpaid_count' => $bills->where('is_paid', false)->count(),
            'upcoming_count' => $bills->where('due_date', '>=', now())->count(),
            'paid_count' => $bills->where('is_paid', true)->count(),
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
}
