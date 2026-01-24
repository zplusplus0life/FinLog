<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Category::withCount('transactions')->latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'type' => 'required|in:income,expense',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return $category;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'type' => 'required|in:income,expense',
            'is_active' => 'required|boolean',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Update the status of the specified resource in storage.
     */
    public function updateStatus(Request $request, Category $category)
    {
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $category->is_active = $validated['is_active'];
        $category->save();

        return response()->json($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Note: The database migration sets onDelete('cascade'). 
        // Deleting a category will also delete all transactions associated with it.
        $category->delete();

        return response()->json(null, 204);
    }
}
