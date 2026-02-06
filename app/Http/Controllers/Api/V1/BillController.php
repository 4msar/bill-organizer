<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\StoreBillRequest;
use App\Http\Requests\Bill\UpdateBillRequest;
use App\Http\Resources\Api\V1\BillResource;
use App\Models\Bill;
use Illuminate\Http\Request;

final class BillController extends Controller
{
    /**
     * Display a listing of the bills.
     */
    public function index(Request $request)
    {
        $query = Bill::with(['category', 'user', 'team'])
            ->when($request->search, function ($q, $search) {
                if (str_contains($search, ':')) {
                    [$column, $value] = explode(':', $search);
                    if ($column && $value && in_fillable($column, Bill::class)) {
                        return $q->where($column, 'like', '%' . $value . '%');
                    }
                }

                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->when($request->status, function ($q, $status) {
                if ($status === 'upcoming') {
                    $q->upcoming(7);
                } else {
                    $q->where('status', $status);
                }
            })
            ->when($request->category_id, function ($q, $categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($request->is_recurring !== null, function ($q) use ($request) {
                $q->where('is_recurring', $request->boolean('is_recurring'));
            })
            ->when($request->has('tags'), function ($q) use ($request) {
                $tags = is_array($request->tags) ? $request->tags : [$request->tags];
                foreach ($tags as $tag) {
                    $q->whereJsonContains('tags', $tag);
                }
            });

        // Sorting
        $sortBy = $request->input('sort_by', 'due_date');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (in_fillable($sortBy, Bill::class)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100);
        $bills = $query->paginate($perPage);

        return BillResource::collection($bills);
    }

    /**
     * Store a newly created bill.
     */
    public function store(StoreBillRequest $request)
    {
        $validated = $request->validated();

        $validated['user_id'] = $request->user()->id;
        $validated['team_id'] = $request->user()->active_team_id;

        $bill = Bill::create($validated);

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
    public function update(UpdateBillRequest $request, Bill $bill)
    {
        $validated = $request->validated();

        $bill->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bill updated successfully',
            'data' => new BillResource($bill->fresh()->load(['category', 'user', 'team'])),
        ]);
    }

    /**
     * Remove the specified bill.
     */
    public function destroy(Bill $bill)
    {
        $bill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bill deleted successfully',
        ]);
    }

    /**
     * Mark bill as paid.
     */
    public function markAsPaid(Request $request, Bill $bill)
    {
        $bill->update(['status' => 'paid']);

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
}
