<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

final class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request)
    {
        $query = Category::with(['user', 'team'])
            ->when($request->search, function ($q, $search) {
                if (str_contains($search, ':')) {
                    [$column, $value] = explode(':', $search);
                    if ($column && $value && in_fillable($column, Category::class)) {
                        return $q->where($column, 'like', '%' . $value . '%');
                    }
                }

                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->when($request->user_id, function ($q, $userId) {
                $q->where('user_id', $userId);
            })
            ->when($request->team_id, function ($q, $teamId) {
                $q->where('team_id', $teamId);
            })
            ->when($request->boolean('with_bills_count'), function ($q) {
                $q->withCount('bills');
            });

        // Sorting
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (in_fillable($sortBy, Category::class)) {
            $query->orderBy($sortBy, $sortDirection === 'desc' ? 'desc' : 'asc');
        }

        // Pagination
        $perPage = min($request->input('per_page', 15), 100);
        $categories = $query->paginate($perPage);

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request)
    {
        $validated = $request->validated();

        $validated['user_id'] = $request->user()->id;
        $validated['team_id'] = $request->user()->active_team_id;

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category->load(['user', 'team'])),
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category->load(['user', 'team', 'bills'])),
        ]);
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $validated = $request->validated();

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($category->fresh()->load(['user', 'team'])),
        ]);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category)
    {
        // Check if the category has any bills
        $billsCount = $category->bills()->count();

        if ($billsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete category - it has {$billsCount} bill(s) associated with it",
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
