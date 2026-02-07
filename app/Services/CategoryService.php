<?php

namespace App\Services;

use App\Models\Category;
use InvalidArgumentException;

final class CategoryService
{
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
