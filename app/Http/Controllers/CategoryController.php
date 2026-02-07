<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    /**
     * Display a listing of the categories.
     */
    public function index(): Response
    {
        $categories = $this->categoryService->getCategory();

        // Get all available icons for the select dropdown
        $availableIcons = $this->getAvailableIcons();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'availableIcons' => $availableIcons,
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        $this->categoryService->createCategory($request->validated());

        return Redirect::route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->categoryService->updateCategory($category, $request->validated());

        return Redirect::route('categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        try {
            $this->categoryService->deleteCategory($category);

            return Redirect::route('categories.index')->with('success', 'Category deleted successfully.');
        } catch (\InvalidArgumentException $e) {
            return Redirect::route('categories.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Get list of available icons.
     */
    private function getAvailableIcons(): array
    {
        return [
            'home' => 'Home',
            'shopping-cart' => 'Shopping',
            'car' => 'Transportation',
            'utensils' => 'Food',
            'wifi' => 'Internet',
            'tv' => 'Entertainment',
            'heart' => 'Health',
            'graduation-cap' => 'Education',
            'landmark' => 'Mortgage',
            'phone' => 'Phone',
            'water' => 'Utilities',
            'credit-card' => 'Credit Card',
            'gift' => 'Gifts',
            'suitcase' => 'Work',
            'children' => 'Children',
            'paw' => 'Pets',
            'plane' => 'Travel',
            'gamepad' => 'Gaming',
            'dumbbell' => 'Fitness',
            'dollar-sign' => 'Other',
        ];
    }
}
