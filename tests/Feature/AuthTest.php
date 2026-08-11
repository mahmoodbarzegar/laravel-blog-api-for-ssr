<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'سارا احمدی',
            'email' => 'sara@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'role'], 'token']);

        $this->assertDatabaseHas('users', [
            'email' => 'sara@example.com',
            'role' => 'reader',
        ]);
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'سارا احمدی',
            'email' => 'sara@example.com',
            'password' => 'password123',
            'password_confirmation' => 'something-else',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'sara@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'سارا احمدی',
            'email' => 'sara@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_a_user_can_login_with_correct_credentials(): void
    {
        User::factory()->create([
            'email' => 'sara@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'sara@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'sara@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'sara@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_their_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/auth/me');

        $response->assertOk()->assertJsonPath('id', $user->id);
    }

    public function test_guest_cannot_fetch_profile(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_a_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('web')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout');

        $response->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
