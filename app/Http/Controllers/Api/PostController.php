<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * Public: paginated list of published posts (home page, category/tag pages use query filters).
     */
    public function index(Request $request)
    {
        $posts = Post::query()
            ->with(['author', 'category', 'tags'])
            ->published()
            ->when($request->category, fn($q, $slug) => $q->whereHas('category', fn($q) => $q->where('slug', $slug)))
            ->when($request->tag, fn($q, $slug) => $q->whereHas('tags', fn($q) => $q->where('slug', $slug)))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return PostResource::collection($posts);
    }

    /**
     * Public: show a single published post by slug + increment view count.
     */
    public function show(string $slug)
    {
        $post = Post::with(['author', 'category', 'tags'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $post->increment('views');

        return new PostResource($post);
    }

    /**
     * Admin/author: list all of own posts (or all, if admin) including drafts.
     */
    public function manage(Request $request)
    {
        $user = $request->user();

        $posts = Post::query()
            ->with(['author', 'category'])
            ->when(! $user->isAdmin(), fn($q) => $q->where('author_id', $user->id))
            ->latest()
            ->paginate(15);

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request)
    {
        $post = Post::create([
            ...$request->validated(),
            'author_id' => $request->user()->id,
        ]);

        $post->tags()->sync($request->input('tags', []));

        return new PostResource($post->load(['author', 'category', 'tags']));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());

        if ($request->has('tags')) {
            $post->tags()->sync($request->input('tags', []));
        }

        return new PostResource($post->fresh(['author', 'category', 'tags']));
    }

    public function destroy(Request $request, Post $post)
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'مقاله حذف شد.']);
    }

    /**
     * Admin/author: fetch a single post (including drafts) for editing.
     */
    public function edit(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        return new PostResource($post->load(['tags', 'category']));
    }
}
