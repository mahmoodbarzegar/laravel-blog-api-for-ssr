<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return ['data' => CategoryResource::collection(
            Category::withCount('publishedPosts')->orderBy('name')->get()
        )];
    }

    public function show(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        return new CategoryResource($category);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403, 'فقط مدیر سایت می‌تواند دسته‌بندی بسازد.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        return new CategoryResource(Category::create($data));
    }

    public function update(Request $request, Category $category)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.$category->id],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($data);

        return new CategoryResource($category);
    }

    public function destroy(Request $request, Category $category)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $category->delete();

        return response()->json(['message' => 'دسته‌بندی حذف شد.']);
    }
}
