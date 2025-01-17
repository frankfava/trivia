<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Category::class);

    }

    /** Show a single Category */
    public function show(Category $category)
    {
        $this->authorize('view', $category);
    }

    /** Delete a Category */
    public function destroy(Category $category)
    {
        // As long as its not used on a Question
        $this->authorize('delete', $category);
    }
}
