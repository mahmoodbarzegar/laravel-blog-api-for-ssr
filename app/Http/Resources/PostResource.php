<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->whenNotNull($this->content),
            'cover_image' => $this->cover_image,
            'status' => $this->status,
            'published_at' => $this->published_at?->toIso8601String(),
            'views' => $this->views,
            'reading_time' => $this->reading_time,
            'author' => new UserResource($this->whenLoaded('author')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->whenCounted('comments'),
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
                'og_image' => $this->og_image ?: $this->cover_image,
                'canonical_slug' => $this->slug,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
