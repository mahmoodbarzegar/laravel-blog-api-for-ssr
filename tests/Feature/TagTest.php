<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_list_tags(): void
    {
        Tag::factory()->count(4)->create();

        $this->getJson('/api/tags')->assertOk()->assertJsonCount(4, 'data');
    }

    public function test_a_reader_cannot_create_a_tag(): void
    {
        $reader = User::factory()->reader()->create();

        $this->actingAs($reader, 'sanctum')
            ->postJson('/api/tags', ['name' => 'لاراول'])
            ->assertStatus(403);
    }

    public function test_an_author_can_create_a_tag(): void
    {
        $author = User::factory()->create(['role' => 'author']);

        $this->actingAs($author, 'sanctum')
            ->postJson('/api/tags', ['name' => 'لاراول'])
            ->assertCreated();

        $this->assertDatabaseHas('tags', ['name' => 'لاراول']);
    }

    public function test_only_an_admin_can_delete_a_tag(): void
    {
        $author = User::factory()->create(['role' => 'author']);
        $tag = Tag::factory()->create();

        $this->actingAs($author, 'sanctum')
            ->deleteJson("/api/tags/{$tag->id}")
            ->assertStatus(403);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/tags/{$tag->id}")
            ->assertOk();

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
