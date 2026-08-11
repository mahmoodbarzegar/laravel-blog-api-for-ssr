<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['q' => ['nullable', 'string', 'max:255']]);

        $term = $request->string('q')->trim()->value();

        $posts = Post::query()
            ->with(['author', 'category'])
            ->published()
            ->search($term)
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return PostResource::collection($posts);
    }
}
