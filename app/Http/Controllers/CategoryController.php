<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(): Response
    {
        $categories = Category::withCount([
            'bills as total_bills_count',
            'bills as unpaid_bills_count' => function ($query) {
                $query->where('status', 'unpaid');
            },
        ])->withSum('bills as total_amount', 'amount')
            ->withSum(['bills as unpaid_amount' => function ($query) {
                $query->where('status', 'unpaid');
            }], 'amount')
            ->latest()
            ->get();

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
        ]);

        $category = new Category($validated);
        $category->team_id = active_team_id();
        $category->save();

        return Redirect::route('categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
        ]);

        $category->update($validated);

        return Redirect::route('categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Check if the category has any bills
        $billsCount = $category->bills()->count();

        if ($billsCount > 0) {
            return Redirect::route('categories.index')->with('error', "Cannot delete category - it has {$billsCount} bill(s) associated with it.");
        }

        $category->delete();

        return Redirect::route('categories.index')->with('success', 'Category deleted successfully.');
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
