<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Public: approved comments for a post (nested replies included).
     */
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->approved()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        return ['data' => CommentResource::collection($comments)];
    }

    /**
     * Authenticated: submit a comment (goes to "pending" until moderated).
     */
    public function store(Request $request, Post $post)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:2000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        $comment = $post->allComments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
            // Trusted authors/admins get auto-approval; readers require moderation.
            'status' => $request->user()->isAuthor() ? 'approved' : 'pending',
        ]);

        return new CommentResource($comment->load('user'));
    }

    /**
     * Admin: list pending comments across all posts for moderation.
     */
    public function pending(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $comments = Comment::with(['user', 'post'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return CommentResource::collection($comments);
    }

    public function moderate(Request $request, Comment $comment)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $comment->update($data);

        return new CommentResource($comment);
    }

    public function destroy(Request $request, Comment $comment)
    {
        abort_unless(
            $request->user()->isAdmin() || $request->user()->id === $comment->user_id,
            403
        );

        $comment->delete();

        return response()->json(['message' => 'نظر حذف شد.']);
    }
}
