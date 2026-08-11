<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'excerpt' => fake()->paragraph(2),
            'content' => collect(range(1, 5))->map(fn () => '<p>'.fake()->paragraph(6).'</p>')->implode("\n"),
            'cover_image' => null,
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'views' => fake()->numberBetween(0, 5000),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft', 'published_at' => null]);
    }
}
