<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_approved_comments_are_publicly_listed(): void
    {
        $post = Post::factory()->create();
        Comment::factory()->count(2)->create(['post_id' => $post->id, 'status' => 'approved']);
        Comment::factory()->create(['post_id' => $post->id, 'status' => 'pending']);

        $response = $this->getJson("/api/posts/{$post->id}/comments");

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_guest_cannot_submit_a_comment(): void
    {
        $post = Post::factory()->create();

        $this->postJson("/api/posts/{$post->id}/comments", ['body' => 'نظر من'])
            ->assertStatus(401);
    }

    public function test_a_readers_comment_requires_moderation(): void
    {
        $reader = User::factory()->reader()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($reader, 'sanctum')
            ->postJson("/api/posts/{$post->id}/comments", ['body' => 'نظر خوانندهٔ عادی']);

        $response->assertCreated()->assertJsonPath('status', 'pending');
    }

    public function test_an_authors_comment_is_auto_approved(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $post = Post::factory()->create();

        $response = $this->actingAs($author, 'sanctum')
            ->postJson("/api/posts/{$post->id}/comments", ['body' => 'نظر نویسنده']);

        $response->assertCreated()->assertJsonPath('status', 'approved');
    }

    public function test_a_non_admin_cannot_view_pending_comments(): void
    {
        $author = User::factory()->create(['role' => 'author']);

        $this->actingAs($author, 'sanctum')
            ->getJson('/api/manage/comments/pending')
            ->assertStatus(403);
    }

    public function test_an_admin_can_view_and_moderate_pending_comments(): void
    {
        $admin = User::factory()->admin()->create();
        $comment = Comment::factory()->create(['status' => 'pending']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/manage/comments/pending')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/manage/comments/{$comment->id}", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('status', 'approved');

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'status' => 'approved']);
    }

    public function test_a_user_can_delete_their_own_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/comments/{$comment->id}")
            ->assertOk();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_a_user_cannot_delete_someone_elses_comment(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder, 'sanctum')
            ->deleteJson("/api/comments/{$comment->id}")
            ->assertStatus(403);
    }
}
