<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use HasFactory, SoftDeletes, HasSlug;

    protected $fillable = [
        'author_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'status',
        'published_at',
        'views',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'views' => 'integer',
        ];
    }
    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if ($post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }
        });
    }
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function allComments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('excerpt', 'like', "%{$term}%")
                ->orWhere('content', 'like', "%{$term}%");
        });
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->meta_description ?: ($this->excerpt ?: '');
    }

    public function getReadingTimeAttribute(): int
    {
        $text = trim(strip_tags($this->content));

        // str_word_count() only recognizes Latin letters, so it undercounts
        // (or zeroes out) Persian/Arabic text. Splitting on whitespace works
        // for any script.
        $words = $text === '' ? [] : preg_split('/\s+/u', $text);

        return max(1, (int) ceil(count($words) / 200));
    }
}
