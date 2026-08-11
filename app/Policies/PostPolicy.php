<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Admins can manage any post; authors may only manage their own.
     */

    public function update(User $user, Post $post): bool
    {
        return $user->isAdmin() || $user->id === $post->author_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->isAdmin() || $user->id === $post->author_id;
    }
}
