<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Category::withCount('posts')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:50',
        ]);

        $data['slug'] = Category::uniqueSlug($data['name']);
        $category     = Category::create($data);
        Audit::record('category.created', $category);

        return response()->json($category, 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json($category->loadCount('posts'));
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:50',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Category::uniqueSlug($data['name'], $category->id);
        }

        $category->update($data);
        Audit::record('category.updated', $category, Audit::diff($category, ['name', 'slug', 'description', 'icon']));

        return response()->json($category);
    }

    public function destroy(Category $category): JsonResponse
    {
        Audit::record('category.deleted', $category);
        $category->delete();
        return response()->json(['message' => 'Category deleted.']);
    }
}
