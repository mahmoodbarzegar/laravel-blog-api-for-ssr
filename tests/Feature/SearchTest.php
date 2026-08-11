<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_matches_posts_by_title(): void
    {
        Post::factory()->create([
            'title' => 'راهنمای کامل لاراول ۱۱',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        Post::factory()->create([
            'title' => 'آشپزی ایرانی برای مبتدیان',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->getJson('/api/search?q=لاراول');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_search_does_not_return_unpublished_posts(): void
    {
        Post::factory()->draft()->create(['title' => 'مقاله پیش‌نویس لاراول']);

        $response = $this->getJson('/api/search?q=لاراول');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    public function test_search_with_empty_query_returns_all_published_posts(): void
    {
        Post::factory()->count(2)->create(['status' => 'published', 'published_at' => now()->subDay()]);

        $response = $this->getJson('/api/search');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }
}
