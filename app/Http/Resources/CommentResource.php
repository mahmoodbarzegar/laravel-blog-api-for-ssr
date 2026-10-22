<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
            'post' => $this->whenLoaded('post', fn() => [
                'id' => $this->post->id,
                'title' => $this->post->title,
                'slug' => $this->post->slug,
            ]),
            'post_id' => $this->post_id,
            'parent_id' => $this->parent_id,
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
