<?php

namespace App\Http\Controllers;

use App\Http\Requests\Bill\StoreBillRequest;
use App\Http\Requests\Bill\UpdateBillRequest;
use App\Models\Bill;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

final class BillController extends Controller
{
    public function index()
    {
        $billsQuery = Bill::with('category')
            ->when(request('search'), function ($query) {
                $search = request('search', '');

                if (str_contains($search, ':')) {
                    [$column, $value] = explode(':', request('search', ''));
                    if ($column && $value && in_fillable($column, Bill::class)) {
                        return $query->where($column, 'like', '%' . $value . '%');
                    }
                }

                $query->where('title', 'like', '%' . $search . '%');
            })
            ->when(request('status'), function ($query) {
                if (request('status') === 'upcoming') {
                    $query->upcoming(7);

                    return;
                }
                $query->where('status', request('status'));
            })
            ->when(request('category'), function ($query) {
                $query->where('category_id', request('category'));
            })
            ->when(request('date'), function ($query) {
                $date = Carbon::parse(request('date'));

                $query->whereBetween('due_date', [
                    $date->copy()->startOfMonth(),
                    $date->copy()->endOfMonth(),
                ]);
            });

        if (
            request('sort_by') &&
            in_fillable(request('sort_by'), Bill::class)
        ) {
            $sortDirection = request('sort_direction', 'asc') === 'desc' ? 'desc' : 'asc';
            $billsQuery->orderBy(request('sort_by'), $sortDirection);
        } else {
            $billsQuery->orderBy('due_date', 'asc');
        }

        $bills = $billsQuery
            ->paginate(15)
            ->onEachSide(1)
            ->withQueryString();

        $currentMonthBills = Bill::query()
            ->currentMonth()
            ->get();

        $upcomingCount = Bill::query()->upcoming(7)->count();

        return inertia('Bills/Index', [
            'bills' => $bills,
            'total_unpaid' => $currentMonthBills->filter(fn($item) => ! $item->isPaid())->sum('amount'),
            'unpaid_count' => $currentMonthBills->filter(fn($item) => ! $item->isPaid())->count(),
            'upcoming_count' => $upcomingCount,
            'paid_count' => $currentMonthBills
                ->filter(fn($item) => $item->isPaid())
                ->count(),
            'categories' => Category::all(),
        ]);
    }

    public function create()
    {
        return inertia('Bills/Create', [
            'categories' => Category::all(),
            'tags' => Bill::getAllTags(),
        ]);
    }

    public function store(StoreBillRequest $request)
    {
        $data = $request->validated();

        Bill::create($data + [
            'team_id' => active_team_id(),
        ]);

        if (parse_url(url()->previous(), PHP_URL_PATH) === '/calendar') {
            return redirect()->back()->with('success', 'Bill created successfully.');
        }

        return redirect()->route('bills.index')->with('success', 'Bill created successfully.');
    }

    public function show(Bill $bill)
    {
        // Load the bill with its category and transactions
        $bill->load([
            'category',
            'transactions' => function ($query) {
                $query->latest('payment_date');
            },
        ]);

        return inertia('Bills/Show', ['bill' => $bill]);
    }

    public function edit(Bill $bill)
    {
        return inertia('Bills/Edit', [
            'bill' => $bill,
            'categories' => Category::all(),
            'tags' => Bill::getAllTags(),
        ]);
    }

    public function update(UpdateBillRequest $request, Bill $bill)
    {
        $data = $request->validated();

        $bill->update($data);

        return redirect()->back()->with('success', 'Bill updated successfully.');
    }

    public function destroy(Bill $bill)
    {
        $bill->delete();

        return redirect()->route('bills.index')->with('success', 'Bill deleted successfully.');
    }

    public function markAsPaid(Bill $bill)
    {
        $bill->markAsPaid();

        return redirect()->back()->with('success', 'Bill marked as paid successfully.');
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
        return response()->json([
            'bill' => $bill->load('category'),
            'paymentMethods' => config('system.payment_methods'),
            'nextDueDate' => $bill->is_recurring ? $bill->calculateNextDueDate() : null,
        ]);
    }

    /**
     * Show invoice generation form
     */
    public function showInvoiceForm(Bill $bill)
    {
        $bill->load(['category', 'user', 'team']);

        return inertia('Bills/Invoice', [
            'bill' => $bill,
        ]);
    }

    /**
     * Redirect to bill page if valid link
     *
     * @param  int  $bill
     * @return \Illuminate\Http\RedirectResponse
     */
    public function visit($bill, Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        /** @var Bill $billItem */
        $billItem = Bill::withoutGlobalScopes()
            ->whereId($bill)
            ->whereUserId($user->id)
            ->with('team')
            ->firstOrFail();

        if (
            $user->activeTeam->id !== $billItem->team_id &&
            $user->hasTeam($billItem->team_id)
        ) {
            $user->switchTeam($billItem->team);
        }

        return redirect()->route('bills.show', $billItem);
    }
}
