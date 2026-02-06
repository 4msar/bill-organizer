<?php

namespace App\Actions\Categories;

use App\Contracts\Action;
use App\Models\Category;

final class DeleteCategoryAction implements Action
{
    /**
     * Delete a category
     *
     * @param  Category  $category
     */
    public function execute(mixed ...$params): void
    {
        $category = $params[0];

        // Check if category has bills
        if ($category->bills()->count() > 0) {
            throw new \InvalidArgumentException('Cannot delete category with associated bills.');
        }

        $category->delete();
    }
}
