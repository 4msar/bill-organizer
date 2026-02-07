<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;
use InvalidArgumentException;

final class CategoryService
{
    function getCategory(): Collection
    {
        $categories = Category::query()
            ->withCount([
                'bills as total_bills_count',
                'bills as unpaid_bills_count' => function ($query) {
                    $query->where('status', 'unpaid');
                },
            ])
            ->withSum('bills as total_amount', 'amount')
            ->withSum(['bills as unpaid_amount' => function ($query) {
                $query->where('status', 'unpaid');
            }], 'amount')
            ->when(request('search'), function ($q, $search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->latest()
            ->get();

        return $categories;
    }

    /**
     * Create a new category
     */
    public function createCategory(array $data): Category
    {
        $data['team_id'] = active_team_id();

        return Category::create($data);
    }

    /**
     * Update a category
     */
    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    /**
     * Delete a category
     */
    public function deleteCategory(Category $category): void
    {
        // Check if category has bills
        if ($category->bills()->count() > 0) {
            throw new InvalidArgumentException('Cannot delete category with associated bills.');
        }

        $category->delete();
    }
}
