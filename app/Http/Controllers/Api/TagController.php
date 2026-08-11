<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        return ['data' => TagResource::collection(Tag::withCount('posts')->orderBy('name')->get())];
    }

    public function show(string $slug)
    {
        return new TagResource(Tag::where('slug', $slug)->firstOrFail());
    }

    public function store(Request $request)
    {
        abort_unless($request->user()?->isAuthor(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tags,name'],
        ]);

        return new TagResource(Tag::create($data));
    }

    public function destroy(Request $request, Tag $tag)
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $tag->delete();

        return response()->json(['message' => 'تگ حذف شد.']);
    }
}
