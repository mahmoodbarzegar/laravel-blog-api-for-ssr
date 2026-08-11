<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_upload_an_image(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('cover.jpg');

        $this->postJson('/api/uploads', ['image' => $file])->assertStatus(401);
    }

    public function test_a_reader_cannot_upload_an_image(): void
    {
        Storage::fake('public');
        $reader = User::factory()->reader()->create();
        $file = UploadedFile::fake()->image('cover.jpg');

        $this->actingAs($reader, 'sanctum')
            ->postJson('/api/uploads', ['image' => $file])
            ->assertStatus(403);
    }

    public function test_an_author_can_upload_and_receives_a_public_url(): void
    {
        Storage::fake('public');
        $author = User::factory()->create(['role' => 'author']);
        $file = UploadedFile::fake()->image('cover.jpg', 2000, 1200);

        $response = $this->actingAs($author, 'sanctum')
            ->postJson('/api/uploads', ['image' => $file]);

        $response->assertCreated()->assertJsonStructure(['url']);

        $this->assertNotEmpty(Storage::disk('public')->allFiles());
    }

    public function test_upload_rejects_non_image_files(): void
    {
        Storage::fake('public');
        $author = User::factory()->create(['role' => 'author']);
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $this->actingAs($author, 'sanctum')
            ->postJson('/api/uploads', ['image' => $file])
            ->assertStatus(422);
    }
}
