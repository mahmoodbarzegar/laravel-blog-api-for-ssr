<?php

namespace Tests\Unit;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_slug_is_generated_automatically_from_the_title(): void
    {
        $post = Post::factory()->create(['title' => 'سلام دنیا لاراول']);

        $this->assertNotEmpty($post->slug);
    }

    public function test_duplicate_titles_produce_unique_slugs(): void
    {
        $first = Post::factory()->create(['title' => 'مقاله تکراری']);
        $second = Post::factory()->create(['title' => 'مقاله تکراری']);

        $this->assertNotEquals($first->slug, $second->slug);
    }

    public function test_the_slug_does_not_change_when_the_post_is_updated(): void
    {
        $post = Post::factory()->create(['title' => 'عنوان اول']);
        $originalSlug = $post->slug;

        $post->update(['title' => 'عنوان دوم']);

        $this->assertEquals($originalSlug, $post->fresh()->slug);
    }

    public function test_seo_title_falls_back_to_the_post_title_when_meta_title_is_empty(): void
    {
        $post = Post::factory()->make(['title' => 'عنوان اصلی', 'meta_title' => null]);

        $this->assertEquals('عنوان اصلی', $post->seo_title);
    }

    public function test_seo_title_uses_meta_title_when_present(): void
    {
        $post = Post::factory()->make(['title' => 'عنوان اصلی', 'meta_title' => 'عنوان سئو سفارشی']);

        $this->assertEquals('عنوان سئو سفارشی', $post->seo_title);
    }

    public function test_reading_time_is_calculated_from_word_count(): void
    {
        $content = collect(range(1, 400))->map(fn () => 'کلمه')->implode(' ');

        $post = Post::factory()->make(['content' => "<p>{$content}</p>"]);

        // ~400 words at 200 wpm → 2 minutes
        $this->assertEquals(2, $post->reading_time);
    }

    public function test_reading_time_is_never_less_than_one_minute(): void
    {
        $post = Post::factory()->make(['content' => '<p>چند کلمه کوتاه</p>']);

        $this->assertEquals(1, $post->reading_time);
    }

    public function test_published_scope_excludes_drafts_and_future_scheduled_posts(): void
    {
        Post::factory()->create(['status' => 'published', 'published_at' => now()->subHour()]);
        Post::factory()->create(['status' => 'draft', 'published_at' => now()->subHour()]);
        Post::factory()->create(['status' => 'published', 'published_at' => now()->addHour()]);

        $this->assertEquals(1, Post::published()->count());
    }

    public function test_search_scope_matches_title_excerpt_and_content(): void
    {
        Post::factory()->create(['title' => 'مقاله درباره ری‌اکت', 'excerpt' => 'یک مقاله عادی', 'content' => '<p>متن عادی</p>']);
        Post::factory()->create(['title' => 'مقاله عادی', 'excerpt' => 'اشاره‌ای به ری‌اکت اینجاست', 'content' => '<p>متن عادی</p>']);
        Post::factory()->create(['title' => 'مقاله بی‌ربط', 'excerpt' => 'خلاصه بی‌ربط', 'content' => '<p>هیچ ارتباطی نیست</p>']);

        $this->assertEquals(2, Post::search('ری‌اکت')->count());
    }

    public function test_search_scope_returns_all_when_term_is_null(): void
    {
        Post::factory()->count(3)->create();

        $this->assertEquals(3, Post::search(null)->count());
    }
}
