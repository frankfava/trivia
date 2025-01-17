<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModelResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);

        $categories = Category::paginate(
            perPage : $request->query('per_page', 10),
            page : $request->query('page', 1)
        );

        return ModelResource::create($categories);

    }

    /** Show a single Category */
    public function show(Category $category)
    {
        $this->authorize('view', $category);

        return ModelResource::create($category);
    }

    /** Delete a Category */
    public function destroy(Category $category)
    {
        // As long as its not used on a Question
        $this->authorize('delete', $category);

        $category->delete();

        return response()->noContent();
    }
}
