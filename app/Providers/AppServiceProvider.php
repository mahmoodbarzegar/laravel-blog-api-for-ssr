<?php

namespace App\Providers;

use App\Models\Post;
use App\Policies\PostPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Single-resource responses (show/store/update/me) return the object
        // directly instead of {"data": {...}} — simpler for the Nuxt client.
        // Paginated collections still return {data, links, meta} untouched.
        JsonResource::withoutWrapping();

        Gate::policy(Post::class, PostPolicy::class);
    }
}
