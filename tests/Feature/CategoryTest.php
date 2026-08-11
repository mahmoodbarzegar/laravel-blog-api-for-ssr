<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_anyone_can_list_categories(): void
    {
        Category::factory()->count(3)->create();

        $this->getJson('/api/categories')->assertOk()->assertJsonCount(3, 'data');
    }

    public function test_anyone_can_view_a_single_category_by_slug(): void
    {
        $category = Category::factory()->create(['name' => 'فناوری']);

        $this->getJson("/api/categories/{$category->slug}")
            ->assertOk()
            ->assertJsonPath('name', 'فناوری');
    }

    public function test_guest_cannot_create_a_category(): void
    {
        $this->postJson('/api/categories', ['name' => 'دسته جدید'])->assertStatus(401);
    }

    public function test_a_non_admin_author_cannot_create_a_category(): void
    {
        $author = User::factory()->create(['role' => 'author']);

        $this->actingAs($author, 'sanctum')
            ->postJson('/api/categories', ['name' => 'دسته جدید'])
            ->assertStatus(403);
    }

    public function test_an_admin_can_create_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/categories', ['name' => 'دسته جدید'])
            ->assertCreated();

        $this->assertDatabaseHas('categories', ['name' => 'دسته جدید']);
    }

    public function test_category_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name' => 'فناوری']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/categories', ['name' => 'فناوری'])
            ->assertStatus(422);
    }

    public function test_an_admin_can_delete_a_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/categories/{$category->id}")
            ->assertOk();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
