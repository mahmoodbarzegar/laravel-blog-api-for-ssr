<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_only_lists_published_posts(): void
    {
        Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);
        Post::factory()->draft()->create();
        Post::factory()->create(['status' => 'published', 'published_at' => now()->addWeek()]); // scheduled, not yet due

        $response = $this->getJson('/api/posts');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_public_can_view_a_single_published_post_and_view_count_increments(): void
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'views' => 0,
        ]);

        $response = $this->getJson("/api/posts/{$post->slug}");

        $response->assertOk()->assertJsonPath('title', $post->title);
        $this->assertEquals(1, $post->fresh()->views);
    }

    public function test_a_draft_post_is_not_publicly_visible(): void
    {
        $post = Post::factory()->draft()->create();

        $this->getJson("/api/posts/{$post->slug}")->assertNotFound();
    }

    public function test_index_can_be_filtered_by_category_and_tag(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $matching = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'category_id' => $category->id,
        ]);
        $matching->tags()->attach($tag);

        Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);

        $byCategory = $this->getJson("/api/posts?category={$category->slug}");
        $byCategory->assertOk();
        $this->assertCount(1, $byCategory->json('data'));

        $byTag = $this->getJson("/api/posts?tag={$tag->slug}");
        $byTag->assertOk();
        $this->assertCount(1, $byTag->json('data'));
    }

    public function test_guest_cannot_create_a_post(): void
    {
        $this->postJson('/api/manage/posts', ['title' => 'X'])->assertStatus(401);
    }

    public function test_a_reader_cannot_create_a_post(): void
    {
        $reader = User::factory()->reader()->create();

        $this->actingAs($reader, 'sanctum')
            ->postJson('/api/manage/posts', $this->validPostPayload())
            ->assertStatus(403);
    }

    public function test_an_author_can_create_a_post(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $tag = Tag::factory()->create();

        $response = $this->actingAs($author, 'sanctum')
            ->postJson('/api/manage/posts', $this->validPostPayload(['tags' => [$tag->id]]));

        $response->assertCreated()->assertJsonPath('title', 'مقاله تستی');

        $this->assertDatabaseHas('posts', ['title' => 'مقاله تستی', 'author_id' => $author->id]);
    }

    public function test_an_author_cannot_update_another_authors_post(): void
    {
        $owner = User::factory()->create(['role' => 'author']);
        $intruder = User::factory()->create(['role' => 'author']);
        $post = Post::factory()->create(['author_id' => $owner->id]);

        $this->actingAs($intruder, 'sanctum')
            ->putJson("/api/manage/posts/{$post->id}", ['title' => 'تغییر غیرمجاز'])
            ->assertStatus(403);
    }

    public function test_an_admin_can_update_any_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->putJson("/api/manage/posts/{$post->id}", ['title' => 'ویرایش‌شده توسط ادمین'])
            ->assertOk()
            ->assertJsonPath('title', 'ویرایش‌شده توسط ادمین');
    }

    public function test_an_author_only_sees_their_own_posts_in_manage_list(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        Post::factory()->count(2)->create(['author_id' => $author->id]);
        Post::factory()->count(3)->create(); // other authors

        $response = $this->actingAs($author, 'sanctum')->getJson('/api/manage/posts');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_an_admin_sees_all_posts_in_manage_list(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->count(4)->create();

        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/manage/posts');

        $response->assertOk();
        $this->assertCount(4, $response->json('data'));
    }

    public function test_owner_can_delete_their_post(): void
    {
        $this->withExceptionHandling();
        $author = User::factory()->create(['role' => 'author']);
        $post = Post::factory()->create(['author_id' => $author->id]);

        $this->actingAs($author, 'sanctum')
            ->deleteJson("/api/manage/posts/{$post->id}")
            ->assertOk();

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    private function validPostPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'مقاله تستی',
            'content' => '<p>محتوای تستی</p>',
            'status' => 'draft',
        ], $overrides);
    }



    public function test_post_detail_does_not_expose_pending_comments(): void
{
    $post = Post::factory()->create(['status' => 'published', 'published_at' => now()->subDay()]);
    Comment::factory()->create(['post_id' => $post->id, 'status' => 'pending']);

    $this->getJson("/api/posts/{$post->slug}")
        ->assertOk()
        ->assertJsonMissingPath('comments');
}
}
