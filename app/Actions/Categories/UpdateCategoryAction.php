<?php

namespace App\Actions\Categories;

use App\Contracts\Action;
use App\Models\Category;

final class UpdateCategoryAction implements Action
{
    /**
     * Update a category
     *
     * @param  Category  $category
     * @param  array  $data
     */
    public function execute(mixed ...$params): Category
    {
        $category = $params[0];
        $data = $params[1];

        $category->update($data);

        return $category->fresh();
    }
}
