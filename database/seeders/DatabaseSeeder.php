<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'مدیر سایت',
            'email' => 'admin@example.com',
        ]);

        $authors = User::factory(3)->create();

        $categories = collect(['فناوری', 'برنامه‌نویسی', 'سبک زندگی', 'کسب‌وکار'])
            ->map(fn ($name) => Category::create(['name' => $name]));

        $tags = Tag::factory(12)->create();

        Post::factory(30)
            ->recycle($authors->push($admin))
            ->recycle($categories)
            ->create()
            ->each(function (Post $post) use ($tags) {
                $post->tags()->attach(
                    $tags->random(rand(1, 3))->pluck('id')
                );
            });

        $this->command->info('Seeding complete. Admin login: admin@example.com / password');
    }
}
