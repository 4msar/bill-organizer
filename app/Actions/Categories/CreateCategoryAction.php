<?php

namespace App\Actions\Categories;

use App\Contracts\Action;
use App\Models\Category;

final class CreateCategoryAction implements Action
{
    /**
     * Create a new category
     *
     * @param  array  $data
     */
    public function execute(mixed ...$params): Category
    {
        $data = $params[0];

        $category = new Category($data);
        $category->team_id = active_team_id();
        $category->save();

        return $category;
    }
}
